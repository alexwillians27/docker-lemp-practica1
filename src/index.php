<?php
/**
 * =====================================================================
 *  ficha.php · Forja de Héroes · UD2 · Reto evaluable
 * =====================================================================
 *  Autor/a: TODO · Variante: TODO
 *
 *  Estructura OBLIGATORIA (como en el paso 09 del caso guiado):
 *   1) directiva strict_types
 *   2) carga de inc/heroe.php con require_once y __DIR__
 *   3) CÁLCULOS: aquí arriba, sin HTML
 *   4) PRESENTACIÓN: HTML con los valores incrustados
 *
 *  PROHIBIDO en este reto: if, else, switch, match, bucles, arrays propios
 *  y funciones propias (llegan en la UD3 y la UD4). Usa operadores,
 *  el ternario y los operadores ?? y ?:.
 */

// TODO 1. strict_types
declare(strict_types=1);

// TODO 2. require_once del fichero de datos
require_once __DIR__ . '/inc/heroe.php';
// ---------------------------------------------------------------------
// TODO 3. CÁLCULOS (sigue el orden del enunciado: R1 a R8)
// ---------------------------------------------------------------------
// R1 · Conversión de tipos
$nivel=(int)$nivelTxt;
$fuerza=(int)$fuerzaTxt;
$destreza=(int)$destrezaTxt;
$inteligencia=(int)$inteligenciaTxt;
$constitucion=(int)$constitucionTxt;
$experiencia=(int)$experienciaTxt;
$vidaActual=(int)$vidaActualTxt;
$oro=(float)$oroTxt;
// R2 · Progresión (nivel, xpEnNivel, xpParaSubir, pctNivel)
$nivel=$experiencia/XP_POR_NIVEL;
$xpEnNivel=$experiencia%XP_POR_NIVEL;
$xpParaSubir=XP_POR_NIVEL-$xpEnNivel;
$pctNivel=($xpEnNivel/XP_POR_NIVEL)*100;

// R3 · Vida (vidaMax, pctVida)
$vidaMax=VIDA_BASE+$constitucion+$nivel+MULT_VIDA;
$pctVida=($vidaActual/$vidaMax)*100;

// R4 · Combate (daño, estadística especial, poder, comparación con el rival)
$daño=((int)$inteligenciaTxt*3)+((int)$nivelTxt**2)/4;
$poder=(int) round($daño+$nivel);
$comparacion=$poder<=>PODER_RIVAL;
$veredicto = ($comparacion===1) ? 'Ventaja: ¡A LA CARGA!' : (($comparacion===0) ? 'Empate' : 'Desventaja: Retirada');

// R5 · Estado y decisiones
$estado= ($vidaActual>=0) ? ($pctVida<35 ? 'En Peligro' : 'En Pie') : 'K.O';
$puedeAscender= $nivel>=5 && $pctVida>=50 ? 'Si' : 'No'; //-----------------------------------
$necesitaPocion= $pctVida<40|| $esVeterano===false ? 'Si' : 'No';
$insignia = $esVeterano ? 'VETERANO' : 'NOVATO';

// R6 · Barras de progreso
$bloquesVida = (int) round($pctVida / 100 * BLOQUES_BARRA);
$barraVida = str_repeat('█', $bloquesVida) . str_repeat('░', BLOQUES_BARRA - $bloquesVida);

// R7 · Textos (htmlspecialchars, ?? , ?: , number_format, .= , heredoc)
$nombreSeguro = htmlspecialchars($nombreHeroe);
$apodoMostrar = $apodo ?? 'Sin apodo';
$lemaMostrar = $lema ?: 'Sin lema (todavía)';

$oroFormateado = number_format($oro, 2, ',', '.') . ' mo';
$dañoFormateado = number_format($daño, 1, ',', '.');
$pctVidaFormateado = number_format($pctVida, 1, ',', '.');

// Registro de combate construido con .= en tres pasos
$registroCombate = "Hora: " . date('H:i:s');
$registroCombate .= " | Héroe: $nombreSeguro (Niv. $nivel)";
$registroCombate .= " vs " . NOMBRE_RIVAL . " -> " . $veredicto;

