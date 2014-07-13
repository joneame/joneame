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
$link = Link::from_db($id);

if(!$link->read) die;
echo '<img vspace="4" alt="websnapr.com" src="http://images.websnapr.com/?size=S&amp;url='.$link->url.'" width="202" height="152"/>';
echo '<br />';
if (!empty($link->url_title)) {
    echo '<strong>'.$link->url_title.'</strong>';
} else {
    echo '<strong>'.$link->title.'</strong>';
}