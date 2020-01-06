<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

if (! defined('mnmpath')) {
    include_once('../config.php');
    header('Content-Type: text/html; charset=utf-8');
}

include_once('pager.php');

global $db, $globals, $current_user;

if (!isset($globals['link_id']) && !empty($_GET['id'])) {
    $globals['link_id'] = intval($_GET['id']);
}


if (!$globals['link_id'] > 0) die;

if (!isset($_GET['p']))  {
    $votes_page = 1;
} else $votes_page = intval($_GET['p']);

$votes_page_size = 20;
$votes_offset=($votes_page-1)*$votes_page_size;

$votes_users = $db->get_var("SELECT count(*) FROM votes WHERE vote_type='links' and vote_link_id=".$globals['link_id']." AND vote_user_id!=0");

$negatives = $db->get_results("select vote_value, count(vote_value) as count from votes where vote_type='links' and vote_aleatorio='normal' and vote_link_id=".$globals['link_id']." and vote_value < 0  group by vote_value order by count desc");

$aleatorios_positivos = $db->get_var("SELECT count(*) FROM votes where vote_type='links' AND vote_aleatorio='aleatorio' AND vote_link_id=".$globals['link_id']." AND vote_value>=0");
$aleatorios_negativos = $db->get_var("SELECT count(*) FROM votes where vote_type='links' AND vote_aleatorio='aleatorio' AND vote_link_id=".$globals['link_id']." AND vote_value<0");


if ($negatives) {

    echo '<div class="news-details">';
    echo '<strong>'._('votos sensuradores').':</strong>&nbsp;&nbsp;';

    foreach ($negatives as $negative) {
            echo get_negative_vote($negative->vote_value) . ':&nbsp;' . $negative->count;
            echo '&nbsp;&nbsp;';
    }
    echo '<br/>';

    if ($aleatorios_positivos == 0 && $aleatorios_negativos == 0) echo '</div>';

}

if ($aleatorios_positivos > 0|| $aleatorios_negativos > 0){
    if (!$negatives) echo '<div class="news-details">';
    echo '<strong>'._('votos aleatorios').': </strong>';
    echo 'positivos:'.$aleatorios_positivos.'&nbsp;&nbsp;';
    echo 'negativos:'.$aleatorios_negativos.'&nbsp;&nbsp;';
    echo '</div>';
}



$votes = $db->get_results("SELECT vote_user_id, vote_aleatorio, vote_value, user_avatar, user_login, date_format(vote_date,'%d/%m-%T') as date, UNIX_TIMESTAMP(vote_date) as ts,inet_ntoa(vote_ip_int) as ip FROM votes, users WHERE vote_type='links' and vote_link_id=".$globals['link_id']." AND vote_user_id > 0 AND user_id = vote_user_id ORDER BY vote_date DESC LIMIT $votes_offset,$votes_page_size");

if (!$votes) return;

echo '<div class="voters-list redondo fondo-caja">';

foreach ( $votes as $vote ){
    echo '<div class="item">';
    $vote_detail = $vote->date;
    // If current users is a god, show the first IP addresses
    if ($current_user->user_level == 'god') $vote_detail .= ' ('. $vote->ip. ')';

    if ($vote->vote_aleatorio == 'aleatorio') $aleatorio = ' (valor: '.$vote->vote_value.')';
    else $aleatorio = '';

    if ($vote->vote_value >=0 ) {
        $vote_detail .= ' '._('valor').":&nbsp;$vote->vote_value";
        echo '<a href="'.get_user_uri($vote->user_login).'" title="'.$vote->user_login.': '.$vote_detail.'">';
        echo '<img src="'.get_avatar_url($vote->vote_user_id, $vote->user_avatar, 20).'" width="20" height="20" alt=""/>';
        echo $vote->user_login.'</a> '.$aleatorio;
    } else {
            echo '<a href="'.get_user_uri($vote->user_login).'" title="'.$vote->user_login.': '.$vote_detail.'">';
            echo '<img src="'.get_avatar_url($vote->vote_user_id, $vote->user_avatar, 20).'" width="20" height="20" alt=""/></a>';
            echo '<span>'.get_negative_vote($vote->vote_value).'</span>'.$aleatorio;
    }
    echo '</div>';
}
echo "</div>\n";
do_contained_pages($globals['link_id'], $votes_users, $votes_page, $votes_page_size, 'votos.php', 'voters', 'voters-container');