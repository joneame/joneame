<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'cortos.class.php');
include(mnminclude.'ban.php');
require_once(mnminclude.'votes.php');

header('Content-Type: text/plain; charset=UTF-8');

$value = intval($_REQUEST['value']);


if(!($id=check_integer('id'))) {
    error(_('falta el ID del corto'));
}

if ($value != -1 && $value != 1) {
    error(_('valor del voto incorrecto'));
}

if(empty($_REQUEST['user'])) {
    error(_('falta el código de usuario'));
}

if($current_user->user_id != $_REQUEST['user']) {
    error(_('usuario incorrecto'));
}

if($current_user->user_id == 0) {
    error(_('debes registrarte para votar cortos'));
}

if (empty($_REQUEST['value']) || ! is_numeric($_REQUEST['value'])) {
    error(_('falta valor del voto'));
}

if ($current_user->user_karma < $globals['carisma_para_votar_cortos']) {
    error(_('carisma demasiado bajo como para votar cortos'));
}

$corto = new Corto;
$corto->id = $id;
$corto->get_single();

if (!$corto->texto){
    error(_('el corto no existe'));
}

if ($corto->id_autor == $current_user->user_id) {
    error(_('no puedes votar tus propios cortos'));
}

if ($corto->activado == 0) {
    error(_('El corto está sin aprobar'));
}


$vote = new Vote;
$vote->user=$current_user->user_id;
$vote->type='cortos';
$vote->link=$id;
if ($vote->exists()) {
    error(_('ya has votado antes este corto'));
}


$vote->value = ($current_user->user_karma > 20) ? 22 * $value: $current_user->user_karma * $value;

if (!$vote->insert()) {
    error(_('ya has votado antes este corto'));
}



$corto->votos = $corto->votos + 1;
$corto->carisma =$corto->carisma  + $vote->value;




if ($vote->value > 0) $dict['image'] = '-2px -868px'; // positivo

else $dict['image'] = '-2px -884px'; // negativo



$dict['id'] = $id;
$dict['votes'] = $corto->votos;
$dict['value'] = $vote->value;
$dict['karma'] = $corto->carisma;



echo json_encode($dict);
$db->query("update cortos set votos=votos+1, carisma=carisma+$vote->value  where id=$id and por != $current_user->user_id");

function error($mess) {
    $dict['error'] = $mess;
    echo json_encode($dict);
    die;
}
