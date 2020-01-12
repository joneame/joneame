<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include_once mnminclude.'post.php';
include_once mnminclude.'sidebars.php' ;

// Warning, it redirects to the content of the variable
if (!empty($globals['lounge_general']) && !$current_user->devel) {
    header('Location: https://'.get_server_name().$globals['base_url'].$globals['lounge_general']);
    die;
}

$globals['start_time'] = microtime(true);

$globals['joneame'] = (isset($globals['joneame']) && $globals['joneame']) || get_server_name() == 'joneame.net';
$globals['localhost'] = get_server_name() == 'localhost';

header('Content-Type: text/html; charset=utf-8');

if ($current_user->user_id) {
    header('Cache-Control: private');
}

function do_tabs($tab_name, $tab_selected = false, $extra_tab = false) {
    global $globals;

    $reload_text = _('recargar');
    $active = ' class="current"';

    if ($tab_name == "main" ) {
        echo '<ul class="tabmain">';

        // url with parameters?
        if (!empty($_SERVER['QUERY_STRING']))
            $query = "?".htmlentities($_SERVER['QUERY_STRING']);

        // START STANDARD TABS
        // First the standard and always present tabs
        // published tab
        if ($tab_selected == 'published') {
            echo '<li '.$active.'><a href="'.$globals['base_url'].'" title="'.$reload_text.'"><b>'._('portada').'</b></a></li>';
        } else {
            echo '<li><a href="'.$globals['base_url'].'"><b>'._('portada').'</b></a></li>';
        }


        // Most voted
        if ($tab_selected == 'popular') {
            echo '<li '.$active.'><a href="'.$globals['base_url'].'las_mejores.php" title="'.$reload_text.'">'._('las mejores').'</a></li>';
        } else {
            echo '<li><a href="'.$globals['base_url'].'las_mejores.php">'._('las mejores').'</a></li>';
        }

        // Most voted
        if ($tab_selected == 'topclicked') {
            echo '<li '.$active.'><a href="'.$globals['base_url'].'mas_visitadas.php" title="'.$reload_text.'">'._('más visitadas').'</a></li>';
        } else {
            echo '<li><a href="'.$globals['base_url'].'mas_visitadas.php">'._('más visitadas').'</a></li>';
        }

        // historias aleatorias, en cola y publicadas
        if ($tab_selected == 'aleatorios') {
            echo '<li '.$active.'><a href="'.$globals['base_url'].'aleatorios.php" title="¿te aburres? ¿todavía quieres más? ¡ésta es tu pestaña!">'._('aleatorias').'</a></li>';
        } else {
            echo '<li><a href="'.$globals['base_url'].'aleatorios.php">'._('aleatorias').'</a></li>';
        }

        // shake it
        if ($tab_selected == 'shakeit') {
            echo '<li '.$active.'><a href="'.$globals['base_url'].'jonealas.php" title="'.$reload_text.'">'._('¡jonéalas todas!').'</a></li>';
        } else {
            echo '<li><a href="'.$globals['base_url'].'jonealas.php">'._('votar historias').'</a></li>';
        }

        // report links
        if ($tab_selected == 'report_links') {
            echo '<li '.$active.'><a href="'.$globals['base_url'].'broken_link.php" title="'.$reload_text.'">'._('¡corrígelas todas!').'</a></li>';
        } else {
            echo '<li><a href="'.$globals['base_url'].'broken_link.php">'._('enlaces reportados').'</a></li>';
        }
        // END STANDARD TABS

        //Extra tab
        if ($extra_tab) {
            if ($globals['link_permalink']) $url = $globals['link_permalink'];
            else $url = htmlentities($_SERVER['REQUEST_URI']);
            echo '<li '.$active.'><a href="'.$url.'" title="'.$reload_text.'">'.$tab_selected.'</a></li>';
        }


        echo '</ul>';

    }
}

