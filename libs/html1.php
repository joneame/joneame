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

    echo '</div>';

    echo '<div id="footwrap">'."";

    echo '<div class="footthingy">';

    echo '<div id="footcol1">'."";

    do_rss();

    echo '</div>'."";

    echo '<div id="footcol2">'."";
    echo '<h5>ayuda</h5>'."";
    echo '<ul id="halluda_hoygan">'."";
    echo '<li><a href="'.$globals['base_url'].'ayuda.php">'._('ayuda').'</a></li>'."";
    echo '<li><a href="'.$globals['base_url'].'ayuda.php?id=faq">'._('faq').'</a></li>'."";
    echo '</ul>'."";
    echo '</div>'."";

    echo '<div id="footcol3">'."";
    echo '<h5>jonéame</h5>'."";
    echo '<ul id="joneamefooter">'."";
    echo '<li><a href="https://twitter.com/joneame">en twitter</a></li>'."";
    echo '<li><a href="'.$globals['base_url'].'cortos.php">cortos</a></li>'."";
    echo '<li><a href="'.$globals['base_url'].'top_mierdas.php">¿historias?</a></li>'."";
    echo '<li><a href="'.$globals['base_url'].'encuestas.php">encuestas</a></li>'."";
    echo '</ul>'."";
    echo '</div>'."";


    echo '<div id="footcol4">'."";
    echo '<h5>estadísticas</h5>'."";
    echo '<ul id="statisticslist">'."";
    echo '<li><a href="'.$globals['base_url'].'mejores_mafiosos.php">'._('mafiosos').'</a></li>'."";
    echo '<li><a href="'.$globals['base_url'].'las_mejores.php">'._('populares').'</a></li>'."";
    echo '<li><a href="'.$globals['base_url'].'mas_comentadas.php">'._('más comentadas').'</a></li>'."";
    echo '<li><a href="'.$globals['base_url'].'mejores_comentarios.php">'._('mejores comentarios').'</a></li>'."";
    echo '<li><a href="'.$globals['base_url'].'mejores_notitas.php">'._('mejores notitas').'</a></li>'."";
    echo '<li><a href="'.$globals['base_url'].'nube.php">'._('nube de etiquetas').'</a></li>'."";
    echo '<li><a href="'.$globals['base_url'].'nube_de_webs.php">'._('nube de webs').'</a></li>'."";

    echo '</ul>'."";
    echo '</div>'."";

    echo '</div>'; // footthingy --neiKo

    echo '<div id="gatete"></div>';

    // El banner ira aqui.
    // do_banner_behean();

    if($credits) do_credits();
    do_js_from_array($globals['post_js']);

    echo '</div></div>';

    // warn warn warn
    // dont do stats of password recovering pages
    if (isset($globals['joneame']) && $globals['joneame'] && isset($globals['recovery']) && !$globals['recovery'] ) {

        if ($current_user->user_id > 0) $tipovisita = 'Registrado';
        else                $tipovisita = 'Anonimo';

        $analytics = "
            <script>

            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-6807553-1']);
            _gaq.push(['_setCustomVar', 1, 'TipoVisita', '$tipovisita']);
            _gaq.push(['_trackPageview']);

            (function() {
              var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
              ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
              var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })();

            </script>";

        $analytics = str_replace(array("\n", "\r", "\t", "  "), ' ', $analytics);

        echo $analytics;
    }

    $gen_time = microtime(true) - $globals['start_time'];

    printf("\n<!-- Generado en %.3f segundos con %d peticiones -->", $gen_time, $db->num_queries);

    echo "</body></html>";
}

