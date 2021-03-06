<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'post.php');

if(!empty($_REQUEST['rows'])) {
    $rows = intval($_REQUEST['rows']);
    if ($rows > 300) $rows = 100; //avoid abuses
} else $rows = 100;

$if_modified = get_if_modified();
if ($if_modified) {
    if ($if_modified < time() - 250000) { // Last 3 days at most
        $if_modified = time() - 250000;
    }
    $from_time = "post_date > FROM_UNIXTIME($if_modified)";
    $from_time_conversation = "conversation_date > FROM_UNIXTIME($if_modified)";
} $from_time = 'True'; // Trick to avoid sql errors with empty "and's"



if ($_REQUEST['q']) {
    include(mnminclude.'search.php');
    if ($if_modified) {
        $_REQUEST['t'] = $if_modified;
    }
    $_REQUEST['w'] = 'posts';
    $search_ids = do_search(true);
    if ($search_ids['ids']) {
        $ids = implode(",", $search_ids['ids']);
        $sql = "SELECT post_id FROM posts WHERE post_id in ($ids) ORDER BY post_id DESC LIMIT $rows";
        $last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(post_date) FROM posts WHERE post_id in ($ids) ORDER BY post_id DESC LIMIT 1");
    }
    $title = _('Jonéame: búsqueda en notas') . ': ' . htmlspecialchars(strip_tags($_REQUEST['q']));
    $globals['redirect_feedburner'] = false;
} elseif (!empty($_GET['user_id'])) {
    //
    // Users posts
    //
    $id = guess_user_id($_GET['user_id']);
    $username = $db->get_var("select user_login from users where user_id=$id");
    $sql = "SELECT post_id FROM posts WHERE post_user_id=$id and $from_time ORDER BY post_date DESC LIMIT $rows";
    $last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(post_date) FROM posts WHERE post_user_id=$id ORDER BY post_date DESC LIMIT 1");
    $title = _('Notitas: notas de ') . $username;
} elseif(!empty($_REQUEST['friends_of'])) {
    //
    // User's friend posts
    //
    $id = guess_user_id($_GET['friends_of']);
    $username = $db->get_var("select user_login from users where user_id=$id");
    $sql = "SELECT post_id FROM posts, friends WHERE friend_type='manual' and friend_from = $id and friend_to=post_user_id and friend_value > 0 and $from_time ORDER BY post_date DESC LIMIT $rows";
    $last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(post_date) FROM posts, friends WHERE friend_type='manual' and friend_from = $id and friend_to=post_user_id and friend_value > 0 ORDER BY post_date DESC LIMIT 1");
    $title = _('Notitas: notas amigos de ') . $username;
} elseif (!empty($_REQUEST['favorites_of'])) {
    /////
    // users' favorites
    /////
    $user_id = guess_user_id($_REQUEST['favorites_of']);
    $username = $db->get_var("select user_login from users where user_id=$user_id");
    $sql = "SELECT post_id FROM posts, favorites WHERE favorite_user_id=$user_id AND favorite_type='post' AND favorite_link_id=post_id ORDER BY favorite_date DESC limit $rows";
    $last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(max(favorite_date)) from favorites where favorite_user_id=$user_id AND favorite_type='post'");
    $title = _('Notitas: favoritos de ') . $username;
} elseif(!empty($_REQUEST['conversation_of'])) {
    //
    // Conversation posts
    //
    $id = guess_user_id($_GET['conversation_of']);
    $username = $db->get_var("select user_login from users where user_id=$id");
    $sql = "SELECT conversation_from as post_id FROM conversations WHERE conversation_user_to=$id and conversation_type='post' ORDER BY conversation_time desc LIMIT $rows";
    $last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(conversation_time) FROM conversations WHERE conversation_user_to=$id and conversation_type='post' ORDER BY conversation_time DESC LIMIT 1");
    $title = _('Notitas: conversación de ') . $username;
} else {
    //
    // All posts
    //
    $id = 0;
    $sql = "SELECT post_id FROM posts WHERE $from_time ORDER BY post_date DESC LIMIT $rows";
    $last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(post_date) FROM posts ORDER BY post_date DESC LIMIT 1");
    $title = _('Notitas: notas');
}


do_header($title);

$post = new Post;
$posts = $db->get_col($sql);
if ($posts) {
    foreach($posts as $post_id) {
        $post->id=$post_id;
        $post->read();
        $username = ($post->tipo == 'admin') ? 'admin' : $post->username;
        $title = strip_tags(text_to_summary($post->clean_content(), 40));
        $title = $username.': ' . htmlentities2unicodeentities($title);
        $content = htmlentities2unicodeentities(put_smileys(save_text_to_html($post->clean_content())));
        echo "  <item>\n";
        echo "      <title>$title</title>\n";
        echo "      <link>https://".get_server_name().post_get_base_url($username).'/'.$post->id."</link>\n";
        echo "      <pubDate>".date("r", $post->date)."</pubDate>\n";
        echo "      <dc:creator>$username</dc:creator>\n";
        echo "      <guid>https://".get_server_name().post_get_base_url($username).'/'.$post->id."</guid>\n";
        echo "      <description><![CDATA[$content";
        echo '</p><p>»&nbsp;'._('autor').': <strong>'.$username.'</strong></p>';
        echo "]]></description>\n";
        echo "  </item>\n\n";
    }
}

  echo "</channel>\n</rss>\n";

function do_header($title) {
    global $if_modified, $last_modified, $dblang, $globals;

    if (!$last_modified > 0) {
        if ($if_modified > 0)
            $last_modified = $if_modified;
        else
            $last_modified = time();
    }
    header('X-If-Modified: '. gmdate('D, d M Y H:i:s',$if_modified));
    header('X-Last-Modified: '. gmdate('D, d M Y H:i:s',$last_modified));
    if ($last_modified <= $if_modified) {
        header('HTTP/1.1 304 Not Modified');
        exit();
    }
    header('Last-Modified: ' .  gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
    header('Content-type: text/xml; charset=UTF-8', true);

    echo '<rss version="2.0" ';

    echo '  xmlns:content="http://purl.org/rss/1.0/modules/content/"';
    echo '  xmlns:wfw="http://wellformedweb.org/CommentAPI/"';
    echo '  xmlns:dc="http://purl.org/dc/elements/1.1/"';
    echo ' >'. "\n";
    echo '<channel>';
    echo '  <title>'.$title.'</title>';
    echo ' ';
    echo '  <link>https://'.get_server_name().post_get_base_url().'</link>';

    echo '  <description>'._('Sitio colaborativo de noticias nada serias').'</description>';
    echo '  <pubDate>'.date("r", $last_modified).'</pubDate>';
    echo '  <generator>jonéame</generator>';
    echo '  <language>'.$dblang.'</language>';
}

function do_footer() {
    echo "</channel>\n</rss>\n";
}