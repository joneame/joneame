<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

if (! defined('mnmpath')) {
    include_once('../config.php');
    header('Content-Type: text/html; charset=utf-8');
}
include_once(mnminclude.'post.php');
include_once(mnminclude.'user.php');
include_once(mnminclude.'geo.php');

if (!empty($_GET['id'])) {
    if (preg_match('/(.+)-(\d+)/u', $_GET['id'], $matches) > 0) {
        $id = 0;
        $date = $matches[2];
        $user_id = explode(',', $matches[1]);
        if (count($user_id) == 2) {
            $user = $db->escape($user_id[0]);
            $post_id = $user_id[1];
        } else {
            $user = $db->escape($matches[1]);
            $date = $matches[2];
            $post_id = 0;
        }

        if ($post_id) {
            $id = (int) $db->get_var("select post_id from posts, users where user_login = '$user' and post_type != 'admin' and post_user_id = user_id and post_id = $post_id and post_type != 'admin' order by post_date desc limit 1");
        }

        // In case of not found in previous case or postid was not given
        if (! $id) {
            $id = (int) $db->get_var("select post_id from posts, users where user_login = '$user' and post_type != 'admin' and post_user_id = user_id and post_date < FROM_UNIXTIME($date) and post_type != 'admin' order by post_date desc limit 1");
        }

        if (!$id > 0) {
            echo '<strong>Error: </strong>' . _('usuario o nota no encontrada');
            die;
        }
    } else {
        $id = intval($_GET['id']);
    }

} else {
    die;
}
$post = new Post;
$post->id=$id;
$post->read();
if(!$post->read) die;
if ($post->avatar)
    echo '<img class="avatar" src="'.get_avatar_url($post->author, $post->avatar, 40).'" width="40" height="40" alt="avatar" style="float:left; margin: 0 5px 5px 0;"/>';
echo '<strong>' . $post->username . '</strong><br/>';
echo $post->print_text();