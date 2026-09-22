# Stack LEMP con Docker Compose (nginx + PHP-FPM + MySQL)

Alternativa a XAMPP para desarrollo web local: en vez de instalar Apache, PHP y
MySQL directamente en el sistema, cada servicio corre en su propio contenedor
Docker, aislado y desechable.

## Servicios

| Servicio | Imagen / build | Función |
|----------|-----------------|---------|
| `web`    | `nginx:1.27-alpine` | Recibe las peticiones HTTP en el puerto `8080` y las reenvía a PHP-FPM |
| `php`    | `php/Dockerfile` (PHP 8.3-FPM) | Ejecuta el código PHP y se conecta a la base de datos con PDO |
| `db`     | `mysql:8.0` | Almacena los datos de la aplicación |

## Cómo levantarlo

```bash
git clone <URL-de-este-repositorio>
cd <carpeta-del-repositorio>
docker compose up -d
```

Después abre [http://localhost:8080](http://localhost:8080) en el navegador.

La primera vez que se carga la página, `src/index.php` crea automáticamente
una tabla `visitas` en la base de datos `appdb` y muestra cuántas veces se ha
cargado la página, para comprobar que los tres contenedores se comunican
correctamente.

## Estructura del repositorio

```
.
├── docker-compose.yml     # Define los tres servicios y su conexión
├── php/
│   └── Dockerfile         # Imagen PHP 8.3-FPM con extensión pdo_mysql
├── nginx/
│   └── default.conf       # Configuración de nginx (fastcgi_pass a php:9000)
├── src/
│   └── index.php          # Código PHP de prueba (conexión PDO)
└── README.md
```

## Detener y limpiar

```bash
docker compose down          # Para los contenedores
docker compose down -v       # Para los contenedores y borra el volumen de datos
```

## Captura de pantalla

<!-- Sustituye esta línea por la imagen real, por ejemplo: -->
![Resultado en el navegador](captura.png)