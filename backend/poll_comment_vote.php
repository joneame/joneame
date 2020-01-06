<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('../config.php');
include(mnminclude.'ban.php');
require_once(mnminclude.'votes.php');

header('Content-Type: text/plain; charset=UTF-8');

$user = intval($_REQUEST['user']);

if(check_ban_proxy()) {
    error(_('IP no permitida'));
}

if(!($id=check_integer('id'))) {
    error(_('falta el ID del comentario'));
}

if(empty($user)) {
    error(_('falta el código de usuario'));
}

if($current_user->user_id != $user) {
    error(_('usuario incorrecto'));
}

if (empty($_REQUEST['value']) || ! is_numeric($_REQUEST['value'])) {
    error(_('falta el valor del voto'));
}

if ($current_user->user_karma < $globals['min_karma_for_comment_votes']) {
    error(_('carisma demasiado bajo como para votar comentarios'));
}

$value = intval($_REQUEST['value']);

if ($value != -1 && $value != 1) {
    error(_('valor del voto incorrecto'));
}


$vote = new Vote;
$vote->user=$current_user->user_id;
$vote->type='poll_comment';
$vote->link=$id;
$vote->aleatorio = false;

if ($vote->exists()) {
    error(_('ya has votado antes'));
}

if ($current_user->user_karma > 20) $vote->value = $value * 22;  //si tiene mas de 20 carisma el valor es siempre 21
else $vote->value = $value * $current_user->user_karma; //sino el voto vale el carisma del usuario

$votes_info = $db->get_row("select autor, votos, carisma, UNIX_TIMESTAMP(fecha) as date from polls_comments where id=$id");


if ($votes_info->autor == $current_user->user_id) {
    error(_('no puedes votar a tus comentarios'));
}

if ($votes_info->date < time() - $globals['time_enabled_votes']) {
    error(_('votos cerrados'.$votes_info->date));
}

if (!$vote->insert()) {
    error(_('ya has votado antes'));
}


$votes_info->votos++;
$votes_info->carisma += $vote->value;

if ($vote->value > 0) $dict['image'] = '-2px -868px'; // positivo
else $dict['image'] = '-2px -884px'; // negativo

$dict['id'] = $id;
$dict['votes'] = $votes_info->votos;
$dict['value'] = $vote->value;
$dict['karma'] = $votes_info->carisma;

echo json_encode($dict);

$db->query("update polls_comments set votos=votos+1, carisma=carisma+$vote->value where id=$id and autor != $current_user->user_id");

function error($mess) {
    $dict['error'] = $mess;
    echo json_encode($dict);
    die;
}