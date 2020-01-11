<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('../config.php');
include(mnminclude.'user.php');
include(mnminclude.'post.php');
include(mnminclude.'html1.php');

$globals['search_options'] = array('w' => 'posts');

array_push($globals['extra_js'], 'jquery-form.pack.js');
array_push($globals['extra_js'], 'posts01.js');

$user=new User();
$user->username = false;

$url_args = false;
$post_id = 0;

if (!isset($_REQUEST['id']) && !empty($_SERVER['PATH_INFO'])) {
    $url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
    $option = $url_args[1]; // The first element is always a "/"
    if (!empty($url_args[2]))
    $post_id = intval($url_args[2]);
} else {
    $url_args = preg_split('/\/+/', $_REQUEST['id']);
    $option = $url_args[0];
    $post_id = intval($url_args[1]);
}

$min_date = date("Y-m-d H:00:00", time() - 192800 *2); //  about 48*2 hours
$page_size = 60;
$offset=(get_current_page()-1)*$page_size;

$rss_option = '';
$js_ans_link = false;

switch ($option) {
    case '':
    case '_all':
        $tab_option = 1;
               $titulo = _('Notitas');
        $sql = "SELECT post_id FROM posts ORDER BY post_last_answer DESC, post_id DESC limit $offset,$page_size";
        $rows = $db->get_var("SELECT count(*) FROM posts where post_date > '$min_date'");
        break;

    case '_amigos':
        if ($current_user->user_id > 0) {
            $tab_option = 3;
            $titulo = _('Notitas de amigos');
            $sql = "SELECT post_id FROM posts, friends WHERE friend_type='manual' and friend_from = $current_user->user_id and friend_to=post_user_id and friend_value > 0 ORDER BY post_id desc limit $offset,$page_size";
            $rows = $db->get_var("SELECT count(*) FROM posts, friends WHERE friend_type='manual' and friend_from = $current_user->user_id and friend_to=post_user_id and friend_value > 0");
        } else {
            $tab_option = 1;
            $sql = "SELECT post_id FROM posts ORDER BY post_id desc limit $offset,$page_size";
            $rows = $db->get_var("SELECT count(*) FROM posts");
        }
        $rss_option="?friends_of=$current_user->user_id";
        break;
        case '_favorites':
        if ($current_user->user_id > 0) {
            $tab_option = 7;
        $titulo = _('Notitas favoritas de ').$current_user->user_login;
            $sql = "SELECT post_id FROM posts, favorites WHERE favorite_user_id=$current_user->user_id AND favorite_type='post' AND favorite_link_id=post_id ORDER BY post_id DESC LIMIT $offset,$page_size";
            $rows = $db->get_var("SELECT count(*) FROM favorites WHERE favorite_user_id=$current_user->user_id AND favorite_type='post'");
        } else {
            $tab_option = 1;
            $sql = "SELECT post_id FROM posts ORDER BY post_id desc limit $offset,$page_size";
            $rows = $db->get_var("SELECT count(*) FROM posts where post_date > '$min_date'");
        }
        $rss_option="";
        break;
     case '_conversacion':
        if ($current_user->user_id > 0) {
            $tab_option = 6;
        $titulo = _('Conversación de ').$current_user->user_login;
            $sql = "SELECT conversation_from as post_id FROM conversations, posts WHERE conversation_user_to=$current_user->user_id and conversation_type='post' and post_id = conversation_from ORDER BY conversation_time desc LIMIT $offset,$page_size";
            $rows =  $db->get_var("SELECT count(*) FROM conversations, posts WHERE conversation_user_to=$current_user->user_id and conversation_type='post' and post_id = conversation_from");
        } else {
            $tab_option = 1;
            $sql = "SELECT post_id FROM posts ORDER BY post_id desc limit $offset,$page_size";
            $rows = $db->get_var("SELECT count(*) FROM posts where post_date > '$min_date'");
        }
    $js_ans_link = true;
        $rss_option="?conversation_of=$current_user->user_id";
        break;
    default:
        $tab_option = 4;

        if ( $post_id > 0 ) {
            $user->id = $db->get_var("select post_user_id from posts where post_id=$post_id AND post_type != 'admin'");
            if(!$user->read() && $option != 'admin') { //si es notita admin no busca usuario, y permite buscar la historia
                  do_error(_('usuario no encontrado'), 404);
            }
            if ($user->username != $option && $option != 'admin') { // $option == username | si es notita admin no busca usuario, y permite buscar la historia
                header('Location: '.post_get_base_url($user->username).'/'.$post_id);
                die;
            }
        $titulo = _('Notita de ').$user->username;
            $sql = "SELECT post_id FROM posts WHERE post_id = $post_id";
            $rows = 1;
        $js_ans_link = true;
        } else {
            $user->username = $db->escape($option);
        $titulo = _('Notitas de ').$user->username;
            if(!$user->read() && $user->username != 'admin') { //si es notita admin no busca usuario, y permite buscar la historia
               do_error(_('usuario no encontrado'), 404);
            }
            if ($option != 'admin'){
            $sql = "SELECT post_id FROM posts WHERE post_user_id=$user->id AND post_type != 'admin' ORDER BY post_id desc limit $offset,$page_size";
            $rows = $db->get_var("SELECT count(*) FROM posts WHERE post_user_id=$user->id AND post_type != 'admin' ");
            }
            else if ($option == 'admin'){
                 $sql = "SELECT post_id FROM posts WHERE post_type = 'admin' ORDER BY post_id desc limit $offset,$page_size";
                 $rows = $db->get_var("SELECT count(*) FROM posts WHERE post_type = 'admin' ");
            }
        }
    $rss_option="?user_id=$user->id";
}