function do_rss() {
    global $globals, $current_user;

    echo '<h5>'._('suscripciones por RSS').'</h5>'."";
    echo '<ul>'."";

    echo '<li>';
    echo '<a href="'.$globals['base_url'].'rss2.php">'._('publicadas').'</a>';
    echo '</li>';

    echo '<li>';
    echo '<a href="'.$globals['base_url'].'rss2.php?status=queued">'._('en cola').'</a>';
    echo '</li>';

    if($current_user->user_id > 0) {
        echo '<li>';
        echo '<a href="'.$globals['base_url'].'comments_rss2.php?conversation_id='.$current_user->user_id.'" title="'._('comentarios de las noticias donde has comentado').'">'._('mis conversaciones').'</a>';
        echo '</li>';
        echo '<li>';
        echo '<a href="'.$globals['base_url'].'comments_rss2.php?author_id='.$current_user->user_id.'">'._('comentarios en mis historias').'</a>';
        echo '</li>';
    }

    echo '<li>';
    echo '<a href="'.$globals['base_url'].'comments_rss2.php">'._('comentarios').'</a>';
    echo '</li>';

    echo '<li>';
    echo '<a href="'.$globals['base_url'].'encuestas_rss.php">'._('encuestas').'</a>';
    echo '</li>';
    echo '</ul>';
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
    echo "\n".'<!--html1:do_pages_start-->';

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

function print_share_icons($full_link, $short_link = false, $title = '', $id) {
    global $globals;

    $full_link = urlencode($full_link);
    if (! $short_link) {
        $short_link = $full_link;
    } else {
        $short_link = urlencode($short_link);
    }

    if ($globals['base_story_url']) {
           $joneame_link = 'https://'.get_server_name().$globals['base_url'].$globals['base_story_url'].'0'.$id;
        }

        if (! $title) $title = get_server_name();

    $title = urlencode($title);
    $space = urlencode(' - ');

    echo '&nbsp;<span class="tool"></span>';
    // Share it in Twitter
    echo '<a href="https://twitter.com/share?text='.$title.$space.'&amp;url='.$short_link.'&amp;via=joneame" rel="noopener" target="_blank"><img class="icon favicon-twitter img-flotante" src="'.get_cover_pixel().'" alt="twitter" title="'._('compartir en twitter').'"/></a>';
    // Share it in Facebook
    echo '&nbsp;&nbsp;<a href="https://www.facebook.com/share.php?u='.$full_link.'" rel="noopener" target="_blank"><img class="icon favicon-facebook img-flotante" src="'.get_cover_pixel().'" alt="facebook" title="'._('compartir en facebook').'"/></a> ';
    //Share it in Google +
    // echo '&nbsp;<span id="plusone-span-'.$id.'"></span> <script> $(function () {gapi.plusone.render("plusone-span", {"size": "small", "count": false})});</script>';

    // Jonéame link
    // echo '</span><span class="tool"><a href="'.$joneame_link.'"><img src="'.$globals['base_url'].$globals['favicon'].'" alt="jonéame" title="'._('enlace corto jonéame').'" width="16" height="16"/></a></span>';
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

function do_credits() {
    global  $globals;

    echo '<div class="footlegal">';
    echo '<ul id="legalese">';

    // IMPORTANT: links change in every installation, CHANGE IT!!
    // contact info
    if ($globals['joneame']) {
        echo '<li>Jonéame</li>';
        echo '<li> - </li>';
        echo '<li><a href="'.$globals['base_url'].'ayuda.php?id=legal">'._('condiciones legales').'</a> ';
        echo '<a href="'.$globals['base_url'].'ayuda.php?id=uso">'._('y de uso').'</a></li>';
        echo '<li> - </li>';
        echo '<li><a href="https://raw.githubusercontent.com/joneame/joneame/master/COPYING">'._('licencia').'</a>, <a href="https://github.com/joneame/joneame">'._('código fuente').'</a></li>';
        echo '<li> - </li>';
        echo '<li><a href="http://www.famfamfam.com/lab/icons/silk/">'._('iconos silk').'</a></li>';
        echo '<li> - </li>';
                echo '<li><a href="'.$globals['base_url'].'ayuda.php?id=legal">'.('contacto').'</a></li>';
        echo '<li> - </li>';
                echo '<li><a href="'.$globals['base_url'].'credits.php">'.('créditos').'</a></li>';
    } else {
        echo '<li>link to code and licenses here (please respect the menéame Affero license and publish your own code!)</li>';
        echo '<li><a href="">contact here</a></li>';
        echo '<li>code: <a href="#">Affero license here</a>, <a href="#">download code here</a></li>';
        echo '<li>you and contact link here</li>';
    }
    echo '</ul>';
    echo '</div>';

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