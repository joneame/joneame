<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
include(mnminclude.'comment.php');
include(mnminclude.'user.php');
include(mnminclude.'geo.php');
require_once(mnminclude.'encuestas.php');

$globals['ads'] = true;

array_push($globals['extra_js'], 'posts01.js');

if (!empty($globals['base_user_url']) && !empty($_SERVER['PATH_INFO'])) {
    $url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
    array_shift($url_args); // The first element is always a "/"
    $_REQUEST['login'] = clean_input_string($url_args[0]);

    if (!empty($url_args[1]))
        $_REQUEST['view'] = $url_args[1];

} else {
    $_REQUEST['login'] = clean_input_string($_REQUEST['login']);
    if (!empty($globals['base_user_url']) && !empty($_REQUEST['login'])) {
        header('Location: ' . get_user_uri($_REQUEST['login'], clean_input_string($_REQUEST['view'])));
        die;
    }
}

$login = $_REQUEST['login'];
if(empty($login)){
    if ($current_user->user_id > 0) {
        header('Location: ' . get_user_uri($current_user->user_login));
        die;
    } else {
        header('Location: '.$globals['base_url']);
        die;
    }
}
$user=new User();
$user->username = $db->escape($login);
$user->disabled = false;

if(!$user->read()) {
     do_error(_('el usuario no existe o se ha dado de baja'), 404);
}


// For editing notes
if ($current_user->user_id == $user->id) {
    array_push($globals['extra_js'], 'jquery-form.pack.js');
}

if (isset($_REQUEST['view']))
$view = clean_input_string($_REQUEST['view']);

if(empty($view)) $view = 'profile';

// Load Google GEO
if (!$user->disabled && $view == 'profile' && $globals['google_maps_api'] && $globals['joneame'] && (($globals['latlng']=$user->get_latlng()) || $current_user->user_id == $user->id)) {
    if ($current_user->user_id == $user->id) {
        geo_init('geo_coder_editor_load', $globals['latlng'], 7, 'user');
    } else {
        geo_init('geo_coder_load', $globals['latlng'], 7, 'user');
    }
    $globals['do_geo'] = true;
}

if (!empty($user->names) && $login != $user->names) {
    do_header("$login ($user->names)");
} else{
        do_header($login);
}

echo '<div id="singlewrap">'."\n";

$url_login = urlencode($login);

if ($view == 'encuestas') $page_size = 7;
else              $page_size = 40;

$offset=(get_current_page()-1)*$page_size;

switch ($view) {
    case 'enviadas':
        do_user_tabs(2, $login);
        do_history();
        do_pages($rows, $page_size);
        break;
    case 'comentarios':
        do_user_tabs(3, $login);
        do_commented();
        do_pages($rows, $page_size, false);
        break;
    case 'votadas':
        do_user_tabs(4, $login);
        do_shaken();
        do_pages($rows, $page_size);
        break;
    case 'conversacion':
        do_user_tabs(5, $login);
        do_conversation();
        do_pages($rows, $page_size, false);
        break;
    case 'favoritos':
        do_user_tabs(6, $login);
        do_favorites();
        do_pages($rows, $page_size);
        break;
    case 'amigos':
        do_user_tabs(7, $login);
        do_friends();
        break;
    case 'comentarios_fav':
        do_user_tabs(8, $login);
        do_favorites_comment();
        do_pages($rows, $page_size);
        break;
    case 'cortos':
        do_user_tabs(9, $login);
        do_cortos();
        do_pages($zenbat, $page_size);
        break;
    case 'encuestas':
        do_user_tabs(11, $login);
        do_encuestas();
        do_pages($rows, $page_size);
        break;
    case 'comentarios_encuestas':
        do_user_tabs(3, $login);
        do_poll_comments();
        do_pages($rows, $page_size);
        break;
    case 'profile':
    default:
        do_user_tabs(1, $login);
        do_profile();
        break;
}

echo '</div>'."\n";

do_footer();


