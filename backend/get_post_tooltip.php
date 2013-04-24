<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (! defined('mnmpath')) {
	include_once('../config.php');
	header('Content-Type: text/html; charset=utf-8');
}
include_once(mnminclude.'post.php');
include_once(mnminclude.'user.php');

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

		$usuario = new User;
		$usuario->username=$user;
		if (! $usuario->read()) {
			echo '<strong>Aviso: </strong>' . _('usuario o nota no encontrada');
			die;
			}
		if ($usuario->avatar) 
			echo '<div style="float: left;"><img style="margin-right: 5px" src="'.get_avatar_url($usuario->id, $usuario->avatar, 80).'" width="80" height="80" alt="'.$usuario->username.'"/></div>';
		echo '<strong>' . _('usuario') . ':</strong>&nbsp;' . $usuario->username;
		if ($current_user->user_id > 0 && $current_user->user_id  != $usuario->id)  {
			echo '&nbsp;' . friend_teaser($current_user->user_id, $usuario->id);
		}
		echo '<br/>';
		if ($usuario->estado) echo '<strong>' . $usuario->username . '</strong>&nbsp;' . $usuario->estado . '<br/>';
		if ($usuario->names) echo '<strong>' . _('nombre') . ':</strong>&nbsp;' . $usuario->names . '<br/>';
		if ($usuario->url) echo '<strong>' . _('web') . ':</strong>&nbsp;' . $usuario->url . '<br/>';
		echo '<strong>' . _('carisma') . ':</strong>&nbsp;' . $usuario->karma . '<br/>';
		echo '<strong>' . _('ranking') . ':</strong>&nbsp;#' . $usuario->ranking() . '<br/>';
		echo '<strong>' . _('desde') . ':</strong>&nbsp;' . get_date($usuario->date) . '<br/>';

		if ($current_user->user_id > 0 && $current_user->user_id != $usuario->id && ($her_latlng = geo_latlng('user', $usuario->id)) && ($my_latlng = geo_latlng('user', $current_user->user_id))) {
			$distance = (int) geo_distance($my_latlng, $her_latlng);
			echo '<strong>'._('distancia') . ':</strong>&nbsp;' . $distance . '&nbsp;kms<br/>';
		}

		$last_post = $db->get_var("SELECT post_id FROM posts WHERE post_user_id=$user->id ORDER BY post_id DESC LIMIT 1");
	
		if ($last_post > 0) {

			$post = Post::From_db(intval($last_post));    
			echo '<div id="addpost"></div>';
			echo '<ol class="comments-list" id="last_post">';   
			$post->print_summary(); 
			echo '<br/>';  
			echo "</ol>\n";	
		} 
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
$post->read_basic();

if(!$post->read) die;

if ($post->avatar && $post->tipo != 'admin') echo '<img src="'.get_avatar_url($post->author, $post->avatar, 40).'" width="40" height="40" alt="avatar" style="float:left; margin: 0 5px 5px 0;"/>';
  
		
if ($post->tipo != 'admin')
	echo '<strong>' . $post->username . '</strong> carisma: '.$post->karma.' ('.$post->src.')<br/>';
else	echo '<strong>' . get_server_name(). '</strong><br/>';
		
echo $post->print_text();