<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include_once(mnminclude.'ban.php');
include_once(mnminclude.'user.php');
include_once(mnminclude.'comment.php');
include_once(mnminclude.'link.php');

header('Content-Type: text/plain; charset=UTF-8');
stats_increment('api', true);

if (check_ban_proxy()) {
    echo 'KO: ' . _('IP no permitida');
    die;
}

$user = new User;
$comment = new Comment;
$link = new Link;

if (empty($_REQUEST['user'])) {
    echo 'KO: ' . _('usuario no especificado');
    die;
}

if (empty($_REQUEST['link'])) {
    echo 'KO: ' . _('enlace no especificado');
    die;
}

$user->username = $_REQUEST['user'];
if (!$user->read()) {
    echo 'KO: ' . _('no se pudo leer al usuario');
    die;
}

$link->uri = $_REQUEST['link'];
if (!$link->read('uri')) {
    echo 'KO: ' . _('no se pudo leer la noticia');
    die;
}

if ($user->level == 'disabled') {
    echo 'KO: ' . _('usuario deshabilitado');
    die;
}

if ( !($user->karma > 7) && $user->level == 'normal') {
    echo 'KO: ' . _('el carisma es muy bajo, necesitas más de 7');
    die;
}

if ($user->give_api_key() != $_REQUEST['key']) {
    echo 'KO: ' . _('clave del API incorrecta');
    die;
}

$text = clean_text($_REQUEST['text'], 0, false);

if (mb_strlen($text) < 5) {
    echo 'KO: ' . _('texto muy corto') . $text;
    die;
}

$comment->author=$user->id;
$comment->content=$text;
$comment->link=$link->id;
$comment->randkey=rand(0,9999999);

$comment->store();
$comment->insert_vote();
$link->update_comments();
$link->read();

echo 'OK';
