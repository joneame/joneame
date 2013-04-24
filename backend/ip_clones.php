<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include_once('../config.php');

if (!$current_user->admin) {
    echo _('usuario no autorizado');
    die;
}

$user_id = intval($_GET['id']);
if (! $user_id > 0) {
    echo _('usuario no identificado');
    die;
}


$clones = $db->get_results("select distinct user_login, clon_ip from clones, users where clon_from = $user_id and user_id = clon_to order by clon_date desc limit 20");
if (! $clones) {
    print _('no hay ning&uacute;n clon'); //no hay ningun clon
    die;
}
echo '<div style="width:550px;padding: 5px 5px;text-align:left">';
echo '<div style="padding-top: 20px;min-width:350px">';

	foreach ($clones as  $clon) {
		if (preg_match('/COOK:/i', $clon->clon_ip)){
         if ($hay !=1)
		echo '<li>no hay clones por IP</li>';//hay clones pero no son por IP (son por cookie)
	$hay = 1; //solo muestra 1 linea
	}
		else  {
			echo '<ul>';
			echo '<li><a href="'.get_user_uri($clon->user_login).'">'.$clon->user_login." </a>(".$clon->clon_ip.")</li>\n";
			echo '</ul>';
		}
	}
echo '</div>';
echo '</div>';