<?php
/**
 * =====================================================================
 *  tablon.php · Tablón de misiones
 * =====================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/funciones.php';
require_once __DIR__ . '/inc/sesion.php';

iniciarSesion();
$aventurero = requiereLogin();
$misiones   = require __DIR__ . '/inc/datos_misiones.php';
$_SESSION['zurron'] ??= [];

// ---- POST: aceptar una misión --------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Leemos el id del formulario y buscamos la misión
    $id = $_POST['id'] ?? '';
    $mision = buscarMision($misiones, $id);

    // 2. Si no existe en el array general
    if ($mision === null) {
        mensajeFlash('Esa misión no existe en el tablón.');
    } 
    // 3. Si ya está metida en el zurrón (sesión)
    elseif (in_array($id, $_SESSION['zurron'], true)) {
        mensajeFlash('Ya has aceptado esta misión anteriormente.');
    } 
    // 4. Si existe y no la teníamos, la añadimos al array de la sesión
    else {
        $_SESSION['zurron'][] = $id;
        mensajeFlash('Misión aceptada: ' . $mision['titulo']);
    }

    // 5. Redirigimos al tablón para matar la petición POST
    header('Location: tablon.php');
    exit;
}

// ---- Visitas de esta sesión -----------------------------------------------
// Se cuenta una visita por cada carga real del tablón (el POST redirige).
$_SESSION['visitas'] = ($_SESSION['visitas'] ?? 0) + 1;

// ---- GET: filtros ---------------------------------------------------------
$rango     = leerRangoFiltro($_GET);
$oroMinimo = leerOroMinimo($_GET);
$visibles  = filtrarMisiones($misiones, $rango, $oroMinimo);
$flash     = leerFlash();

$titulo = 'Tablón de misiones';
require __DIR__ . '/inc/cabecera.php';
?>
<h1>Tablón de misiones</h1>
<p class="nota">
    Entrada: <?= e($_SESSION['entrada'] ?? '—') ?> ·
    Visitas de esta sesión: <?= (int) $_SESSION['visitas'] ?>
</p>
<?php if ($flash !== null): ?><p class="aviso"><?= e($flash) ?></p><?php endif; ?>

<form method="get" action="tablon.php" class="filtros">
    <label>Rango
        <select name="rango">
            <option value="">Todos</option>
            <?php foreach (RANGOS as $r): ?>
                <option value="<?= $r ?>" <?= $r === $rango ? 'selected' : '' ?>><?= $r ?> · <?= etiquetaRango($r) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Recompensa mínima
        <input type="number" name="oro" min="0" max="10000" step="10" value="<?= $oroMinimo ?>">
    </label>
    <button type="submit">Filtrar</button>
</form>

<p><?= count($visibles) ?> de <?= count($misiones) ?> misiones.</p>

<div class="misiones">
<?php if (empty($visibles)): ?>
    <p>Ninguna misión cumple el filtro.</p>
<?php else: ?>
    <?php foreach ($visibles as $mision): ?>
        <article class="mision <?= claseRango($mision['rango']) ?>">
            <span class="rango"><?= e($mision['rango']) ?> · <?= etiquetaRango($mision['rango']) ?></span>
            
            <h2><?= e($mision['titulo']) ?></h2>
            
            <?php 
            $textoDias = $mision['dias'] === 1 ? '1 día' : $mision['dias'] . ' días'; 
            ?>
            <p class="nota"><?= e($mision['zona']) ?> · <?= $textoDias ?></p>
            
            <p><?= e($mision['descripcion']) ?></p>
            
            <p class="oro"><?= formatearOro($mision['recompensa']) ?></p>

            <?php if (in_array($mision['id'], $_SESSION['zurron'], true)): ?>
                <p class="en-zurron">✔ En tu zurrón</p>
            <?php else: ?>
                <form method="post" action="tablon.php">
                    <input type="hidden" name="id" value="<?= e($mision['id']) ?>">
                    <button type="submit">Aceptar misión</button>
                </form>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<?php require __DIR__ . '/inc/pie.php'; ?>