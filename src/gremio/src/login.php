<?php
/**
 * =====================================================================
 *  login.php · Inicio de sesión contra MySQL
 * =====================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/funciones.php';
require_once __DIR__ . '/inc/sesion.php';
require_once __DIR__ . '/inc/conexion.php';

iniciarSesion();

if (aventureroActual() !== null) {
    header('Location: tablon.php');
    exit;
}

$error    = null;
$usuario  = $_COOKIE[COOKIE_USUARIO] ?? ''; // Leemos la cookie si existe
$recordar = isset($_COOKIE[COOKIE_USUARIO]); // Marcado si había cookie

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Leemos y limpiamos los campos del formulario
    $usuarioForm = trim($_POST['usuario'] ?? '');
    $claveForm   = $_POST['clave'] ?? '';
    $recordar    = isset($_POST['recordar']);
    
    // 2. Comprobamos que no vengan vacíos
    if ($usuarioForm === '' || $claveForm === '') {
        $error = 'Escribe tu usuario y tu contraseña.';
        $usuario = $usuarioForm; 
    } else {
        try {
            // 3. Intentamos autenticar al usuario
            $aventureroBD = autenticar(conectar(), $usuarioForm, $claveForm);
            
            // 4. Si el login es CORRECTO
            if ($aventureroBD !== null) {
                
                // Respuesta para tu memoria: session_regenerate_id(true) se usa
                // para evitar ataques de "Fijación de Sesión" (Session Fixation). 
                // Al cambiarle el ID a la sesión justo tras loguearse, nos aseguramos
                // de que ningún atacante que conociera el ID antiguo pueda entrar.
                session_regenerate_id(true);       
                
                // Guardamos sus datos y preparamos su sesión
                $_SESSION['aventurero'] = $aventureroBD;
                $_SESSION['zurron'] = [];
                $_SESSION['entrada'] = date('H:i'); // Guardamos la hora actual
                
                // Gestionamos la cookie de recordar
                if ($recordar) {
                    recordarUsuario($usuarioForm);
                } else {
                    olvidarUsuario();
                }
                
                mensajeFlash('¡Bienvenido al gremio, ' . $aventureroBD['nombre'] . '!');
                header('Location: tablon.php');
                exit;
                
            } else {
                // 5. Si falla la clave o el usuario
                $error = 'Usuario o contraseña incorrectos.';
                $usuario = $usuarioForm;
            }
            
        } catch (PDOException $ex) {
            // En caso de que se caiga la base de datos (Excepción PDO)
            error_log('Error de BD en login: ' . $ex->getMessage());
            $error = 'Error interno al conectar con la base de datos.';
            $usuario = $usuarioForm;
        }
    }
}

$motivo = match ($_GET['motivo'] ?? '') {
    'privado' => 'Esa sala es solo para miembros del gremio. Inicia sesión.',
    'salida'  => 'Has salido del gremio. Tu zurrón se ha vaciado.',
    default   => null,
};

$titulo = 'Entrar';
$aventurero = null;
require __DIR__ . '/inc/cabecera.php';
?>
<section class="tarjeta estrecha">
    <h1>Entrada al gremio</h1>
    <?php if ($motivo !== null): ?><p class="aviso"><?= e($motivo) ?></p><?php endif; ?>
    <?php if ($error !== null): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

    <form method="post" action="login.php">
        <label>Usuario
            <input type="text" name="usuario" value="<?= e($usuario) ?>" maxlength="30" required autofocus>
        </label>
        <label>Contraseña
            <input type="password" name="clave" required>
        </label>
        <label class="casilla">
            <input type="checkbox" name="recordar" <?= $recordar ? 'checked' : '' ?>>
            Recordar mi usuario en este navegador
        </label>
        <button type="submit">Entrar</button>
    </form>
</section>
<?php require __DIR__ . '/inc/pie.php'; ?>