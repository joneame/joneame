<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'html1.php');
include(mnminclude.'historial.class.php');


if ($current_user->admin) {
	do_header(_('Administración de historial'));
	
	if ($_POST) {
		 if (empty($_POST['who']) or empty($_POST['nota']))
		 	die("Pon algo");
		else {
			$historial = new Historial;
			$historial->who = intval($_POST['who']);
			$historial->texto = $db->escape($_POST['nota']);
			$historial->insert();
			echo '<p class="error"><strong>'._('Historial').'</strong></p> ';
		        echo '<p>'._('la nota ha sido correctamente agregada al historial'). ' </p>';
			do_footer();
			die;
		}
	}
	
	echo '<br/><br/>';
	echo '<div class="genericform" style="margin:10px; text-align: center">';
	echo '<form action="historial.php" method="post" id="bidali" name="bidali">';

	echo '<label>A&ntilde;adir nota para: </label>';
	echo '<select name="who" id="who">';
		
	// Menuda liada que viene aquí
	$usuarios = $db->get_results("SELECT * FROM users ORDER BY user_login ASC");
		
	foreach ($usuarios as $usuario) 
		echo '<option value="'.$usuario->user_id.'">'.$usuario->user_login.'</option>';
		
	echo '</select>';
	echo '<br/><br/><label>Nota:</label>';
	echo '<textarea name="nota" id="nota"></textarea>';
	echo '<p><input class="button" type="submit" value="'._('Añadir nota');

	echo '" />&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span></p>';
	echo '</form>';
	echo '</div>';
	
	echo '<br/><br/><br/>';
} else {
	 do_error(_('Esta página es sólo para administradores, sal de aqui, cojones ya. <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>Le llamaremos a Chuck Norris para que te de una patada giratoria. O sino a Jack Bauer, que &uacute;ltimamente Chuck anda muy ocupado resolviendo conflictos con los chinos.'), 403);
	
}
echo "</div>";
echo "</div>"; // singlewrap
do_footer();