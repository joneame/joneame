<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

/**** 
Description: it shows the comment votes in a div, used in modal windows via AJAX
***/

include_once('../config.php');
include_once('pager.php');
include_once(mnminclude.'html1.php');
//do_js_includes();
//header('Content-Type: text/html; charset=utf-8');

global $db;

if (!empty($_GET['id'])) {
	$id = intval($_GET['id']);
} 

if (! $id > 0 ) die;

if (!isset($_GET['p']))  {
	$votes_page = 1;
} else $votes_page = intval($_GET['p']);

$votes_page_size = 20;
$votes_offset=($votes_page-1)*$votes_page_size;


$comment = $db->get_row("select comment_votes, comment_user_id from comments where comment_id = $id");

echo '<div style="width:550px;padding: 5px 5px;text-align:left">';
echo '<div style="padding-top: 20px;min-width:350px">';
$votes = $db->get_results("SELECT vote_user_id, vote_value, user_avatar, user_login, date_format(vote_date,'%d/%m-%T') as date, UNIX_TIMESTAMP(vote_date) as ts,inet_ntoa(vote_ip_int) as ip FROM votes, users WHERE vote_type='comments' and vote_link_id=$id AND vote_user_id > 0 AND user_id = vote_user_id ORDER BY vote_date DESC LIMIT $votes_offset,$votes_page_size");
if ($votes) {
	echo '<div class="voters-list">';
	foreach ( $votes as $vote ) {

		if ($comment->comment_user_id == $vote->vote_user_id) continue;

		echo '<div class="item">';
		$vote_detail = "$vote->date carisma: $vote->vote_value";
		// If current users is a god, show the first IP addresses
		if ($current_user->user_level == 'god') $vote_detail .= ' ('. $vote->ip. ')';
		if ($vote->vote_value<0) $style = 'style="color: #f00"';
		else $style = '';
		echo '<a '.$style.' href="'.get_user_uri($vote->user_login).'" title="'.$vote->user_login.': '.$vote_detail.'" target="_blank">';
		echo '<img src="'.get_avatar_url($vote->vote_user_id, $vote->user_avatar, 20).'" width="20" height="20" alt=""/>';
		echo $vote->user_login.'</a>';
		echo '</div>';
	}
	echo "</div>\n";
}


do_contained_pages($id, $comment->comment_votes, $votes_page, $votes_page_size, 'mostrar_comentarios_votos.php', 'voters');
echo '</div>';
echo '</div>';