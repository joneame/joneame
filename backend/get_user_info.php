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
include_once(mnminclude.'user.php');
include_once(mnminclude.'post.php');


if (empty($_GET['id'])) die;
$id = intval($_GET['id']);
$user = new User;
$user->id=$id;
if (! $user->read()) die;
if ($user->avatar)
    echo '<div style="float: left;"><img style="margin-right: 5px" src="'.get_avatar_url($user->id, $user->avatar, 80).'" width="80" height="80" alt="'.$user->username.'"/></div>';
echo '<strong>' . _('usuario') . ':</strong>&nbsp;' . $user->username;
if ($current_user->user_id > 0 && $current_user->user_id  != $user->id)  {
    echo '&nbsp;' . friend_teaser($current_user->user_id, $user->id);
}
echo '<br/>';
if ($user->estado) echo '<strong>' . $user->username . '</strong>&nbsp;' . $user->estado . '<br/>';
if ($user->names) echo '<strong>' . _('nombre') . ':</strong>&nbsp;' . $user->names . '<br/>';
if ($user->url) echo '<strong>' . _('web') . ':</strong>&nbsp;' . $user->url . '<br/>';
echo '<strong>' . _('carisma') . ':</strong>&nbsp;' . $user->karma . '<br/>';
echo '<strong>' . _('ranking') . ':</strong>&nbsp;#' . $user->ranking() . '<br/>';
echo '<strong>' . _('desde') . ':</strong>&nbsp;' . get_date($user->date) . '<br/>';

$post = new Post;
if ($post->read_last($user->id)) {
    echo '<br clear="left"><strong>'._('última nota').'</strong>: ';
    $post->print_text();
}