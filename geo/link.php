<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (! defined('mnmpath')) {
	include_once('../config.php');
	header('Content-Type: text/html; charset=utf-8');
	stats_increment('ajax');
}

include_once(mnminclude.'link.php');

if (empty($_GET['id'])) die;

$id = intval($_GET['id']);

$link = new Link;
$link->id = $id;
$link->read();

if(!$link->read) die;

echo '<div style="font-size:8.5pt;width:250px; margin-right:15px">';
if ($link->avatar) {
	echo '<img src="http://'.get_server_name(). get_avatar_url($link->author, $link->avatar, 40).'" width="40" height="40" alt="avatar"  style="float:left; margin: 0 5px 4px 0;"/>';
}
echo '<a href="'.$link->get_permalink().'" target="_blank"><strong>'.$link->title.'</strong></a><br clear="all"/>';
echo _('en').': '.$link->meta_name.', '.$link->category_name.' | '._('chorradas').':&nbsp;'.$link->comments.' | carisma:&nbsp;'. intval($link->karma). ' | '._('sensuras').':&nbsp;'. $link->negatives;
echo '</div>';