function do_header($title, $id='home') {
    global $current_user, $globals;

    $globals['recovery'] = false;

    if (!isset($globals['link_id'])) $globals['link_id'] = false;

    $cotillona = preg_match('/cotillona\.php/', $_SERVER['REQUEST_URI']);
    $globals['uri'] = preg_replace('/[<>\r\n]/', '', urldecode($_SERVER['REQUEST_URI']));

    if ($current_user->user_id > 0) {

        $current_user->c_conversation = get_comment_unread_conversations();

        $current_user->p_conversation = get_post_unread_conversations();

        $current_user->polls = get_polls_unvoted();
    }

    $vars = compact('cotillona', 'title', 'id');
    Haanga::Load('header.html', $vars);
}


function do_css_includes() {
    global $globals;

    echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$globals['base_url'].$globals['css_main'].'?key='.anticache_key($globals['css_main']).'" />';
}

function do_js_includes() {
    global $globals, $current_user;

    echo '<script src="'.$globals['base_url'].'js/jquery.pack.js"></script>';

    // Cache for Ajax
    echo '<script src="'.$globals['base_url'].'js/jsoc-0.12.0.js"></script>';
    echo '<script src="'.$globals['base_url'].'js/general.js?key='.anticache_key('js/general.js').'"></script>';
    do_js_from_array($globals['extra_js']);

    echo '<script src="'.$globals['base_url'].'js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>';

    echo '<script>var base_url="'.$globals['base_url'].'";';
    echo 'if(top.location != self.location)top.location = self.location;';
    echo '$(document).ready(function() {$("a.fancybox").fancybox()});';
    echo '</script>'."";

    if (isset($globals['extra_js_text'])) {
         echo '<script>';
         echo $globals['extra_js_text']."";
         echo '</script>'."";
    }
}

function do_js_from_array($array) {
    global $globals;

    foreach ($array as $js) {
        if (preg_match('/^http|^\//', $js)) {
            echo '<script src="'.$js.'"></script>';
        } else {
            echo '<script src="'.$globals['base_url'].'js/'.$js.'"></script>';
        }
    }
}

function do_footer($credits = true) {
    global $globals, $db, $current_user;

    $vars = compact('credits');
    Haanga::Load('footer.html', $vars);

    $gen_time = microtime(true) - $globals['start_time'];
    printf("\n<!-- Generado en %.3f segundos con %d peticiones -->", $gen_time, $db->num_queries);
}

function do_rss_box() {
    global $globals, $current_user;


    echo '<div class="sidebox"><h4>'._('suscripciones por RSS').'</h4></div>'."";
    echo '<ul class="storyrsslist fondo-caja espaciador">'."";

    if(!empty($_REQUEST['q'])) {
        $search =  htmlspecialchars($_REQUEST['q']);
        echo '<li>';
        echo '<a href="'.$globals['base_url'].'rss2.php?q='.urlencode($search).'">'._("búsqueda").': '. htmlspecialchars($_REQUEST['q'])."</a>";
        echo '</li>';
    }

    // RSS related to a single link
    if ($globals['link']) {
        if(!empty($globals['link']->meta_name)) {
            echo '<li>';
            echo '<a href="'.$globals['base_url'].'rss2.php?meta='.$globals['link']->meta_id.'&amp;status=all">'._('temática').': <em>'.$globals['link']->meta_name."</em></a>";
            echo '</li>';
        }
        if(!empty($globals['link']->category_name)) {
            echo '<li>';
            echo '<a href="'.$globals['base_url'].'rss2.php?category='.$globals['link']->category.'&amp;status=all">'._('categoría').': <em>'.$globals['link']->category_name."</em></a>";
            echo '</li>';
        }
    }
    echo '<li>';
    echo '<a href="'.$globals['base_url'].'rss2.php">'._('en portada').'</a>';
    echo '</li>';

    echo '<li>';
    echo '<a href="'.$globals['base_url'].'rss2.php?status=queued">'._('en cola').'</a>';
    echo '</li>';

    if($globals['link_id']) {
        echo '<li>';
        echo '<a href="'.$globals['base_url'].'comments_rss2.php?id='.$globals['link_id'].'">'._('comentarios <em>de esta noticia</em>').'</a>';
        echo '</li>';
    }

    if($current_user->user_id > 0) {
        echo '<li>';
        echo '<a href="'.$globals['base_url'].'comments_rss2.php?conversation_id='.$current_user->user_id.'" title="'._('comentarios de las noticias donde has comentado').'">'._('mis conversaciones').'</a>';
        echo '</li>';
        echo '<li>';
        echo '<a href="'.$globals['base_url'].'comments_rss2.php?author_id='.$current_user->user_id.'">'._('comentarios en mis noticias').'</a>';
        echo '</li>';
    }

    echo '<li>';
    echo '<a href="'.$globals['base_url'].'comments_rss2.php">'._('todos los comentarios').'</a>';
    echo '</li>';
    echo '</ul></div>';
}



