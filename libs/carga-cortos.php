<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

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

echo ' <a href="'.get_corto_uri($corto->id).'" class="moar">';
echo '<img src="'.get_cover_pixel().'" class="icon permalink-mini" alt="permalink">';
echo '</a> ';

$texto = clean_text($corto->texto);
$cortado = text_sub_text($texto, 115);

echo $izena.': '.$cortado;

if (strlen($cortado) < strlen($texto)) {
    echo ' <a href="'.get_corto_uri($corto->id).'" class="moar">[Más]</a>';
}

if ($current_user->user_id > 0 && $corto->id_autor != $current_user->user_id) {
    $corto->iconos_votos();
}

if ($corto->votos > 0) {
    echo '<span>';

    $corto->info_votos();
    echo ' '.$info.'</span>';
}