// Crónica de tres líneas con heredoc
$clase = CLASE_HEROE; 
$cronica = <<<CRONICA
Nombre: $nombreSeguro | Clase: $clase
Nivel: $nivel | Experiencia faltante: $xpParaSubir XP
Estado del héroe: $estado
CRONICA;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>"Ficha de ".<?= htmlspecialchars($nombreHeroe) ?>"</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    
<main>
    <!-- TODO: insignia VETERANO/NOVATO, usando la etiqueta LARGA con echo -->
    <span class="insignia"> <?php echo $insignia; ?> </span>
    <!-- TODO: a partir de aquí, usa la etiqueta CORTA de salida -->
    <h1><?= htmlspecialchars($nombreHeroe) ?></h1>
    <p class="subtitulo"><?= CLASE_HEROE . ', ' . ($apodo ?? 'Sin apodo') . ', Lema: ' . ($lema ?: 'Sin lema (todavía)') ?></p>

    <h2>Estadísticas</h2>
    <div class="rejilla">
        <div class="stat"><div class="etq">Nivel</div><div class="val"><?= $nivelTxt ?></div></div>
        <div class="stat"><div class="etq">Vida</div><div class="val"><?= $vidaActualTxt . ' / ' . VIDA_BASE ?></div></div>
        <div class="stat"><div class="etq">Daño</div><div class="val"><?= number_format($daño, 1, ',', '.') ?></div></div>
        <div class="stat"><div class="etq">Estadística especial</div><div class="val"><?= ($inteligencia * 10) + ($experiencia % 100) ?></div></div>
        <div class="stat"><div class="etq">Poder</div><div class="val"><?= $poder ?></div></div>
        <div class="stat"><div class="etq">Oro</div><div class="val"><?= number_format($oro, 2, ',', '.') ?> mo</div></div>
    </div>
    <table>
        <tr><th>Fuerza</th><th>Destreza</th><th>Inteligencia</th><th>Constitución</th></tr>
        <tr><td><?= $fuerza ?></td><td><?= $destreza ?></td><td><?= $inteligencia ?></td><td><?= $constitucion ?></td></tr>
    </table>

    <h2>Progreso</h2>
    <p class="barra vida">VIDA <?= $barraVida ?> · <?= number_format($pctVida, 1, ',', '.') ?> %</p>
    <p class="barra xp">XP&nbsp;&nbsp; <?= str_repeat('█', (int)round($pctNivel / 100 * BLOQUES_BARRA)) . str_repeat('░', BLOQUES_BARRA - (int)round($pctNivel / 100 * BLOQUES_BARRA)) ?> · <?= $xpEnNivel ?>/<?= XP_POR_NIVEL ?> (faltan <?= $xpParaSubir ?>)</p>
    <h2>Estado y decisiones</h2>
    <table>
        <tr><td>Estado</td><td><?= $estado ?></td></tr>
        <tr><td>¿Puede ascender de rango? (nivel ≥ 5 y vida ≥ 50 %)</td><td><?= $puedeAscender ?></td></tr>
        <tr><td>¿Necesita poción? (vida &lt; 40 % o no veterano)</td><td><?= $necesitaPocion ?></td></tr>
        <tr><td>Rival: <?= NOMBRE_RIVAL ?> (poder <?= PODER_RIVAL ?>)</td><td><?= $comparacion ?> → <?= $veredicto ?></td></tr>
    </table>

    <h2>Crónica</h2>
    <pre><?= $cronica ?></pre>
    <p><em><?= $registroCombate ?></em></p>

    <h2>Modo depuración</h2>
    <pre>
Tipo de $experienciaTxt: <?= get_debug_type($experienciaTxt) ?> 
Tipo de $experiencia: <?= get_debug_type($experiencia) ?> 

<?php var_dump($oro, $pctVida, $esVeterano, $apodo); ?>

Comparación == : <?= ($experienciaTxt == $experiencia) ? 'true' : 'false' ?> 
Comparación === : <?= ($experienciaTxt === $experiencia) ? 'true' : 'false' ?> 
    </pre>

    <footer><?= date('d/m/Y H:i:s') ?> · <?= PHP_VERSION ?> ·
        <a href="diagnostico.php">Diagnóstico del servidor</a></footer>
</main>
</body>
</html>