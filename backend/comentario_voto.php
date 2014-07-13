<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'ban.php');
require_once(mnminclude.'votes.php');
require_once(mnminclude.'comment.php');

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
$vote->type='comments';
$vote->link=$id;

if ($vote->exists()) {
    error(_('ya has votado antes'));
}

if ($current_user->user_karma > 20) $vote->value = $value * 22;  //si tiene mas de 20 carisma el valor es siempre 21
else $vote->value = $value * $current_user->user_karma; //sino el voto vale el carisma del usuario

$comment = new Comment;
$comment->id = $id;
$comment->read_basic();

if ($comment->type != 'normal') {
    error(_('no puedes votar un comentario especial'));
}

if ($comment->author == $current_user->user_id) {
    error(_('no puedes votar a tus comentarios'));
}

if ($comment->date < time() - $globals['time_enabled_votes']) {
    error(_('votos cerrados'));
}

if (!$vote->insert()) {
    error(_('ya has votado antes'));
}

$comment->votes++;

$comment->karma += $vote->value;

if ($vote->value > 0) $dict['image'] = '-2px -868px'; // positivo
else $dict['image'] = '-2px -884px'; // negativo


$dict['id'] = $id;
$dict['votes'] = $comment->votes;
$dict['value'] = $vote->value;
$dict['karma'] = $comment->karma;

echo json_encode($dict);

$db->query("update comments set comment_votes=comment_votes+1, comment_karma=comment_karma+$vote->value where comment_id=$id and comment_user_id != $current_user->user_id");

function error($mess) {
    $dict['error'] = $mess;
    echo json_encode($dict);
    die;
}