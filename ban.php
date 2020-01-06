<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');

if (!($bn = check_ban_proxy()))
    header('Location: https://'.get_server_name().$globals['base_url']);

do_header(_('Estás baneado | Jonéame'));

?>
<div id="singlewrap"><div class="genericform"><div class="genericform">
<h4>Estás baneado</h4>
<fieldset class="fondo-caja espaciador borde">Tu dirección IP ha sido bloqueada.<br /><br />

<?php
if ($bn['comment']) {
    echo '<dt>Motivo:</dt>';
    echo '<dd>'.$bn['comment'].'</dd>';
}
?>

<br />
Si crees que ha sido un error ponte en contacto con nosotros a través de
<em>ad<em></em>min&#64;jo<strong></strong>neame&#46;n<strong></strong>et</em>.
</fieldset></div></div>

<?php
do_footer();