function do_profile() {
    global $user, $current_user, $login, $db, $globals;

    if(!empty($user->url)) {
        if ($user->karma < 10) $nofollow = 'rel="nofollow"';
        else                $nofollow = '';

        if (!preg_match('/^http/', $user->url)) $url = 'https://'.$user->url;
        else $url = $user->url;
    }

    echo '<div class="genericform">';

    $last_post = $db->get_var("SELECT post_id FROM posts WHERE post_user_id=$user->id and post_type != 'admin'  ORDER BY post_id DESC LIMIT 1");

    if ($last_post > 0) {

        $post = Post::From_db(intval($last_post));
        echo '<div id="addpost"></div>';
        echo '<ol class="comments-list" id="last_post">';
        $post->print_summary();
        echo '<br/>';
        echo "</ol>\n";
    }

    echo '<h4>';
    echo _('información personal');
    if($user->id == $current_user->user_id) {
        echo '<a href="'.$globals['base_url'].'profile.php" class="buttonlink">'._('modificar').'</a>';
    } elseif ($current_user->user_level == 'god') {
        echo '<a href="'.$globals['base_url'].'profile.php?login='.urlencode($login).'" class="buttonlink">'._('modificar').'</a>';
    } elseif ($current_user->user_level == 'admin') {
        echo '<a href="'.$globals['base_url'].'bsc.php?login='.urlencode($login).'" class="buttonlink">'._('bajada súbita de carisma').'</a>';
    }
    echo '</h4><fieldset class="fondo-caja">';

    if ($current_user->user_id !=1) {
        if(!empty($user->estado))
            echo '<div class="estado"><strong>'.$user->username.' </strong>'.$user->estado.'</div>';

    } else
        echo '<div class="estado"><strong>'.$user->username.' </strong><a id="link" style="color:#333;" onclick="javascript:cambiar_estado('.$user->id.', \'formulario\')">'.$user->estado.'</a></div>';



    // Avatar
    echo '<img class="thumbnail" src="'.get_avatar_url($user->id, $user->avatar, 80).'" width="80" height="80" alt="'.$user->username.'" title="avatar" />';

    // Geo div
    if($globals['do_geo']) {
    echo '<div style="width:140px; float:left;">';
        echo '<div id="map" class="thumbnail" style="width:130px; height:130px; overflow:hidden; float:left"></div>';
        if ($current_user->user_id > 0 && $current_user->user_id != $user->id && $globals['latlng'] && ($my_latlng = geo_latlng('user', $current_user->user_id))) {
            $distance = (int) geo_distance($my_latlng, $globals['latlng']);
            echo '<p style="color: #429ee9; font-size: 90%">'._('estás a')." <strong>$distance kms</strong> "._('de').' '.$user->username.'</p>';
        }
    echo '&nbsp;</div>';
    }


    echo '<div style="float:left;min-width:65%">';
    echo '<dl>';
    if(!empty($user->username)) {
        echo '<dt>'._('usuario').':</dt><dd>';
        if (!empty($url)) {
            echo '<a href="'.$url.'" '.$nofollow.'>'.$user->username.'</a>';
        } else {
            echo $user->username;
        }
        // Print friend icon
        if ($current_user->user_id > 0 && $current_user->user_id != $user->id) {
            echo '&nbsp;<a id="friend-'.$current_user->user_id.'-'.$user->id.'" href="javascript:obtener(\'amigos.php\',\''.$current_user->user_id.'\',\'friend-'.$current_user->user_id.'-'.$user->id.'\',0,\''.$user->id.'\')">'.friend_teaser($current_user->user_id, $user->id).'</a>';
        }

        if ($login===$current_user->user_login || $current_user->user_level == 'god') {
            echo " (" . _('id'). ": <em>$user->id</em>)";
            echo " (<em>$user->level</em>)";
        }

        if($current_user->user_level=='god') {
            echo " (<em>$user->username_register</em>)";
        }

        if ($current_user->user_id > 0 && $current_user->user_id != $user->id) echo '&nbsp;<a href="'.get_mensajes_uri($user->username).'"><img class="icon message" style="float: right; margin-right: 5px;" src="'.get_cover_pixel().'" title="'._('enviar un mensaje privado').'"/></a>';

        if ($current_user->user_id > 0 && $current_user->user_id == $user->id)
        echo '&nbsp;<a href="'.get_mensajes_uri($user->username).'"><img class="icon message" style="float: right; margin-right: 5px;" src="'.get_cover_pixel().'" title="'._('ir a mi bandeja de entrada').'"/></a>';

        echo '</dd>';
    }

    if(!empty($user->names)) {
        echo '<dt>'._('nombre').':</dt><dd>'.$user->names.'</dd>';
    }
    include(mnminclude.'coti2.inc.php');
    if ($current_user->admin && $user->level != 'god' && $user->level != 'disabled') {
        echo '<dt>'._('cotillona').':</dt>';

        //Gestionar baneos de la cotillona
        if (!baneatuta($user->id))
            echo '<dd><a href="'.$globals['base_url'].'cotillona.php?ban='.$user->id.'">Banear usuario temporalmente</a></dd>';
        else
            echo '<dd><a href="'.$globals['base_url'].'cotillona.php?unban='.$user->id.'">Desbanear usuario de la cotillona</a></dd>';

    }


    // Show public info is it's a god
    if($current_user->user_id > 0 && !empty($user->public_info) && (
            $current_user->user_id == $user->id
            || $current_user->user_level=='god' )) {
        echo '<dt>'._('Jabber/Gtalk').':</dt><dd> '.$user->public_info.'</dd>';
    }

    if(!empty($url)) {
        echo '<dt>'._('sitio web').':</dt><dd><a href="'.$url.'" '.$nofollow.'>'.$url.'</a></dd>';
    }

    echo '<dt>'._('desde').':</dt><dd>'.get_date_time($user->date).'</dd>';

    if ( $current_user->admin) {

     if($user->level == 'disabled') echo '<dt>'._('deshabilitado el día').':</dt><dd>'.get_date_time($user->modification).'</dd>';
      else echo '<dt>'._('modificado').':</dt><dd>'.get_date_time($user->modification).'</dd>';

    }

    $last_seen = $user->get_last_date();

        if ($last_seen)
        echo '<dt>'._('visto por última vez').':</dt><dd>'.get_date_time($last_seen).'</dd>';



    /*if ($user->birth) {
    $cumple = explode(',',$user->birth);
    echo '<dt>'._('cumpleaños').':</dt><dd>'.$cumple[0].' de '.get_month(intval($cumple[1])).' </dd>';
    }*/


    if($current_user->user_level=='god' ) {
        echo '<dt>'._('email').':</dt><dd>'.$user->email. " (<em>$user->email_register</em>)</dd>";
    }

    if ($user->id == $current_user->user_id || $current_user->user_level=='god' ) {
    echo '<dt>'._('clave API').':</dt><dd id="api-key"><a href="javascript:obtener(\'obtener_api.php\',\'\',\'api-key\',0,\''.$user->id.'\')">'._('leer clave API').'</a> <b>('._('no la divulgues').')</b></dd>';
    }

    $carisma_maximo = 30;
        $flota = "right";
    $long_barra = round($user->karma * 100 / $carisma_maximo);
    if ($long_barra > 100) $long_barra = 100;
    if ($long_barra < 0) $long_barra = 0;
    if ($long_barra < 50) $flota = "right"; else $flota = "left";

    echo '<dt>'._('carisma').':</dt><dd><div class="barra-carisma-outer semi-redondo">';

    echo '<div class="barra-carisma-inner" style="width: '.$long_barra.'%;"></div>';

    echo '<div class="barra-carisma-numero" style="'.$flota.': 3px;';

    if ($long_barra < 50) echo ' color: #333; text-shadow: 0 0 2px #fff;';

    echo '">'.$user->karma.'</div></div>';

    // Carisma details
    if ($user->id == $current_user->user_id || $current_user->user_level=='god' ) {
        echo '<a class="fancybox" href="'.$globals['base_url'].'backend/calculo_carisma.php?id='.$user->id.'"> ('._('detalle del cálculo carisma').')</a>';
    } else
        echo '<div style="clear: both;"></div>'; // para que no se monte el número en el ranking al lado de la barra de carisma, si el usuario no puede ver el detalle del cálculo --neiKo

    echo '</dd>';

    echo '<dt>'._('ranking').':</dt><dd>#'.$user->ranking().'</dd>';

    $user->all_stats();
    echo '<dt>'._('noticias enviadas').':</dt><dd>'.$user->total_links.'</dd>';
    if ($user->total_links > 0 && $user->published_links > 0) {
        $percent = intval($user->published_links/$user->total_links*100);
    } else {
        $percent = 0;
    }
    if ($user->total_links > 1) {
        $entropy = intval(($user->blogs() - 1) / ($user->total_links - 1) * 100);
        echo '<dt><em>'._('entropía').'</em>:</dt><dd>'.$entropy.'%</dd>';
    }
    echo '<dt>'._('noticias publicadas').':</dt><dd>'.$user->published_links.' ('.$percent.'%)</dd>';
    echo '<dt>'._('comentarios').':</dt><dd>'.$user->total_comments.'</dd>';
    echo '<dt>'._('notas').':</dt><dd>'.$user->total_posts.'</dd>';
    echo '<dt>'._('número de joneos').':</dt><dd>'.$user->total_votes.'</dd>';
    if ($globals['cortos_activados']) echo '<dt>'._('cortos enviados').':</dt><dd>'.$user->cortos_totales.'</dd>';
        echo '<dt>'._('encuestas realizadas').':</dt><dd>'.$user->encuestas_totales.'</dd>';

    // historial del usuario
    if ($current_user->admin) {

        include(mnminclude.'historial.class.php');

        echo '<dt>'._('historial').':</dt>';
        $historial = new Historial;
        $historial->read($user->id);

        if ($historial->error)
            echo '<dd>No hay historial de este usuario</dd>';
        else
            foreach ($historial->data as $h_item)
            {

                // informacion del usuario que ha escrito esto
                $usr = new User;
                $usr->id = intval($h_item->por);

                if (!$usr->read()) $usuario = "Unknown";
                else $usuario = $usr->username;

                echo '<dd><b>'.$usuario.'</b> (<em>'.$h_item->fecha.'</em>) '.htmlspecialchars($h_item->texto).'</dd>';

            }
    }
    echo '</dl>';

    echo '</div>';
    echo '</fieldset>';

    // Print GEO form
    if($globals['do_geo'] && $current_user->user_id == $user->id ) {
        echo '<div class="geoform">';
        geo_coder_print_form('user', $current_user->user_id, $globals['latlng'], _('ubícate en el mapa (si te apetece)'), 'user');
        echo '</div>';
    }

    if ($current_user->user_level == 'god') {
        $clone_counter = 0;
        echo '<br/><fieldset class="fondo-caja redondo"><legend class="mini barra redondo">'._('verificar clones').'</legend>';
        echo '<p><strong><a class="fancybox" href="'.$globals['base_url'].'backend/cookie_clones.php?id='.$user->id.'">'._('verificar clones por cookie').'</a></strong></p>';
        echo '<p><strong><a class="fancybox" href="'.$globals['base_url'].'backend/ip_clones.php?id='.$user->id.'">'._('verificar clones por IP').'</a></strong></p>';
        echo '</fieldset>';
    }

    // Show first numbers of the address if the user has god privileges
    if ($current_user->user_level == 'god') {
        echo '<br/><fieldset class="fondo-caja redondo"><legend class="mini barra redondo">'._('últimas direcciones ip').'</legend>';

        $addresses = $db->get_results("select INET_NTOA(vote_ip_int) as ip from votes where vote_type='links' and vote_user_id = $user->id order by vote_date desc limit 30");

        // Try with comments
        if (! $addresses) {
            $addresses = $db->get_results("select comment_ip as ip from comments where comment_user_id = $user->id and comment_date > date_sub(now(), interval 30 day) order by comment_date desc limit 30");
        }

        if (! $addresses) {
            // Use register IP
            $addresses = $db->get_results("select user_ip as ip from users where user_id = $user->id");
        }

        // Not addresses to show
        if (! $addresses) {
            return;
        }

        $prev_address = '';
        foreach ($addresses as $dbaddress) {
           $ip_pattern = preg_replace('/\.[0-9]+$/', '', $dbaddress->ip);
            if ($ip_pattern != $prev_address) {
                echo $dbaddress->ip. '<br/>';
                $clone_counter++;
                $prev_address = $ip_pattern;
                if ($clone_counter >= 30) break;
            }
        }
        echo '</fieldset>';
    }
    echo '</div>';
}

