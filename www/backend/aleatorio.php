<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'link.php');
include(mnminclude.'ban.php');

header('Content-Type: text/plain; charset=UTF-8');

if(check_ban_proxy()) {
    error(_('IP no permitida'));
}

if(!($id=check_integer('id'))) {
    error(_('Falta el ID del artículo'));
}

if(empty($_REQUEST['user']) && $_REQUEST['user'] !== '0' ) {
    error(_('Falta el código de usuario'));
}

if ($current_user->user_id == 0) {
    error(_('Debes registrarte para poder votar aleatoriamente'));
}

if ($current_user->user_karma < 7) {
    error(_('No tienes suficiente carisma para usar el aleatorio'));
}

$link = Link::from_db($id);

if(!$link->read) {
    error(_('historia inexistente'));
}

if (!$globals ['aleatorios_usuarios_activados']) {
    error(_('temporalmente no se puede votar aleatorio las noticias de los demás'));
}

if(!$link->is_votable()) {
	error(_('votos chapaus'));
}

if( $link->status != 'queued') {
    error(_('no se puede votar aleatorio si no está en cola'));
}

if($current_user->user_id != $_REQUEST['user']) {
	error(_('Usuario incorrecto'). $current_user->user_id . '-'. htmlspecialchars($_REQUEST['user']));
}


if ($link->aleatorios_count() >= $globals['aleatorios_maximos']) {
error(_('demasiados votos aleatorios'));
}

//votos aleatorios en los últimos 10 minutos
$aleatorios_user = (int) $db->get_var("select count(*) from votes where vote_type= 'links' AND vote_user_id=$current_user->user_id AND vote_aleatorio='aleatorio'  and vote_date > date_sub(now(), interval 10 minute ) ");

if ($aleatorios_user >= $globals['aleatorios_maximos_por_usuario'] ) {
error(_('Has usado demasiado el voto aleatorio en los últimos minutos'));
}

$value = ($current_user->user_karma > 20) ? '22' : $current_user->user_karma;

$link->insert_aleatorio = true;

if (!$link->insert_vote($value)) {
	error(_('ya has votado antes'));
}


if ($link->status == 'discard' && $link->votes > $link->negatives && $link->karma > 0) {
	$link->read_basic();
	$link->status = 'queued';
	$link->store_basic();
}
	
echo $link->json_votes_info_aleatorio(intval($value));

function error($mess) {
	$dict['error'] = $mess;
	echo json_encode($dict);
	die;
}
?>
