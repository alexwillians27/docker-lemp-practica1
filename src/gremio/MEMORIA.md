# Memoria · Gremio de Aventureros

**Código de alumno/a:** ______  (el enunciado pide el código, no el nombre)

## 1. Mi aventurero

- Usuario que he añadido a la tabla: `alex` · nombre «Alex <el Errante>» · clase Explorador.
- Contraseña: `Gremio2026` (en la base de datos solo está su hash).

Comando con el que generé el hash, dentro del contenedor PHP:

```bash
docker compose exec php php -r "echo password_hash('Gremio2026', PASSWORD_DEFAULT), PHP_EOL;"
```

Salida (bcrypt, coste 10):

```
$2y$10$QC5zIkFInKIYusr9ioyAx.SB5VmGtLotk.IY4fyodgJnCM61PDvty
```

Ese hash es lo único que se guarda, en `sql/mi_aventurero.sql`:

```sql
INSERT INTO aventureros (usuario, nombre, clase, password_hash) VALUES
('alex', 'Alex <el Errante>', 'Explorador', '$2y$10$QC5zIkFInKIYusr9ioyAx.SB5VmGtLotk.IY4fyodgJnCM61PDvty');
```

Lo importé después de `gremio.sql`:

```bash
docker compose exec -T db sh -c 'mysql -uroot -prootpass appdb' < src/gremio/sql/mi_aventurero.sql
```

## 2. Parte 1 · match

Sí he puesto `default` en `etiquetaRango()`:

```php
return match ($rango) {
    'E' => 'Novato',
    'D' => 'Aprendiz',
    'C' => 'Veterano',
    'B' => 'Experto',
    'A' => 'Élite',
    'S' => 'Leyenda',
    default => 'Desconocido',
};
```

Si llega el rango `'Z'` no hay brazo que lo cubra, así que entra por `default` y
devuelve `'Desconocido'`. En PHP 8 un `match` sin brazo coincidente lanza
`UnhandledMatchError` y rompería la página entera; con `default` la web sigue
funcionando aunque el dato venga mal. Prefiero un texto de relleno a un error
fatal, porque el rango puede venir de la base de datos o de una URL manipulada.

## 3. Parte 2 · Validación de la URL

Al escribir a mano `tablon.php?rango=Z&oro=hola` se ven las **10 misiones** y no
salta ningún error. Lo evitan estas dos funciones:

```php
function leerRangoFiltro(array $get): ?string
{
    if (empty($get['rango'])) {
        return null;
    }
    $rango = strtoupper(trim($get['rango']));
    if (in_array($rango, RANGOS, true)) {
        return $rango;
    }
    return null;
}

function leerOroMinimo(array $get): int
{
    if (!isset($get['oro'])) {
        return 0;
    }
    $oro = filter_var($get['oro'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0, 'max_range' => 10000],
    ]);
    return $oro === false ? 0 : $oro;
}
```

`'Z'` no está en `['E', 'D', 'C', 'B', 'A', 'S']`, así que `in_array(... , true)`
falla y el rango se queda en `null` (filtro «Todos»). `'hola'` no pasa
`FILTER_VALIDATE_INT`, devuelve `false` y se usa `0`, de modo que no se descarta
ninguna misión. Sin esas comprobaciones, `'Z'` compararía contra un rango
inexistente y `'hola'` llegaría como texto a una comparación numérica.

## 4. Parte 3 · Sesión frente a cookie

En `$_SESSION` (servidor) guardo lo que no debe poder leer ni cambiar el navegador:

| Clave | Contenido |
|---|---|
| `aventurero` | id, usuario, nombre y clase (sin `password_hash`) |
| `zurron` | ids de las misiones aceptadas |
| `visitas` | contador de cargas del tablón en esta sesión |
| `entrada` | hora del login (`date('H:i')`) |
| `flash` | mensaje de un solo uso |

En cookies (navegador) solo guardo datos no sensibles:

| Cookie | Contenido | Duración |
|---|---|---|
| `gremio_tema` | `claro` u `oscuro`, validado con `in_array` | 1 año |
| `gremio_usuario` | nombre de usuario para rellenar el login | 30 días |

No lo haría al revés porque la cookie viaja al cliente y cualquiera puede
editarla: si el zurrón o el aventurero autenticado vivieran ahí, se podría
falsear el inventario o la identidad. Además, el servidor no podría vaciar el
zurrón al cerrar sesión, cosa que sí consigue `cerrarSesion()` al destruir
`$_SESSION`. Por eso el tema y el usuario sobreviven al logout y el zurrón no.

## 5. Parte 3 · Seguridad del login