function do_cortos() {
    global $current_user, $zenbat, $user, $db, $globals, $offset, $page_size;

echo '<h4>';
echo _('cortos enviados por '.$user->username);
echo '</h4>';
echo '<fieldset class="fondo-caja">';

if (!$globals['cortos_activados']) {

    echo _('Lo sentimos, los cortos están desactivados.');
    echo '</fieldset>';
    return;

}

if ($current_user->user_id > 0 && $user->id ==$current_user->user_id)
    echo '<div style="float: right;">Para enviar un nuevo corto, haz clic <a href="'.$globals['base_url'].'nuevo_corto.php">aquí</a>.</div>';

echo '<div style="float:left;min-width:65%">';

if ($user->id ==$current_user->user_id) {
        $eskaera = $db->get_col("SELECT id FROM cortos WHERE por = '".$user->id."'  ORDER BY id LIMIT $offset,$page_size");
        $zenbat = $db->get_var("SELECT count(*) FROM cortos WHERE por=$user->id  ");
} else {
        $eskaera = $db->get_col("SELECT id FROM cortos WHERE por = '".$user->id."' AND activado = '1' ORDER BY id LIMIT $offset,$page_size");
        $zenbat = $db->get_var("SELECT count(*) FROM cortos WHERE por = '".$user->id."' AND activado = '1' ");
}

if ($zenbat == 0) {
    echo _('El usuario no ha enviado ningún corto');
    echo '</fieldset>';
    return;
}

    $zenbakia = $offset + 1;
    $aktibatugabe = 0;

    foreach ($eskaera as $id) {

        $corto = new Corto;
        $corto->id = intval($id);
        $corto->get_single();

        if (!$corto->activado) {
            $aktibatugabe ++;
            continue; //no lo muestra si está pendiente
        }

        echo '<dl><dt>'._('Número ').$zenbakia.'';
        $corto->print_short_info();
        $zenbakia ++;
    }

    echo '<dl>';
    echo '<dt></dt><dd>';
    echo ' ';
    echo '</dd>';
    echo '</dl>';

    if ($user->id ==$current_user->user_id) {

        if ($aktibatugabe > 0) {
            echo '<dl>';
            echo '<dt>'._('Pendientes').':</dt><dd>';
            echo $aktibatugabe._(' (Cantidad de cortos propuestos pendientes a aprobar)').'<br><br><br>';
            echo '<dt>'._('AVISO').':</dt><dd>';
            echo _('Si editas algún corto, este pasará a estar de nuevo pendiente de la aprobación de un administrador.');
            echo '</dd>';
            echo '</dl>';
        }

    echo '<div class="redondo atencion">'._('La edición de cortos estará limitada a '.$globals['ediciones_max_cortos'] .' veces por corto, sin limitación por tiempo. Requerirá, al igual que el envío, la aprobación por parte de un administrador, por lo que no será inmediata. Éste aprobará el corto si su significado no varía significativamente. Si el corto es rechazado, se mantendrá igual que antes y no perderás las ediciones restantes.').'</div>';

    }

echo '</div>';
echo '</fieldset>';

}

