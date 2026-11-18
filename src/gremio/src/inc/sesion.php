<?php
/**
 * =====================================================================
 *  inc/sesion.php · Sesión y cookies del Gremio · PLANTILLA DEL ALUMNO
 * =====================================================================
 *  PARTE 3 (UD5) · CE 4.b (sesiones) · CE 4.c (cookies) · CE 4.e.1 (autenticación)
 *
 *  Recuerda la amnesia de la UD2: PHP olvida todo al terminar la petición.
 *  La SESIÓN guarda datos en el SERVIDOR; el navegador solo guarda una
 *  cookie con el identificador (PHPSESSID). Las COOKIES propias guardan
 *  datos pequeños en el NAVEGADOR (aquí, preferencias no sensibles).
 */

declare(strict_types=1);

const COOKIE_TEMA    = 'gremio_tema';
const COOKIE_USUARIO = 'gremio_usuario';
const TEMAS          = ['claro', 'oscuro'];

/** Arranca la sesión con una cookie de sesión segura. (Dado) */
function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Devuelve el aventurero guardado en la sesión, o null. (Dado) */
function aventureroActual(): ?array
{
    return $_SESSION['aventurero'] ?? null;
}

/**
 * Protege una página: si no hay sesión, redirige a login.php?motivo=privado
 * y TERMINA el script. Si la hay, devuelve el aventurero.
 *
 * TODO (Parte 3): ahora devuelve un invitado para que puedas hacer las
 * partes 1 y 2 sin login. Cuando llegues a la Parte 3, cámbialo.
 */
/**
 * Protege una página: si no hay sesión, redirige a login.php?motivo=privado
 * y TERMINA el script. Si la hay, devuelve el aventurero.
 */

function requiereLogin(): array
{
    // MODO DESARROLLO: Devolvemos un usuario "Invitado" falso para poder 
    // probar las Partes 1 y 2 sin tener que hacer login en la base de datos.
    return ['id' => 0, 'usuario' => 'invitado', 'nombre' => 'Invitado', 'clase' => '—'];
}

/** Guarda un mensaje para mostrarlo UNA vez en la siguiente página. (Dado) */
function mensajeFlash(string $texto): void
{
    $_SESSION['flash'] = $texto;
}

/** Recupera y borra el mensaje flash. (Dado) */
function leerFlash(): ?string
{
    $texto = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $texto;
}

/**
 * Tema guardado en la cookie COOKIE_TEMA. Si no existe o su valor no está
 * en TEMAS (¡alguien puede haberla manipulado!), devuelve 'claro'.
 */
function temaActual(): string
{
    // Leemos la cookie. Si no existe, usamos una cadena vacía
    $tema = $_COOKIE[COOKIE_TEMA] ?? '';
    
    if (in_array($tema, TEMAS, true)) {
        return $tema;
    }
    return 'claro';
}

/**
 * Guarda el tema en la cookie COOKIE_TEMA durante un año.
 */
function guardarTema(string $tema): void
{
    setcookie(COOKIE_TEMA, $tema, [
        'expires'  => time() + (365 * 24 * 60 * 60), // 1 año en segundos
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/** Guarda el NOMBRE DE USUARIO (nunca la contraseña) 30 días en COOKIE_USUARIO. */
function recordarUsuario(string $usuario): void
{
    setcookie(COOKIE_USUARIO, $usuario, [
        'expires'  => time() + (30 * 24 * 60 * 60), // 30 días en segundos
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/** Borra la cookie COOKIE_USUARIO (caducidad en el pasado). */
function olvidarUsuario(): void
{
    setcookie(COOKIE_USUARIO, '', [
        'expires'  => time() - 3600, // 1 hora en el pasado (se borra instantáneamente)
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * Comprueba las credenciales contra la tabla `aventureros`.
 */
function autenticar(PDO $pdo, string $usuario, string $clave): ?array
{
    // 1. Consulta preparada para evitar Inyección SQL
    $stmt = $pdo->prepare("SELECT * FROM aventureros WHERE usuario = :usuario");
    $stmt->execute([':usuario' => $usuario]);
    $fila = $stmt->fetch();
    
    // 2. Si no hay fila (el usuario no existe)
    if ($fila === false) {
        return null;
    }
    
    // 3. Verificamos la contraseña contra el hash de la base de datos
    if (!password_verify($clave, $fila['password_hash'])) {
        return null;
    }
    
    // 4. Quitamos el hash antes de devolver el array (nunca guardes hashes en sesión)
    unset($fila['password_hash']);
    return $fila;
}
// Respuesta para tu memoria: El mensaje de error debe ser idéntico si falla el 
// usuario o la contraseña para EVITAR LA ENUMERACIÓN DE USUARIOS. Si el sistema 
// dijera "El usuario no existe", un atacante podría ir probando nombres hasta 
// adivinar cuáles están registrados en el gremio.

/**
 * Cierra la sesión POR COMPLETO.
 */
function cerrarSesion(): void
{
    $_SESSION = []; // Vaciamos los datos
    
    // Destruimos la cookie que guarda el PHPSESSID en el navegador
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $params["path"],
            'domain'   => $params["domain"],
            'secure'   => $params["secure"],
            'httponly' => $params["httponly"],
            'samesite' => $params["samesite"] ?? 'Lax'
        ]);
    }
    
    session_destroy(); // Destruimos el archivo en el servidor
}