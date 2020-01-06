<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('../config.php');
include(mnminclude.'ban.php');

header('Content-Type: text/plain; charset=UTF-8');

if(check_ban_proxy()) {
    error(_('IP no permitida'));
}

if(!($id=check_integer('id'))) {
    error(_('Falta el ID de la notita'));
}

if(empty($_REQUEST['user'])) {
    error(_('Falta el código de usuario'));
}

if($current_user->user_id != $_REQUEST['user']) {
    error(_('Usuario incorrecto'). $current_user->user_id . '-'. htmlspecialchars($_REQUEST['user']));
}

if (empty($_REQUEST['value']) || ! is_numeric($_REQUEST['value'])) {
    error(_('Falta valor del voto'));
}

if ($current_user->user_karma < $globals['min_karma_for_post_votes']) {
    error(_('Carisma bajo para votar notitas'));
}


$value = intval($_REQUEST['value']);

if ($value != -1 && $value != 1) {
    error(_('Valor del voto incorrecto'));
}

require_once(mnminclude.'votes.php');
$vote = new Vote;
$vote->user=$current_user->user_id;
$vote->type='posts';
$vote->link=$id;
if ($vote->exists()) {
    error(_('ya has votado antes'));
}

$votes_freq = intval($db->get_var("select count(*) from votes where vote_type='posts' and vote_user_id=$current_user->user_id and vote_date > subtime(now(), '0:0:30') and vote_ip_int = ".$globals['user_ip_int']));

$freq = 6;

if ($current_user->user_karma > 20) { //si tiene mas de 20 carisma el valor es siempre 21
$vote->value = $value * 22;
} else { //sino el voto vale el carisma del usuario
$vote->value = $value * $current_user->user_karma;
}

$votes_info = $db->get_row("select post_user_id, post_votes, post_karma, post_type, UNIX_TIMESTAMP(post_date) as date from posts where post_id=$id");

if ($votes_info->post_user_id == $current_user->user_id) {
    error(_('no puedes votar a tus notitas'));
}

if ($votes_info->date < time() - $globals['time_enabled_note_votes']) {
    error(_('votos cerrados'));
}

if ($votes_info->post_type == 'admin') {
    error(_('no puedes votar una notita admin'));
}

if (!$vote->insert()) {
    error(_('ya has votado antes'));
}

$votes_info->post_votes++;
$votes_info->post_karma += $vote->value;

if ($vote->value > 0) $dict['image'] = '-2px -868px'; // positivo
else $dict['image'] = '-2px -884px'; // negativo

$dict['id'] = $id;
$dict['votes'] = $votes_info->post_votes;
$dict['value'] = $vote->value;
$dict['karma'] = $votes_info->post_karma;

echo json_encode($dict);

$db->query("update posts set post_votes=post_votes+1, post_karma=post_karma+$vote->value where post_id=$id and post_user_id != $current_user->user_id");

function error($mess) {
    $dict['error'] = $mess;
    echo json_encode($dict);
    die;
}

?>
