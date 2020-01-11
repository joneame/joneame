<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'link.php');
include(mnminclude.'html1.php');

$link = new Link;

if (!isset($_REQUEST['id']) && !empty($_SERVER['PATH_INFO'])) {
    $url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
    array_shift($url_args); // The first element is always a "/"

    // If the first argument are only numbers, redirect to the story with that id
    if (preg_match('/^0\d+$/', $url_args[0])) {
            $link->id = intval($url_args[0]);
            if ($link->read('id')) {
                header('Location: ' . $link->get_permalink());
                die;
            }
    }

    $link->uri = $db->escape($url_args[0]);

    if (!$link->uri) {
        do_error(_('no encontrado'), 404);
    }

    if (! $link->read('uri') ) {
        do_error(_('no encontrado'), 404);
    }
} else {
    $url_args = preg_split('/\/+/', $_REQUEST['id']);
    $link->id=intval($url_args[0]);
    if(is_numeric($url_args[0]) && $link->read('id') ) {
        // Redirect to the right URL if the link has a "semantic" uri
        if (!empty($link->uri) && !empty($globals['base_story_url'])) {
            header ('HTTP/1.1 301 Moved Permanently');
            if (!empty($url_args[1])) $extra_url = '/' . urlencode($url_args[1]);
            header('Location: ' . $link->get_permalink(). $extra_url);
            die;
        }
    } else {
        do_error(_('argumentos no reconocidos'), 404);
    }
}

/* neiko: quitar 404 de google, hack de la hostia */
if ($_SERVER['PATH_INFO'] == $_SERVER['REQUEST_URI']) {
    header('Location: '.$link->get_permalink());
    die;
}

if (!$link->sent && !$current_user->admin) {

     do_error(_('la historia no ha sido enviada'), 404);

} else if ($link->is_discarded()) {

    // Dont allow indexing of discarded links
    if ($globals['bot'])  do_error(_('error'), 404);

} else {
    //Only shows ads in non discarded images
    $globals['ads'] = true;
}


// Check for a page number which has to come to the end, i.e. ?id=xxx/P or /historia/uri/P
$last_arg = count($url_args)-1;

if ($last_arg > 0) {
    // Dirty trick to redirect to a comment' page
    if (preg_match('/^000/', $url_args[$last_arg])) {
        if ($url_args[$last_arg] > 0) {
            header('Location: ' . $link->get_permalink().get_comment_page_suffix($globals['comments_page_size'], (int) $url_args[$last_arg], $link->comments).'#comment-'.(int) $url_args[$last_arg]);
        } else {
            header('Location: ' . $link->get_permalink());
        }
        die;
    }
    if ($url_args[$last_arg] > 0) {
        $requested_page = $current_page =  (int) $url_args[$last_arg];
        array_pop($url_args);
    }
}

if (isset($url_args[1])){
    switch ($url_args[1]) {
        case '':
            $tab_option = 1;
            $order_field = 'comment_order';

            if ($globals['comments_page_size'] && $link->comments > $globals['comments_page_size']*$globals['comments_page_threshold']) {
                if (!$current_page) $current_page = ceil($link->comments/$globals['comments_page_size']);
                $offset=($current_page-1)*$globals['comments_page_size'];
                $limit = "LIMIT $offset,".$globals['comments_page_size'];
            }
            break;
        case 'mejores-comentarios':

            $tab_option = 2;
            if ($globals['comments_page_size'] > 0 ) $limit = 'LIMIT ' . $globals['comments_page_size'];
            $order_field = 'comment_karma desc, comment_id asc';
            break;

        case 'votos':

            $tab_option = 3;
            break;
        case 'eventos':

            $tab_option = 4;
            break;
        case 'cotillona':

            $tab_option = 5;
            break;
        case 'favoritos':

            $tab_option = 6;
            break;
        case 'trackbacks':

            $tab_option = 7;
            break;

        case 'burradas':

            $tab_option = 8;
            if ($globals['comments_page_size'] > 0 ) $limit = 'LIMIT ' . $globals['comments_page_size'];
            $order_field = 'comment_karma asc, comment_id asc';
            break;

        default:
            do_error(_('página inexistente'), 404);
    }
} else {

            $tab_option = 1;
            $order_field = 'comment_order';

            if ($globals['comments_page_size'] && $link->comments > $globals['comments_page_size']*$globals['comments_page_threshold']) {
                if (!$current_page) $current_page = ceil($link->comments/$globals['comments_page_size']);
                $offset=($current_page-1)*$globals['comments_page_size'];
                $limit = "LIMIT $offset,".$globals['comments_page_size'];
            }

}

// Set globals
$globals['link'] = &$link;
$globals['link_id'] = $link->id;
$globals['link_permalink'] = $globals['link']->get_permalink();

$link->update_visitors();

// to avoid search engines penalisation
if (isset($tab_option) && $tab_option != 1 || $link->status == 'discard') {
    $globals['noindex'] = true;
}

do_modified_headers($link->modified, $current_user->user_id.'-'.$globals['link_id'].'-'.$link->comments.'-'.$link->modified);

if ($link->status != 'published')
    $globals['do_vote_queue']=true;

if (!empty($link->tags))
    $globals['tags']=$link->tags;

// add also a rel to the comments rss
$globals['extra_head'] = '<link rel="alternate" type="application/rss+xml" title="'._('comentarios de esta noticia').'" href="https://'.get_server_name().$globals['base_url'].'comments_rss2.php?id='.$link->id.'" />'."\n";

$globals['extra_js'] = array('historias.js', 'ajax_com.js');

do_header(_($link->title) . ' | Jonéame');
do_tabs("main",_('historia'), true);

// Para el spinner
echo '<input type="hidden" name="cargando" id="cargando" value="1">';

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();

echo '<br/>';

if ($link->comments > 4) {
    do_best_story_comments($link);
}
if (! $current_user->user_id) {
    do_best_stories();
}
do_rss_box();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

$globals['show_visits'] = true;

echo '<div id="newswrap">'."\n";

$link->print_summary();


echo '<div id="contenido">';

$server = $_SERVER['PATH_INFO'];

if (isset($tab_option)){
    switch ($tab_option) {
    case 1:
        echo '<script>$( document ).ready(function() { link_show('.$link->id.','.$tab_option.',2,"'.$server.'"); });</script>';
        break;
    case 2:
        echo '<script>$( document ).ready(function() { link_show('.$link->id.','.$tab_option.',3,"'.$server.'"); });</script>';
        break;
    case 3:
        echo '<script>$( document ).ready(function() { link_show('.$link->id.','.$tab_option.',6,"'.$server.'"); });</script>';
        break;
    case 6:
        echo '<script>$( document ).ready(function() { link_show('.$link->id.','.$tab_option.',7,"'.$server.'"); });</script>';
        break;
    case 4:
        echo '<script>$( document ).ready(function() { link_show('.$link->id.','.$tab_option.',1,"'.$server.'"); });</script>';
        break;
    case 5:
        echo '<script>$( document ).ready(function() { link_show('.$link->id.','.$tab_option.',8,"'.$server.'"); });</script>';
        break;
    case 7:
        echo '<script>$( document ).ready(function() { link_show('.$link->id.','.$tab_option.',5,"'.$server.'"); });</script>';
        break;
    case 8:
        echo '<script>$( document ).ready(function() { link_show('.$link->id.','.$tab_option.',4,"'.$server.'");</script>';
        break;
    }
} //isset

echo '</div>'."\n"; //newswap

$globals['tag_status'] = $globals['link']->status;
do_footer();
