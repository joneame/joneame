<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

do_header(_('Sitio bloqueado - Blocked site | Jonéame'));
?>

<div id="singlewrap"><div class="genericform"><div class="genericform">
<h4>Este sitio ha sido bloqueado - This site has been blocked</h4>
<fieldset class="fondo-caja espaciador borde">Los enlaces desde este sitio han sido bloqueados por abuso o falta de 
autorización.<br/><br/>
<dt>Posibles razones:</dt>
	<dd><b>1.</b> La web no está autorizada a enlazar este sitio web.</dd>
        <dd><b>2.</b> Se han detectado abusos y se ha bloqueado el acceso.</dd>
	<dd><b>3.</b> La administración ha decidido bloquear este sitio.</dd>
<dt>Sitio desde el cual intentas acceder:</dt>
<dd><?php echo !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Desconocido'; ?></dd>
<br/><br/>
Si crees que ha sido un error o quieres más información, contacta con nosotros:
<em>ad<em></em>min&#64;jo<strong></strong>neame&#46;n<strong></strong>et</em>.<br/>
Para acceder a Jonéame, haz clic <a href="http://joneame.net/">aquí.</a>
<br/><br/><hr/><br/>
Links from this site were blocked due to abuse or lack of autorization.
<br/><br/>
<dt>Possible reasons:</dt>
	<dd><b>1.</b> The site is not autorized to link us.</dd>
        <dd><b>2.</b> Abuses were detected so the access was blocked.</dd>
	<dd><b>3.</b> Our administration decided to block this site.</dd>
<dt>Site from which you tried to access:</dt>
<dd><?php echo !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Unknown'; ?></dd>
<br/><br/>
If you believe this site was blocked in error or you want further information, contact us:
<em>ad<em></em>min&#64;jo<strong></strong>neame&#46;n<strong></strong>et</em>.<br/>
To access Jonéame, click <a href="http://joneame.net/">here.</a>
</fieldset></div></div>

<?php
do_footer();