function do_history () {
    global $db, $rows, $user, $offset, $page_size, $globals;

    $rows = $db->get_var("SELECT count(*) FROM links WHERE link_author=$user->id AND link_sent=1");
    $links = $db->get_col("SELECT link_id FROM links WHERE link_author=$user->id AND link_sent=1 ORDER BY link_date DESC LIMIT $offset,$page_size");
    if ($links) {
        echo '<ul class="barra redondo herramientas" style="margin: 0 0 5px 15px;">';
        echo '<li><a class="icon mozilla" href="'.$globals['base_url'].'link_bookmark.php?user_id='.$user->id.'&amp;option=enviadas" title="'._('exportar bookmarks en formato Mozilla').'">'._('exportar bookmarks en formato Mozilla').'</a>';
        echo '<li><a class="icon rss" href="'.$globals['base_url'].'rss2.php?sent_by='.$user->id.'" title="'._('obtener historial en rss2').'">'._('obtener historial en rss2').'</a></li>';
        echo '</ul>';

        foreach($links as $link_id) {
            $link = Link::from_db($link_id);
            $link->print_summary('short');
        }
    }
}

function do_favorites () {
    global $db, $rows, $user, $offset, $page_size, $globals;


    $rows = $db->get_var("SELECT count(*) FROM favorites WHERE favorite_user_id=$user->id AND favorite_type='link'");
    $links = $db->get_col("SELECT link_id FROM links, favorites WHERE favorite_user_id=$user->id AND favorite_type='link' AND favorite_link_id=link_id ORDER BY link_date DESC LIMIT $offset,$page_size");

    if ($links) {
        echo '<div style="margin-left: 13px">';

    echo '<ul class="barra redondo herramientas" style="margin: 0 0 5px 15px;">';
    echo '<li><a class="icon mozilla" href="'.$globals['base_url'].'link_bookmark.php?user_id='.$user->id.'&amp;option=favorites" title="'._('exportar bookmarks en formato Mozilla').'">'._('exportar bookmarks en formato Mozilla').'</a>';
    echo '<li><a class="icon rss" href="'.$globals['base_url'].'rss2.php?favorites='.$user->id.'" title="'._('obtener historial en rss2').'">'._('obtener historial en rss2').'</a></li>';
    echo '</ul><br/>';

        echo '</div>';
        foreach($links as $link_id) {
            $link = Link::from_db($link_id);
            $link->print_summary('short');
        }
    }
}

