<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
array_push($globals['extra_js'], 'posts01.js'); // meter valor al array
include(mnminclude.'html1.php');
include(mnminclude.'search.php');
include(mnminclude.'link.php');
include(mnminclude.'comment.php');
include(mnminclude.'busquedas.php');


if (!$current_user->devel && !$globals['buscador_activado']) do_error("El buscador está en el quirófano", 500);

// Manage "search" url and redirections accordingly
if (!empty($globals['base_search_url'])) {
    if (!empty($_SERVER['PATH_INFO']) ) {
        $q = preg_quote($globals['base_url'].$globals['base_search_url']);
        if(preg_match("{^$q}", $_SERVER['SCRIPT_URL'])) {
            $_REQUEST['q'] = urldecode(substr($_SERVER['PATH_INFO'], 1));
        }
    } elseif (!empty($_REQUEST['q'])) {
        $_REQUEST['q'] = substr(trim(strip_tags($_REQUEST['q'])), 0, 300);
        if (!preg_match('/\//', $_REQUEST['q']) ) {  // Freaking Apache rewrite that translate //+ to just one /
                                                        // for example "http://" is converted to http:/
                                                        // also it cheats the paht_info and redirections, so don't redirect
            header('Location: https://'. get_server_name().$globals['base_url'].$globals['base_search_url'].urlencode($_REQUEST['q']));
            die;
        }
    } elseif (isset($_REQUEST['q'])) {
        header('Location: https://'. get_server_name().$globals['base_url']);
        die;
    }
}


$page_size = 20;
$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = true;

$globals['noindex'] = true;

$response = do_search(false, $offset, $page_size);

do_header(_('búsqueda de'). ' "'.htmlspecialchars($_REQUEST['words']).'"');
do_tabs('main',_('búsqueda'), htmlentities($_SERVER['REQUEST_URI']));

switch ($_REQUEST['w']) {
    case 'posts':
        $rss_program = 'sneakme_rss2.php';
        break;
    case 'comments':
        $rss_program = 'comments_rss2.php';
        break;
    case 'links':
    default:
        $rss_program = 'rss2.php';
}

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_rss_box($rss_program);
echo '</div>';
/*** END SIDEBAR ***/

echo '<div id="newswrap">';

// Search form
echo '<div class="genericform" style="text-align: center">';
echo '<fieldset class="fondo-caja semi-redondo">';

print_search_form();

if(!empty($_REQUEST['q'])) {
 echo '<div style="font-size:85%;margin-top: 5px">';
    echo _('encontrados').': '.$response['rows'].', '._('tiempo total').': '.sprintf("%1.3f",$response['time']).' '._('segundos');
    echo '&nbsp;<a href="'.$globals['base_url'].$rss_program.'?'.htmlspecialchars($_SERVER['QUERY_STRING']).'" rel="rss"><img src="'.$globals['base_url'].'img/iconos/feed.png" alt="rss2" height="16" width="16"  style="vertical-align:top"/></a>';

/*guardar en favoritos*/
if ($current_user->user_id > 0) {
    $favicon = imagen($current_user->user_id, $_REQUEST['q']);
    echo '&nbsp;<a id=favoritos href="javascript:obtener(\'busqueda_fav.php\',\''.$current_user->user_id.'\',\'favoritos\',0,\''.$_REQUEST['q'].'\')">'.$favicon.'</a>';
}
echo '</div>';
}

echo '</fieldset>';
echo '</div>';
echo '<div>';
if ($response['ids']) {
    $rows = min($response['rows'], 1000);

   if ($_REQUEST['w'] == "posts")
                echo '<ol class="notitas-list">';
   else if ($_REQUEST['w'] == "comments")
                echo '<ol class="comments-list">';


    foreach($response['ids'] as $id) {

        //$obj->basic_summary = true;
        switch ($_REQUEST['w']) {
            case 'posts':
        $obj = Post::from_db($id);
                $obj->print_summary(300);
                break;
            case 'comments':
        $obj = Comment::from_db($id);
                $obj->print_summary(false, 300);
                break;
            case 'links':
            default:
          $obj = Link::from_db($id);
                $obj->print_summary();
        break;
        }
    }

   if ($_REQUEST['w'] == "posts" or $_REQUEST['w'] == "comments")
    echo '</ol>';
}

do_pages($rows, $page_size);
echo '</div>';

do_footer();

