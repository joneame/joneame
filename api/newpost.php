<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('../config.php');
include_once(mnminclude.'ban.php');
include_once(mnminclude.'user.php');
include_once(mnminclude.'post.php');

header('Content-Type: text/plain; charset=UTF-8');
stats_increment('api', true);



if (check_ban_proxy()) {
    echo 'KO: ' . _('IP no permitida');
    die;
}

$user = new User;
$post = new Post;
if (empty($_REQUEST['user'])) {
    echo 'KO: ' . _('usuario no especificado');
    die;
}

$user->username = $_REQUEST['user'];
if (!$user->read()) {
    echo 'KO: ' . _('no se pudo leer al usuario');
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


$post = new Post;

if(!empty($_REQUEST['charset']) && ! preg_match('/utf-*8/i', $_REQUEST['charset'])) {
    $_REQUEST['text'] = @iconv($_REQUEST['charset'], 'UTF-8//IGNORE', $_REQUEST['text']);
}

$text = clean_text($_REQUEST['text'], 0, false, 300);

if (mb_strlen($text) < 5) {
    echo 'KO: ' . _('texto muy corto') . $text;
    die;
}

// Testinf mode print message an die
if (isset($_REQUEST['test'])) {
    echo 'OK: ' . $text;
    die;
}

$post->author=$user->id;
$post->src='api';
$post->content=$text;

if($post->same_text_count(60) > 0) {
        echo 'KO: ' . _('nota previamente grabada');
        die;
};

// Verify that there are a period of 1 minute between posts.
if(intval($db->get_var("select count(*) from posts where post_user_id = $user->id and post_date > date_sub(now(), interval 1 minute)"))> 0) {
        echo 'KO: ' . _('debe esperar 1 minuto entre notas');
        die;
};

$same_links = $post->same_links_count();
if ($same_links > 2) {
    $reduction = $same_links * 0.2;
    $user->karma = $user->karma - $reduction;
    syslog(LOG_NOTICE, "Joneame: newpost decreasing $reduction of karma to $user->username (now $user->karma)");
    $user->store();
}


$post->store();
echo 'OK: ' . _('nota grabada');