$globals['ads'] = true;

do_header($titulo . ' | Jonéame');
do_posts_tabs($tab_option, $user->username);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
echo '<br/>';
if ($rows > 20) {
    do_best_posts();
    do_best_comments();
}

echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

echo '<div class="notes">';

$post = new Post;
$post->print_post_teaser($rss_option);

$posts = $db->get_col($sql);
if ($posts) {
    echo '<ol class="notitas-list">';
    foreach ($posts as $dbpost) {
        $post = Post::from_db($dbpost);
        if ( $post_id > 0 && $user->id > 0 && $user->id != $post->author) {
            echo '<li>'. _('Error: la notita no existe') . '</li>';
        } else {

            $original_id = $post->id;
            if ($tab_option == 1 || $js_ans_link) $post->can_answer = true;
            if (!$post->is_answer || $tab_option == 6 || $tab_option == 4) $post->print_summary();

            if (!$post->is_answer) {
                $sql = "SELECT answer_post_id as post_id FROM answers WHERE answer_from = $post->id";
                $respuestas = $db->get_col($sql);

                if ($respuestas) {
                    //TODO echo '<p align="right"><a  id="show-hide-'.$original_id.'" href="javascript:hide_answers('.$original_id.')"> Ocultar</a></p><br/>';
                    $answer = new Post;
                    echo '<div id="respuestas-'.$original_id.'" class="replies">'."\n";
                    echo '<ol class="notitas-list">';
                    foreach ($respuestas as $dbanswer) {
                        $answer = Post::from_db($dbanswer);
                        if ($tab_option == 1 || $js_ans_link == true) $answer->can_answer = true;
                        $answer->print_summary();
                    }
                    echo "</ol>\n";
                    echo '</div>'."\n";
                }
            }

            // buscamos la notita padre, el JS imprime la opción de respuesta en la notita madre y estas pestañas no contienen notitas padres
            if (($tab_option == 4 && $post->is_answer()) || ($tab_option == 6 && $post->is_answer())) $original_id = $db->get_var("SELECT answer_from FROM answers WHERE answer_post_id=$post->id");

            if (!$post->is_answer() || $js_ans_link && $tab_option ==6 || $tab_option == 4) echo '<div id="respuesta-'.$original_id.'"></div>';

        }
    }
    echo "</ol>\n";

// Update conversation time
    if ($tab_option == 6) {
        Post::update_read_conversation();
    }
}
echo '</div>';
do_pages($rows, $page_size);

echo '</div>';
do_footer();
