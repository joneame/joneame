<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (! defined('mnmpath')) {
    include_once('../config.php');
    header('Content-Type: text/html; charset=utf-8');
}
include_once(mnminclude.'link.php');


if (empty($_GET['id'])) die;
$id = intval($_GET['id']);

$link = new Link;
$link->id = $id;
$link->read_basic();

$positivos = $link->votes+$link->aleatorios_positivos+$link->anonymous;

$user_login = $db->get_row("select user_login as user, user_avatar from users where user_id = $link->author");
echo '<p>';
if ($user_login->user_avatar) {
    echo '<img src="'.get_avatar_url($link->author, $user_login->user_avatar, 40).'" width="40" height="40" alt="avatar"  style="float:left; margin: 0 5px 0 0;"/>';
}
echo '<strong>' . $link->title . '</strong><br/>';
echo _('por').'&nbsp;<strong>' .$user_login->user. '</strong><br/>';
echo _('carisma').': '. intval($link->karma).' | '._('sensuras'). ': '. $link->negatives. ' | '._('votos').': '. $positivos.'</p>';
echo text_to_html($link->content);