function do_favorites_comment () {
    global $db, $rows, $user, $offset, $page_size;


    $rows = $db->get_var("SELECT count(*) FROM favorites WHERE favorite_user_id=$user->id AND favorite_type='comment'");
    $comments =  $db->get_results("SELECT comment_id, comment_type, comment_link_id FROM comments, favorites WHERE favorite_user_id=$user->id AND favorite_type='comment' AND comment_id = favorite_link_id ORDER BY comment_id DESC LIMIT $offset,$page_size");


    $last_link = 0;
    $comentario = new Comment;
    $link = new Link;

    foreach ($comments as $comment) {

        if ($comment->comment_type == 'admin' && ! $current_user->admin) continue;

        $comentario->id = $comment->comment_id;
        $comentario->read();

        if ($last_link != $comment->comment_link_id) {
            $link->id = $comment->comment_link_id;
            $link->read();

            echo '<h4 class="izquierda">';
            echo '<a href="'.$link->get_permalink().'">'. $link->title. '</a>';
            echo ' ['.$link->comments.']';
            echo '</h4>';
            $last_link = $comment->comment_link_id;
        }

        echo '<ol class="comments-list">';
        $comentario->print_summary($link, 2000, false);
        echo "</ol>\n";
    }


}

function do_shaken() {
    global $db, $rows, $user, $offset, $page_size, $globals;

    if ($globals['bot']) return;

    $rows = $db->get_var("SELECT count(*) FROM links, votes WHERE vote_type='links' and vote_user_id=$user->id AND vote_link_id=link_id");
    $links = $db->get_results("SELECT link_id, vote_value FROM links, votes WHERE vote_type='links' and vote_user_id=$user->id AND vote_link_id=link_id ORDER BY vote_date DESC LIMIT $offset,$page_size");

    if ($links) {
        echo '<ul class="barra redondo herramientas" style="margin: 0 0 5px 15px;">';
        echo '<li><a class="icon mozilla" href="'.$globals['base_url'].'link_bookmark.php?user_id='.$user->id.'&amp;option=joneadas" title="'._('exportar bookmarks en formato Mozilla').'">'._('exportar bookmarks en formato Mozilla').'</a>';
        echo '<li><a class="icon rss" href="'.$globals['base_url'].'rss2.php?voted_by='.$user->id.'" title="'._('obtener historial en rss2').'">'._('obtener historial en rss2').'</a></li>';
        echo '</ul><br/>';

        foreach($links as $linkdb) {
            $link = Link::from_db($linkdb->link_id);
            echo '<div style="max-width: 60em">';
            $link->print_summary('short', 0, false);

            if ($linkdb->vote_value < 0) {
                echo '<div style="color: #551a8b; margin: -5px 0 0 86px; font-size: 120%"><strong>';
                echo get_negative_vote($linkdb->vote_value);
                echo "</strong></div>\n";
            }
            echo "</div>\n";
        }
        echo '<br/><span style="color: #429ee9;"><strong>'._('Nota').'</strong>: ' . _('sólo se visualizan los votos de los últimos meses') . '</span><br />';
    }
}


