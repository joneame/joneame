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

include_once('pager.php');

global $db;

if (!isset($globals['link_id']) && !empty($_GET['id'])) {
    $globals['link_id'] = intval($_GET['id']);
}
if (! $globals['link_id'] > 0 ) die;

if (!isset($_GET['p']))  {
    $favorites_page = 1;
} else $favorites_page = intval($_GET['p']);

$favorites_page_size = 20;
$favorites_offset=($favorites_page-1)*$favorites_page_size;


$favorites_users = $db->get_var("SELECT count(*) FROM favorites WHERE favorite_link_id=".$globals['link_id']);

$favorites = $db->get_results("SELECT favorite_user_id, user_avatar, user_login, date_format(favorite_date,'%d/%m %T') as date FROM favorites, users WHERE favorite_link_id=".$globals['link_id']." AND user_id = favorite_user_id LIMIT $favorites_offset,$favorites_page_size");
echo '<div class="voters-list fondo-caja redondo">';
if ($favorites) {
    foreach ($favorites as $vote) {
        echo '<div class="item">';
        echo '<a href="'.get_user_uri($vote->user_login, 'favorites').'" title="'.$vote->date.'">';
        echo '<img src="'.get_avatar_url($vote->favorite_user_id, $vote->user_avatar, 20).'" width="20" height="20" alt="'.$vote->user_login.'"/>';
        echo $vote->user_login.'</a>';
        echo '</div>';
    }
} else {
    echo '<p class="noone">nadie ha marcado aún esta historia como favorita</p>';
}
echo "</div><br clear='left'/>\n";
do_contained_pages($globals['link_id'], $favorites_users, $favorites_page, $favorites_page_size, 'get_link_favorites.php', 'voters', 'voters-container');
?>