function force_authentication() {
    global $current_user;

    if(!$current_user->authenticated) {
        header('Location: '.$globals['base_url'].'login.php?return='.$_SERVER['REQUEST_URI']);
        die;
    }
    return true;
}

function do_pages($total, $page_size=25, $margin = true, $mini = false) {
    global $db;

    if ($total > 0 && $total < $page_size) return;

    $index_limit = 5;

    $query=preg_replace('/page=[0-9]+/', '', $_SERVER['QUERY_STRING']);
    $query=preg_replace('/^&*(.*)&*$/', "$1", $query);
    if(!empty($query)) {
        $query = htmlspecialchars($query);
        $query = "&amp;$query";
    }

    $current = get_current_page();
    $total_pages=ceil($total/$page_size);
    $start=max($current-intval($index_limit/2), 1);
    $end=$start+$index_limit-1;
    echo '<!--html1:do_pages_start-->';

    if ($margin) {
        echo '<div class="pages-margin">';
    } elseif ($mini) {
        echo '<div class="pages-mini">';
    } else {
        echo '<div class="pages">';
    }

    if($current==1) {
        echo '<span class="barra semi-redondo nextprev">« '._('anterior'). '</span>';
    } else {
        $i = $current-1;
        echo '<a class="barra semi-redondo" href="?page='.$i.$query.'">« '._('anterior').'</a>';
    }
 if ($total_pages > 0) {
    if($start>1) {
        $i = 1;
        echo '<a class="barra semi-redondo" href="?page='.$i.$query.'" title="'._('ir a página')." $i".'">'.$i.'</a>';
        echo '<span class="barra semi-redondo current">...</span>';
    }
    for ($i=$start;$i<=$end && $i<= $total_pages;$i++) {
        if($i==$current) {
            echo '<span class="barra semi-redondo current">'.$i.'</span>';
        } else {
            echo '<a class="barra semi-redondo" href="?page='.$i.$query.'" title="'._('ir a la página')." $i".'">'.$i.'</a>';
        }
    }
    if($total_pages>$end) {
        $i = $total_pages;
        echo '<span class="barra semi-redondo current">...</span>';
        echo '<a class="barra semi-redondo" href="?page='.$i.$query.'" title="'._('ir a la página')." $i".'">'.$i.'</a>';
    }
  } else {
    if($current>2) {
            echo '<a class="barra semi-redondo" href="?page=1" title="'._('ir a página')." 1".'">1</a>';
           echo '<span class="barra semi-redondo current">...</span>';
        }
    echo '<span class="barra semi-redondo current">'.$current.'</span>';
  }

     if($total < 0 || $current<$total_pages) {
        $i = $current+1;
        echo '<a class="barra semi-redondo" href="?page='.$i.$query.'">'._('siguiente').' »</a>';
    } else {
        echo '<span class="barra semi-redondo nextprev">» '._('siguiente'). '</span>';
    }
    echo '</div>'.'<!--html1:do_pages-->';

}