function do_encuestas () {
    global $db, $rows, $user, $offset, $page_size, $globals;

    if ($globals['bot']) return;

    echo '<ul class="barra redondo herramientas" style="margin: 0 0 5px 15px;">';
        echo '<li><a class="icon rss" href="'.$globals['base_url'].'encuestas_rss.php?user_id='.$user->id.'" title="'._('obtener historial en rss2').'">'._('historial por RSS').'</a></li>';
    echo '</ul><br/><br/>';

    $rows = $db->get_var("SELECT count(*) FROM encuestas WHERE encuesta_user_id=$user->id ");
    $encuestas = $db->get_col("SELECT encuesta_id FROM encuestas WHERE encuesta_user_id=$user->id ORDER BY encuesta_start DESC LIMIT $offset,$page_size");

        foreach ($encuestas as $id) {

               echo '<div id="newswrap">';
               $encuesta = new Encuesta;
               $encuesta->id =$id;
               $encuesta->read();
               $encuesta->print_encuesta();
               echo '</div>';
          }

}


function do_commented () {
    global $db, $rows, $user, $offset, $page_size, $globals, $current_user;

    if ($globals['bot']) return;


    $rows = $db->get_var("SELECT count(*) FROM comments WHERE comment_user_id=$user->id");
    $comments = $db->get_results("SELECT comment_id, link_id, comment_type FROM comments, links WHERE comment_user_id=$user->id and link_id=comment_link_id ORDER BY comment_date desc LIMIT $offset,$page_size");
    if ($comments) {
        echo '<ul class="barra redondo herramientas" style="margin: 0 0 5px 15px;">';
        echo '<li><a class="icon comment" href="'.get_user_uri($user->username, 'comentarios_encuestas').'"><img src="'.get_cover_pixel().'" />a encuestas</a></li>'."\n";
        echo '<li><a class="icon mozilla" href="'.$globals['base_url'].'link_bookmark.php?user_id='.$user->id.'&amp;option=chorradas" title="'._('exportar bookmarks en formato Mozilla').'">'._('exportar bookmarks en formato Mozilla').'</a>';
        echo '<li><a class="icon rss" href="'.$globals['base_url'].'comments_rss2.php?user_id='.$user->id.'" title="'._('obtener comentarios en rss2').'">'._('obtener comentarios en rss2').'</a></li>';
        echo '</ul><br/>';

         print_comment_list($comments);
    }
}

