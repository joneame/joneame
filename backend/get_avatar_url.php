<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include_once('../config.php');

$id = intval($_GET['id']);
if (! $id > 0) die;
$size = intval($_GET['size']);
if (!$size > 0) $size = 80;

$user=$db->get_row("select user_avatar from users where user_id=$id");
if ($user) {
    //header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . get_avatar_url($id, $user->user_avatar, $size));
}
die;