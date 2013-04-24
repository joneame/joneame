<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (! defined('mnmpath')) {
	include_once('../config.php');
	header('Content-Type: text/html; charset=utf-8');
}


if (!empty($_GET['id'])) {	
	$id = intval($_GET['id']);	
} else {	
	die;
}

include_once(mnminclude.'encuestas.php');

$encuesta = new Encuesta;
$encuesta->id=$id;
$encuesta->read_basic();

if(!$encuesta->read)  die;  

if ($encuesta->avatar) {
    	echo '<img src="'.get_avatar_url($encuesta->autor, $encuesta->avatar, 40).'" width="40" height="40" alt="avatar" style="float:left; margin: 0 5px 4px 0;"/>';
}

echo '<strong>' . $encuesta->nick . '</strong>, votos totales: '.$encuesta->votos_totales.'<br/>';

echo put_smileys(save_text_to_html(mb_substr($encuesta->contenido, 0, 1000)));