- Con `' OR '1'='1` **no se entra**. `autenticar()` usa una consulta preparada
  con el marcador `:usuario`:

```php
$stmt = $pdo->prepare("SELECT * FROM aventureros WHERE usuario = :usuario");
$stmt->execute([':usuario' => $usuario]);
```

  El valor se envía aparte de la SQL, así que se busca literalmente el usuario
  `' OR '1'='1`, que no existe: la condición no se altera.

- `session_regenerate_id(true)` cambia el ID de sesión justo después de
  autenticar y borra el archivo de la sesión antigua. Sin esto, un atacante que
  hubiera fijado un `PHPSESSID` antes del login podría reutilizarlo después
  (fijación de sesión).

- El mensaje es el mismo («Usuario o contraseña incorrectos.») para usuario
  inexistente y para contraseña errónea. Si dijera «El usuario no existe», se
  podrían probar nombres hasta descubrir cuáles están dados de alta
  (enumeración de usuarios).

## 6. Pruebas que he hecho

| Prueba | Resultado esperado | Resultado obtenido |
|---|---|---|
| Entrar en `tablon.php` sin login | Redirección a `login.php?motivo=privado` | 302 a `login.php?motivo=privado` y aviso «Esa sala es solo para miembros del gremio» |
| Login con contraseña mala | Mismo error genérico, sin entrar | 200 con «Usuario o contraseña incorrectos.» |
| Aceptar la misma misión dos veces | No se duplica en el zurrón | «Ya has aceptado esta misión anteriormente.»; tras F5 sigue una sola vez |
| Cerrar sesión y volver al zurrón | Sin sesión y zurrón vacío | Redirige a `login.php?motivo=privado` |
| Cambiar el tema y cerrar sesión | El tema se mantiene; el zurrón no | El tema oscuro y el usuario recordado se conservan; el zurrón se vacía |

Comprobaciones extra: `tablon.php?rango=Z&oro=hola` muestra las 10 misiones; una
cookie `gremio_tema` manipulada a `zombie` cae a `claro`; el contador sube en
cada recarga (1, 2, 3…); el tablón muestra la hora del login junto al total de
visitas de la sesión.

## 7. Dificultades y cómo las he resuelto

- **`requiereLogin()` devolvía un invitado falso.** Mientras hacía las Partes 1
  y 2 me venía bien, pero dejaba el tablón abierto sin login. Lo cambié por la
  comprobación real: si `aventureroActual()` es `null`,
  `header('Location: login.php?motivo=privado')` y `exit`.
- **El contador de visitas contaba dos veces al aceptar una misión.** Si sumaba
  antes del bloque POST, la petición POST y la redirección posterior sumaban
  dos. Lo moví detrás del POST: solo cuentan las cargas GET que pintan el tablón.
- **La cookie cruda no cambiaba al guardar el tema.** `$_COOKIE` se rellena al
  empezar la petición, así que un `setcookie()` no se ve hasta la siguiente. Lo
  resolví con el patrón PRG: el POST guarda, avisa con flash y redirige; la
  página siguiente ya muestra el valor nuevo.
- **El compose no coincidía con `conexion.php`.** El servicio `php` recibía
  `DB_HOST`, `DB_NAME`… y `conexion.php` (que es dado) lee `MYSQL_HOST`,
  `MYSQL_DATABASE`… El login fallaba con «Error interno al conectar con la base
  de datos». Alineé las variables del compose con las que espera el código.
- **El hash.** Lo generé primero con una herramienta bcrypt externa, que produce
  `$2b$`; para asegurar la compatibilidad lo regeneré con `password_hash()` de
  PHP 8.3 dentro del contenedor, que devuelve `$2y$`.

## 8. Uso de IA

He utilizado IA de apoyo en puntos concretos del proyecto, no para generarlo
entero. En concreto:

- `requiereLogin()`: sustituir el invitado de desarrollo por la redirección
  real con `header()` y `exit`.
- El contador de visitas y la hora de entrada del tablón, colocados después del
  POST para que aceptar una misión no contara dos veces.
- El guardado del tema en `preferencias.php`: validación con `in_array` estricto,
  `guardarTema()`, mensaje flash y patrón PRG.
- La generación y comprobación del hash de mi aventurero con `password_hash()`
  dentro del contenedor.
- La redacción y el orden de esta memoria.

Las decisiones están revisadas y las pruebas de la sección 6 se han ejecutado
sobre el proyecto. El resto del código se apoya en el material de clase y en el
NotebookLM de la asignatura. Dejo constancia de este uso porque el enunciado
solo permite el cuaderno del profesor y prefiero que quede claro en qué puntos
me apoyé.