//Used in editlink.php and submit.php
function print_categories_form($selected = 0) {
    global $db;
    echo '<fieldset style="clear: both;" class="redondo"><legend class="mini barra redondo">'._('selecciona la categoría más apropiada').'</legend>';

    $categories = $db->get_results("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
    $columns = ceil(count($categories) / 5);

    while ($cur_categories = array_splice($categories, 0, $columns)) {
        echo '<dl class="categorylist">';
        foreach ($cur_categories as $category) {
            echo '<dd class="categorias"><input name="category" id="cat-'.$category->category_id.'" type="radio" ';
            if ($selected == $category->category_id) echo 'checked="true" ';
            echo 'value="'.$category->category_id.'"/> <label for="cat-'.$category->category_id.'" class="category-entry">'._($category->category_name).'</label></dd>'."";
        }
        echo '</dl>';
    }
    echo '<br style="clear: both;"/>';
    echo '</fieldset>';
}

function get_share_to_twitter_url($url, $title) {
    return 'https://twitter.com/share?text=' . urlencode($title) . '&amp;url=' . urlencode($url) . '&amp;via=joneame';
}

function get_share_to_facebook_url($url, $title) {
    return 'https://www.facebook.com/share.php?u=' . urlencode($url);
}

function do_error($mess = false, $error = false, $send_status = true, $generate_header = true, $generate_footer = true) {
    global $globals;
    $globals['ads'] = false;

    if (!$mess) $mess = _('algún error nos ha petado');

    if ($error && $send_status) {
        header("HTTP/1.0 $error $mess");
        header("Status: $error $mess");
    }

    if($generate_header)
    do_header(_('Error'));

    echo '<div class="error-outer">';
    echo '<div class="error-inner">';

    if ($error)
    echo _('<h1 class="error-msg">error').' '.$error.'</h1>'."";
    else
    echo _('<h1 class="error-msg">error').'</h1>'."";

    echo '<h2>'.$mess."";

    echo '<br/><br/><br/><br/><br/><br/><br/><br/><br/>Se ha designado un equipo de especialistas de Jonéame altamente cualificados para resolver la situación (que lo consigan o no ya es otra cosa).</h2>';

    echo '</div>';
    echo '</div>';

    if($generate_footer)
    do_footer();
    die;
}

function do_banner_top () {
    require('carga-cortos.php');
}

function do_banner_right() { // side banner A
    global $globals;

    if (isset($globals['mobile']) && $globals['mobile']) return;
}

function do_posts_tabs($tab_selected, $username) {
    global $globals, $current_user;

    $reload_text = _('recargar');
    $active = ' class="current"';

    echo '<ul class="tabmain">';

    // All
    if ($tab_selected == 1) {
        echo '<li'.$active.'><a href="'.post_get_base_url().'" title="'.$reload_text.'">'._('todas').'</a></li>';
    } else {
        echo '<li><a href="'.post_get_base_url().'">'._('todas').'</a></li>';
    }

    // Last
    echo '<li><a href="'.$globals['base_url'].'ultimas_notitas.php" title="'._('escritas las últimas 24 horas').'">'._('últimas').'</a></li>';
    echo '<li><a href="'.$globals['base_url'].'sneakme_rss2.php'.$rss_option.'">RSS</a></li>';

    // Best
    echo '<li><a href="'.$globals['base_url'].'mejores_notitas.php" title="'._('más votadas en 24 horas').'">'._('mejores').'</a></li>';

    if ($tab_selected == 7) {
        echo '<li'.$active.'><a href="'.post_get_base_url('_favorites').'">'._('favoritas').'</a></li>';
    }else if ($current_user->user_id > 0) {
        echo '<li><a href="'.post_get_base_url('_favorites').'">'._('favoritas').'</a></li>';
    }

    // conversación
        if ($tab_selected == 6) {
        echo '<li'.$active.'><a href="'.post_get_base_url('_conversacion').'" title="'.$reload_text.'">'._('conversación').'</a></li>';
    } else if ($current_user->user_id > 0) {
        echo '<li><a href="'.post_get_base_url('_conversacion').'">'._('conversación').'</a></li>';
    }

    // Friends
    if ($tab_selected == 3) {
        echo '<li'.$active.'><a href="'.post_get_base_url('_amigos').'" title="'.$reload_text.'">'._('amigos').'</a></li>';
    } else if ($current_user->user_id > 0) {
        echo '<li><a href="'.post_get_base_url('_amigos').'">'._('amigos').'</a></li>';
    }

    // User
    if ($tab_selected == 4) {
        echo '<li'.$active.'><a href="'.post_get_base_url($username).'" title="'.$reload_text.'">'.$username.'</a></li>';
    } elseif ($current_user->user_id > 0) {
        echo '<li><a href="'.post_get_base_url($current_user->user_login).'">'.$current_user->user_login.'</a></li>';
    }
    // END STANDARD TABS

    echo '</ul>';
}

function anticache_key($file) {
    return substr(md5(filemtime(__DIR__."/../".$file)), 0, 8);
}