function do_conversation () {
     global $db, $rows, $user, $offset, $page_size, $globals, $current_user;

      if ($globals['bot']) return;

     $rows = $db->get_var("SELECT count(*) FROM conversations WHERE conversation_user_to=$user->id and conversation_type='comment'");
     $comments = $db->get_results("SELECT comment_id, link_id, comment_type FROM conversations, comments, links WHERE conversation_user_to=$user->id and conversation_type='comment' and comment_id=conversation_from and link_id=comment_link_id ORDER BY conversation_time desc LIMIT $offset,$page_size");

     if ($comments) {
         echo '<ul class="barra redondo herramientas" style="margin: 0 0 5px 15px;">';
        echo '<li><a class="icon rss" href="'.$globals['base_url'].'comments_rss2.php?answers_id='.$user->id.'" title="'._('obtener comentarios en rss2').'">'._('obtener comentarios en rss2').'</a></li>';
        echo '</ul><br/><br/><br/>';
            print_comment_list($comments);
      }

    /* Update conversation */
       if ($current_user->user_id == $user->id) {
         Comment::update_read_conversation();
        }


}

function print_comment_list($comments) {
    global $current_user;

    $last_link = 0;

    foreach ($comments as $dbcomment) {

         if ($dbcomment->comment_type == 'admin' && !$current_user->admin) continue;

         if ($last_link != $dbcomment->link_id) {
            $link = Link::from_db($dbcomment->link_id);
            echo '<h4 class="izquierda">';
            echo '<a href="'.$link->get_permalink().'">'. $link->title. '</a>';
            echo ' ['.$link->comments.']';
            echo '</h4>';
            $last_link = $dbcomment->link_id;
        }

        $comment = Comment::from_db($dbcomment->comment_id);
        echo '<ol class="comments-list">';
        $comment->print_summary($link, 2000, false);
        echo "</ol>\n";
    }

}


function do_friends() {
    global $db, $user, $globals;

    echo '<ul class="barra redondo herramientas" style="margin: 0 0 5px 15px;">';
    echo '<li><a class="icon rss" href="'.$globals['base_url'].'rss2.php?friends_of='.$user->id.'" title="'._('noticias de amigos en rss2').'">'._('noticias de amigos en rss2').'</a></li>';
    echo '</ul><br/>';

    echo '<div style="width: 48%; display: block; float: left">';
    echo '<h4>'._('eligió como amigo a...').'</h4>';
    echo '<div style="padding: 10px 0px 30px 5px" class="fondo-caja">';
    $prefered_id = $user->id;
    $prefered_type = 'from';
    echo '<div id="from-container">'. "\n";
    require('backend/paginas_amigos.php');
    echo '</div>'. "\n";
    echo '</div>'. "\n";
    echo '</div>'. "\n";


    echo '<div style="margin-left: 30px; width: 48%; display: block; float: left">';
    echo '<h4>'._('fue elegido como amigo por...').'</h4>';
    echo '<div style="padding: 10px 0px 30px 5px" class="fondo-caja">';
    $prefered_id = $user->id;
    $prefered_type = 'to';
    echo '<div id="to-container">'. "\n";
    require('backend/paginas_amigos.php');
    echo '</div>'. "\n";
    echo '</div>'. "\n";
    echo '</div>'. "\n";

    echo '<br clear="all" />';
}

