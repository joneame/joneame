<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('../config.php');
include(mnminclude.'link.php');
include(mnminclude.'ban.php');

header('Content-Type: text/plain; charset=UTF-8');

$user = intval($_REQUEST['user']);

if(check_ban_proxy()) {
    error(_('IP no permitida'));
}

if(!($id=check_integer('id'))) {
    error(_('falta el ID de la noticia'));
}

if(empty($user) && $user != '0' ) {
    error(_('falta el código de usuario'));
}

$link = new Link;
$link->id = $id;
$link->read_basic();

if(!$link->read) {
    error(_('historia inexistente'));
}

if(!$link->is_votable()) {
    error(_('votos cerrados'));
}

if (!$link->votos_permitidos) {
    error(_('votos bloqueados'));
}

if (!$link->sent) {
    error(_('la historia no ha sido enviada'));
}

// Only if the link has been not published, let them play
if ($current_user->user_id == 0) {
    if (! $anonnymous_vote) {
        error(_('los votos anónimos están temporalmente deshabilitados'));
    } else {
        // Check that there are not too much annonymous votes
        if ($link->status == 'published') $anon_to_user_votes = max(3, $anon_to_user_votes); // Allow more ano votes if published.
        if ($link->anonymous >  $link->votes * $anon_to_user_votes) {
            error(_('demasiados votos anónimos para esta noticia, regístrate como mafioso o espera a que más usuarios registrados la voten'));
        }
    }
}

if($current_user->user_id != $user) {
    error(_('usuario incorrecto: '). $current_user->user_id . ' vs '. htmlspecialchars($_REQUEST['user']));
}

if($current_user->user_id > 0) {
    $value = ($current_user->user_karma > 22) ? 22 : $current_user->user_karma;
}

if($current_user->user_id == 0) {
    $value=$anon_carisma;
}

$link->insert_aleatorio = false;

if (!$link->insert_vote($value)) {
    error(_('ya has votado antes esta historia'));
}

if ($link->status == 'discard' && $current_user->user_id > 0 && $link->votes > $link->negatives && $link->karma > 0) {
    $link->read_basic();
    $link->status = 'queued';
    $link->store_basic();
}

echo $link->json_votes_info(intval($value));

function error($mess) {
    $dict['error'] = $mess;
    echo json_encode($dict);
    die;
}