function print_search_form() {
    echo '<form id="thisform" action="">';
    echo '<input type="text" name="q" value="'.htmlspecialchars($_REQUEST['words']).'" class="form-full"/>';
    echo '<input class="button" type="submit" value="'._('buscar').'" />';

    // Print field options
    echo '<br />';


    echo '<select name="w" id="w">';
    switch ($_REQUEST['w']) {
        case 'posts':
        case 'comments':
            echo '<option value="'.$_REQUEST['w'].'" selected="selected">'.$_REQUEST['w'].'</option>';
            $what = $_REQUEST['w'];
            break;
        case 'links':
        default:
            $what = 'links';
            echo '<option value="" selected="selected">'.$what.'</option>';
    }
    foreach (array('links', 'posts', 'comments') as $w) {
        if ($w != $what) {
            echo '<option value="'.$w.'">'.$w.'</option>';
        }
    }
    echo '</select>';

    $visibility = $_REQUEST['w'] != 'links' ? ' disabled="disabled"' : '';
    echo '&nbsp;&nbsp;<select name="p" id="p" '.$visibility.'>';
    switch ($_REQUEST['p']) {
        case 'url':
        case 'tags':
        case 'title':
        case 'site':
            echo '<option value="'.$_REQUEST['p'].'" selected="selected">'.$_REQUEST['p'].'</option>';
            break;
        default:
            echo '<option value="" selected="selected">'._('campos...').'</option>';
            break;
    }
    foreach (array('url', 'tags', 'title', 'site') as $p) {
        if ($p != $_REQUEST['p']) {
            echo '<option value="'.$p.'">'.$p.'</option>';
        }
    }
    echo '<option value="">'._('todo el texto').'</option>';
    echo '</select>';

    // Print status options
    echo '&nbsp;&nbsp;<select name="s" id="s"'.$visibility.'>';
    switch ($_REQUEST['s']) {
        case 'published':
        case 'queued':
        case 'discard':
        case 'autodiscard':
        case 'abuse':
            echo '<option value="'.$_REQUEST['s'].'" selected="selected">'.$_REQUEST['s'].'</option>';
            break;
        default:
            echo '<option value="" selected="selected">'._('estado...').'</option>';
            break;
    }
    foreach (array('published', 'queued', 'discard', 'autodiscard', 'abuse') as $p) {
        if ($p != $_REQUEST['s']) {
            echo '<option value="'.$p.'">'.$p.'</option>';
        }
    }
    echo '<option value="">'._('todas').'</option>';
    echo '</select>';

    // Select period
    echo '&nbsp;&nbsp;<select name="h">';
    if($_REQUEST['h'] > 0) {
        $date = get_date(time()-$_REQUEST['h']*3600);
        echo '<option value="'.$_REQUEST['h'].'" selected="selected">'.$date.'</option>';
    } else {
        echo '<option value="" selected="selected">'._('período...').'</option>';
    }
    echo '<option value="'.intval(24).'">'._('24 horas').'</option>';
    echo '<option value="'.intval(48).'">'._('48 horas').'</option>';
    echo '<option value="'.intval(24*7).'">'._('última semana').'</option>';
    echo '<option value="'.intval(24*30).'">'._('último mes').'</option>';
    echo '<option value="'.intval(24*180).'">'._('6 meses').'</option>';
    echo '<option value="'.intval(24*365).'">'._('1 año').'</option>';
    echo '<option value="">'._('todas').'</option>';
    echo '</select>';


    echo '&nbsp;&nbsp;<select name="o">';
    if($_REQUEST['o'] == 'date') {
        echo '<option value="date">'._('por fecha').'</option>';
        echo '<option value="">'._('por relevancia').'</option>';
    } else {
        echo '<option value="">'._('por relevancia').'</option>';
        echo '<option value="date">'._('por fecha').'</option>';
    }
    echo '</select>';
    echo '</form>';

    echo '<script>';
    echo '$(document).ready(function() {';
    echo '    $("#w").change(function() {';
    echo '        type = $("#w").val();';
//  echo '        if (type == "links") $("#link_options").css("visibility", "visible");';
//  echo '        else $("#link_options").css("visibility", "hidden");';
    echo '        if (type == "links") { $("#p").attr("disabled", false); $("#s").attr("disabled", false); }';
    echo '        else { $("#p").attr("disabled", true); $("#s").attr("disabled", true); }';
    echo '    });';
    echo '});';
    echo '</script>';
}