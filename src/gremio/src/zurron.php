<?php
/**
 * =====================================================================
 *  zurron.php · Mi zurrón: misiones aceptadas
 * =====================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/funciones.php';
require_once __DIR__ . '/inc/sesion.php';

iniciarSesion();
$aventurero = requiereLogin();
$misiones   = require __DIR__ . '/inc/datos_misiones.php';
$_SESSION['zurron'] ??= [];

// ---- POST: abandonar una misión ------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    
    // array_search busca el valor en el array y devuelve su posición (índice)
    $posicion = array_search($id, $_SESSION['zurron'], true);
    
    if ($posicion !== false) {
        // unset elimina el elemento de esa posición
        unset($_SESSION['zurron'][$posicion]);
        // array_values reconstruye los índices (0, 1, 2...) para que no queden huecos
        $_SESSION['zurron'] = array_values($_SESSION['zurron']);
        
        mensajeFlash('Has abandonado la misión.');
    }
    
    header('Location: zurron.php');
    exit;
}

// ---- GET: pintar el zurrón -----------------------------------------------
$ids   = $_SESSION['zurron'];
$total = totalRecompensa($misiones, $ids);
$flash = leerFlash();

$titulo = 'Mi zurrón';
require __DIR__ . '/inc/cabecera.php';
?>
<h1>Mi zurrón</h1>
<?php if ($flash !== null): ?><p class="aviso"><?= e($flash) ?></p><?php endif; ?>

<?php if (empty($ids)): ?>
    <p>Tu zurrón está vacío. ¡Ve al <a href="tablon.php">tablón</a> y acepta un encargo!</p>
<?php else: ?>
    <table>
        <tr>
            <th>Misión</th>
            <th>Rango</th>
            <th>Recompensa</th>
            <th>Acción</th>
        </tr>
        <?php foreach ($ids as $id): ?>
            <?php 
            $mision = buscarMision($misiones, $id); 
            // Si por algún casual la misión ya no existe en la base de datos, la saltamos
            if ($mision === null) continue;
            ?>
            <tr>
                <td><?= e($mision['titulo']) ?></td>
                <td><?= e($mision['rango']) ?></td>
                <td class="oro"><?= formatearOro($mision['recompensa']) ?></td>
                <td>
                    <form method="post" action="zurron.php">
                        <input type="hidden" name="id" value="<?= e($mision['id']) ?>">
                        <button type="submit" class="boton-peligro">Abandonar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <th colspan="2">Recompensa Total</th>
            <th class="oro"><?= formatearOro($total) ?></th>
            <th></th>
        </tr>
    </table>
<?php endif; ?>

<p class="nota">El zurrón vive en la sesión: si sales del gremio, se vacía.</p>
<?php require __DIR__ . '/inc/pie.php'; ?>