function do_user_tabs($option, $user) {
    global $globals;

    $active = array();

    // Avoid PHP warnings
    for ($n=1; $n <= 9; $n++) $active[$n] = '';

    $active[$option] = ' class="current"';

    echo '<ul class="tabmain">'."\n";
    echo '<li'.$active[1].'><a href="'.get_user_uri($user).'">'._('perfil'). '</a></li>';
    echo '<li'.$active[7].'><a href="'.get_user_uri($user, 'amigos').'">'._('amigos').'</a></li>';

    if ($globals['cortos_activados']) echo '<li'.$active[9].'><a href="'.get_user_uri($user, 'cortos').'" class="separada">'._('cortos'). '</a></li>';

    if (!$globals['bot']) echo '<li'.$active[3].'><a href="'.get_user_uri($user, 'comentarios').'" class="separada">'._('comentarios'). '</a></li>';
    echo '<li'.$active[5].'><a href="'.get_user_uri($user, 'conversacion').'">'._('conversación'). '</a></li>';
    if (!$globals['bot']) echo '<li'.$active[8].'><a href="'.get_user_uri($user, 'comentarios_fav').'">'._('favoritos').'</a></li>';


    echo '<li'.$active[2].'><a href="'.get_user_uri($user, 'enviadas').'" class="separada">'._('enviadas'). '</a></li>';
    if (!$globals['bot']) echo '<li'.$active[4].'><a href="'.get_user_uri($user, 'votadas').'">'._('votadas'). '</a></li>';
    if (!$globals['bot']) echo '<li'.$active[6].'><a href="'.get_user_uri($user, 'favoritos').'">'.('favoritas').'</a></li>';

    echo '<li><a href="'.post_get_base_url($user).'" class="separada">'._('notas'). '</a></li>';
        echo '<li><a href="'.get_user_uri($user,'encuestas').'" class="separada">'._('encuestas'). '</a></li>';
    echo '</ul>';
}

function do_poll_comments() {
    global $db, $rows, $user, $offset, $page_size, $globals;

    if ($globals['bot']) return;

    include mnminclude.'opinion.php';

    echo '<ul class="barra redondo herramientas" style="margin: 0 0 5px 15px;">';
    echo '<li><a class="icon comment" href="'.get_user_uri($user->username, 'comentarios').'"><img src="'.get_cover_pixel().'" />a historias</a></li>'."\n";
    echo '<li><a class="icon rss" href="'.$globals['base_url'].'opiniones_rss.php?user_id='.$user->id.'" title="'._('obtener historial en rss2').'">'._('historial por RSS').'</a></li>';
    echo '</ul>';

    $rows = $db->get_var("SELECT count(*) FROM polls_comments WHERE autor=$user->id ");
    $opiniones = $db->get_col("SELECT id FROM polls_comments WHERE autor=$user->id ORDER BY fecha DESC LIMIT $offset,$page_size");

        foreach ($opiniones as $id) {

               echo '<div class="comments">';
               echo '<ol class="comments-list">';

               $encuesta = new Opinion;
               $encuesta->id =$id;
               $encuesta->read();
               echo $encuesta->print_opinion();
               echo '</ol></div>';
          }

}

function get_month($month){

    switch($month){
        case '1': return _('enero');
        case '2': return _('febrero');
        case '3': return _('marzo');
        case '4': return _('abril');
        case '5': return _('mayo');
        case '6': return _('junio');
        case '7': return _('julio');
        case '8': return _('agosto');
        case '9': return _('septiembre');
        case '10': return _('octubre');
        case '11': return _('noviembre');
        case '12': return _('diciembre');
        default: return false;
    }
}