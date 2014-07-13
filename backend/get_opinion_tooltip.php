<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (! defined('mnmpath')) {
    include_once('../config.php');
    header('Content-Type: text/html; charset=utf-8');
}
include_once(mnminclude.'opinion.php');

if (!empty($_GET['id'])) {
    if (!empty($_GET['link'])) {
        $link = intval($_GET['link']);
        $order = intval($_GET['id']);
        $id = $db->get_var("select id from polls_comments where encuesta_id=$link and orden=$order");
        if (! $id > 0) die;
    } else {
        $id = intval($_GET['id']);
    }
} else {
    die;
}

$comment = new Opinion;
$comment->id = $id;
$comment->read();

if ($comment->avatar) {
        echo '<img src="'.get_avatar_url($comment->por, $comment->avatar, 40).'" width="40" height="40" alt="avatar" style="float:left; margin: 0 5px 4px 0;"/>';
}

echo '<strong>' . $comment->user_login . '</strong><br/>';

echo put_smileys(save_text_to_html(mb_substr($comment->contenido, 0, 1000)));