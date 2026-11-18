<?php
/**
 * =====================================================================
 *  tablon.php · Tablón de misiones · PLANTILLA DEL ALUMNO
 * =====================================================================
 *  PARTE 1 (UD3): recorrer el array de misiones y pintar cada tarjeta.
 *  PARTE 2 (UD4): filtro por GET y aceptación por POST.
 *  PARTE 3 (UD5): página solo para miembros; contador de visitas en sesión.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/funciones.php';
require_once __DIR__ . '/inc/sesion.php';

iniciarSesion();
$aventurero = requiereLogin();
$misiones   = require __DIR__ . '/inc/datos_misiones.php';
$_SESSION['zurron'] ??= [];

// TODO (Parte 3): suma 1 a $_SESSION['visitas'] en cada carga del tablón.

// ---- POST: aceptar una misión --------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO (Parte 2):
    //  1. Lee $_POST['id'] y busca la misión con buscarMision().
    //  2. Si no existe → mensajeFlash('Esa misión no existe en el tablón.')
    //  3. Si ya está en $_SESSION['zurron'] → mensaje de aviso, sin duplicar.
    //  4. Si no → añade el id a $_SESSION['zurron'] y mensaje de confirmación.
    //  5. Redirige (header Location + exit) para no reenviar el formulario con F5.
}

// ---- GET: filtros ---------------------------------------------------------
$rango     = leerRangoFiltro($_GET);
$oroMinimo = leerOroMinimo($_GET);
$visibles  = filtrarMisiones($misiones, $rango, $oroMinimo);
$flash     = leerFlash();

$titulo = 'Tablón de misiones';
require __DIR__ . '/inc/cabecera.php';
?>
<h1>Tablón de misiones</h1>
<!-- TODO (Parte 3): muestra la hora de entrada y las visitas de esta sesión. -->
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

<p><?= count($visibles) ?> de <?= count($misiones) ?> misiones.</p>

<?php
/*
 * TODO (Parte 1): recorre $visibles con foreach y pinta por cada misión...
 */
?>

<div class="misiones">
<?php if (empty($visibles)): ?>
    <p>Ninguna misión cumple el filtro.</p>
<?php else: ?>
    <?php foreach ($visibles as $mision): ?>
        <article class="mision <?= claseRango($mision['rango']) ?>">
            <span class="rango"><?= e($mision['rango']) ?> · <?= etiquetaRango($mision['rango']) ?></span>
            
            <h2><?= e($mision['titulo']) ?></h2>
            
            <?php 
            // Controlamos el singular/plural de los días
            $textoDias = $mision['dias'] === 1 ? '1 día' : $mision['dias'] . ' días'; 
            ?>
            <p class="nota"><?= e($mision['zona']) ?> · <?= $textoDias ?></p>
            
            <p><?= e($mision['descripcion']) ?></p>
            
            <p class="oro"><?= formatearOro($mision['recompensa']) ?></p>

            <!-- Preparación de la Parte 2: El botón de aceptar misión -->
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