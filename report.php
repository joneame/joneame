<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// David Martí <neikokz@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');

function peta() {
    header('Location: https://'.get_server_name().$globals['base_url']);
    die;
}

if (!$globals['reports_notitas']) {
    do_error('Los reportes están desactivados', 403);
}

if ($_POST['done']) {
    do_header('Reportar notita');
    echo '<div id="singlewrap"><div class="genericform"><div class="genericform">
        <h4>Reportar notita</h4>
        <fieldset class="fondo-caja espaciador borde">
        ¿De verdad pensabas que ibamos a enviar el reporte? Tu reporte ha sido enviado a /dev/null
        </fieldset>
        </div></div></div>';
    do_footer();
    die;
}

if (!is_numeric($_REQUEST['p']) || $current_user->user_id == 0)
    peta();

$reported_id = intval($_REQUEST['p']);

$reported_post = new Post();
$reported_post->id = $reported_id;

if (!$reported_post->read())
    peta();

do_header('Reportar notita');

$you = $current_user->user_login;
$your_id = $current_user->user_id;
$reportee = $reported_post->username;

?>

<style type="text/css">
.genericform a { font-weight: bold; color: #cc3300; }
label { font-weight: normal; }
input[type=submit] { margin: 0 auto; display: block; }
</style>

<br/><br/>

<div id="singlewrap"><div class="genericform"><div class="genericform">
<h4>Reportar notita</h4>
<fieldset class="fondo-caja espaciador borde">
<b>- REPORTE ENVIADO POR: <a href="<?php echo get_user_uri($you); ?>"><?php echo $you; ?></a></b><br/>
- NOTITA <a href="<?php echo post_get_base_url($reportee).'/'.$reported_id; ?>">#<?php echo $reported_id; ?></a> | USUARIO: <a href="<?php echo get_user_uri($reportee); ?>"><?php echo $reportee; ?></a><br/><br/>

<form action="report.php" method="POST">

<strong>Comentario sobre el reporte:</strong><br/>
<textarea style="width: 500px; height: 100px;" name="textie"></textarea><br/><br/>

<b>Tipo de reporte:</b><br/>

<input id=1 type="radio" value="1" name="tipo"><label for=1>SPAM (Anuncios de webs, cadenas de referidos o similares)</label><br>
<input id=2 type="radio" value="2" name="tipo"><label for=2>TROLL (Cuenta para trollear)</label><br>
<input id=5 type="radio" value="5" name="tipo"><label for=5>FLOOD (Contenido repetitivo)</label><br>
<input id=3 type="radio" value="3" name="tipo"><label for=3>CONTENIDO (Contenido inadecuado)</label><br>
<input id=4 type="radio" value="4" name="tipo"><label for=4>OTROS (Detalle en comentario)</label><br>

<br/>

Información sobre reportes/avisos: <b>El aviso será revisado próximamente.</b>

<b>Control automático:</b> si se ha realizado un uso correcto del sistema, el aviso también será analizado de forma instantánea por un control automático, para conseguir mayor efectividad y la máxima velocidad de actuación posible.

[ Condiciones de aplicación: <b>+6 meses de antigüedad</b> y <b>uso correcto en los últimos 90 días</b> ]

<br/><br/>

<span style="color: red;">
<b>Nota:</b> Usos inadecuados (reportes sin justificación, reportes desde cuentas clon, organizados, etc)
podrán afectar negativamente a la cuenta emisora del reporte [ <a href="<?php echo get_user_uri($you); ?>"><?php echo $you; ?></a> | ID: <b><?php echo $your_id; ?></b> ] y provocaran que durante
90 dias (n1) o indefinidamente (n2) los nuevos avisos desde esta cuenta sean ignorados por el sistema de
control automático.
</span>

<br/><br/>

<input type="submit" value="Enviar reporte"/>

<input type="hidden" value="1" name="done"/>

</form>

<br/>

</fieldset></div></div>

<?php

do_footer();