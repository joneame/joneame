<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once(mnminclude.'user.php');
require_once(mnminclude.'cortos.class.php');
require_once(mnminclude.'utils.php');
global $current_user;

$corto = new Corto;
$corto->get_random();

if ($corto->aux->status) {
	$izena = $corto->aux->value2;
} else {
	$izena = '<a href='.get_user_uri($corto->por, 'cortos').' target="_blank">'.$corto->por.'</a>';
}

$info =  _('v').': <span id="vc-'.$corto->id.'">'.$corto->votos.'</span>, '._('c').': <span id="vk-'.$corto->id.'">'.$corto->carisma.'</span>';

if ($current_user->user_id > 0 && $corto->id_autor != $current_user->user_id) {
	$corto->iconos_votos();
}

echo ' <a href="'.get_corto_uri($corto->id).'" class="moar">';
echo '#';
echo '</a> ';

$texto = clean_text($corto->texto);
$cortado = text_sub_text($texto, 115);

echo $izena.': '.$cortado;

if (strlen($cortado) < strlen($texto)) {
	echo ' <a href="'.get_corto_uri($corto->id).'" class="moar">[Más]</a>';
}

if ($corto->votos > 0) {
	echo '<span>';

	$corto->info_votos();
	echo ' '.$info.'</span>';
}