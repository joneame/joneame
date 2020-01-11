<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'sneak.php');
include(mnminclude.'user.php');
require_once(mnminclude.'coti2.inc.php');

$globals['cotillona'] = 1;
$globals['favicon'] = 'img/favicons/favicon-coti.png';
init_sneak();

/* Manage cotibans */

if (!empty($_REQUEST['ban']) || (isset($_POST['razon_ban']) && $_POST['razon_ban'])) {

    /* Si tiene un ban */
    if ($_POST['razon_ban'])
    $razon = $db->escape($_POST['razon_ban']);

    /* Solicita banear pero no tiene una razón: Imprime el formulario */
    if (!$razon) {
        do_header(_('Cotillona | Jonéame'));
        print_razon_edit(intval($_REQUEST['ban']));
        do_footer();
        die;
    }

    /* El usuario no es admin */
    if (!$current_user->user_id > 0 || !$current_user->admin) {
        $error_text = 'El usuario no es admin.';
        if ($current_user->user_login) $error_text .= ' '.$current_user->user_login;
    }

    $user_id = intval($_POST['user_id']);

    /* Lee información básica del usuario (from coti2.inc.php)*/
    $user = read($user_id);

    if (!$user) $error_text .=' El usuario no existe.';

    if ($user['level'] == 'god') $error_text .= ' Intentando banear a un god.';

    if ($user['id'] == $current_user->user_id) $error_text .= ' Usuario intentando auto-banearse.';

    if (baneatuta($user['id'])) $error_text .= ' El usuario ya está baneado.';

    if (strlen($razon) < 15) $error_text .= ' Razón demasiado corta.';

    if ($razon) $razon = $db->escape($razon);

    /* Si hay error lo guarda como error */
    if ($error_text) {
        $error_text = clean_text($error_text);
        cotiban_log_insert('cotiban_error', $error_text, 0, $user['id']);
        }
    /* Save cotiban */
    else if ($razon) cotiban_log_insert('cotiban', $razon, 1, $user['id']);

}

/* Manage cotiunbans */

if (!empty($_REQUEST['unban'])) {

    /* El usuario no es admin */
    if (!$current_user->user_id > 0 || !$current_user->admin) {
        $error_text = 'El usuario no es admin ';
        if ($current_user->user_login) $error_text .= $current_user->user_login.'.';
    }

    $id = intval($_REQUEST['unban']);

    /* Lee información básica del usuario (from coti2.inc.php)*/
    $user = read($id);

    if (!$user) $error_text .=' El usuario no existe.';

    if ($user['level'] == 'god') $error_text .= ' Intentando banear a un god.';

    if ($user['id'] == $current_user->user_id) $error_text .= ' Usuario intentando auto-banearse.';

    if (!baneatuta($user['id'])) $error_text .= ' El usuario no está baneado.';

    /* Si hay error lo guarda como error */
    if ($error_text) {
        $error_text = clean_text($error_text);
        cotiban_log_insert('cotiunban_error', $error_text, 0, $user['id']);
        }
    else if ($razon = 'Desbaneado') {
         /* Desbanea */
         unban($user['id']);
         /* Guarda el log */
         cotiban_log_insert('cotiunban', $razon, 0, $user['id']);
        }
}


// Start html
if (!empty($_REQUEST['friends'])) {
    do_header(_('Amigos | Jonéame'));
} elseif ($current_user->user_id > 0 && !empty($_REQUEST['admin']) && ($current_user->admin)) {
    do_header(_('Admin | Jonéame'));
} elseif ($current_user->user_id > 0 && !empty($_REQUEST['devel']) && ($current_user->devel)) {
    do_header(_('Developers | Jonéame'));

} else {
    do_header(_('Cotillona | Jonéame'));
}

?>

<script>
//<![CDATA[
var my_version = '<?php echo $sneak_version; ?>';
var server_name = '<?php echo get_server_name(); ?>';
var base_url = '<?php echo $globals['base_url']; ?>';
var sneak_base_url = '<?php echo $globals['base_url']; ?>backend/cotillona.php';
var mykey = <?php echo rand(100,999); ?>;
var is_admin = <?php if ($current_user->admin) echo 'true'; else echo 'false'; ?>;
var is_devel = <?php if ($current_user->devel) echo 'true'; else echo 'false'; ?>;

var default_gravatar = '<?php echo $globals['base_url']; ?>img/v2/no-avatar-20.png';
var do_animation = true;
var animating = false;
var animation_colors = Array(0.2, 0.4, 0.6, 0.8, 1);
var colors_max = animation_colors.length - 1;
var current_colors = Array();
var animation_timer;

var do_hoygan = <?php if (isset($_REQUEST['hoygan']))  echo 'true'; else echo 'false'; ?>;
var do_flip = <?php if (isset($_REQUEST['flip']))  echo 'true'; else echo 'false'; ?>;

$(function(){
    start_sneak();
});

function play_pause() {
    if (is_playing()) {
        document.getElementById('play-pause-img').className = "icon play";
        if( document.getElementById('comment-input'))
            document.getElementById('comment-input').disabled=true;
        do_pause();

    } else {
        document.getElementById('play-pause-img').className = "icon pause";
        if (document.getElementById('comment-input'))
            document.getElementById('comment-input').disabled=false;
        do_play();
    }
    return false;
}

function set_initial_display(item, i) {
    var j;
    if (i >= colors_max)
        j = colors_max - 1;
    else j = i;
    current_colors[i] = j;
    item.css('opacity', animation_colors[j]);
}

function clear_animation() {
    clearInterval(animation_timer);
    animating = false;
    $('#items').children().css('opacity', '');
}

function animate_background() {
    if (current_colors[0] == colors_max) {
        clearInterval(animation_timer);
        animating = false;
        return;
    }
    var items = new Object; // For IE6
    items = $('#items').children();
    for (i=new_items-1; i>=0; i--) {
        if (current_colors[i] < colors_max) {
            current_colors[i]++;
            items.slice(i,i+1).css('opacity', animation_colors[current_colors[i]]);
        } else
            new_items--;
    }
}


function to_html(data) {
    var tstamp=new Date(data.ts*1000);
    var timeStr;
    var text_style = '';
    var chat_class = 'sneaker-chat';

    var hours = tstamp.getHours();
    var minutes = tstamp.getMinutes();
    var seconds = tstamp.getSeconds();

    timeStr  = ((hours < 10) ? "0" : "") + hours;
    timeStr  += ((minutes < 10) ? ":0" : ":") + minutes;
    timeStr  += ((seconds < 10) ? ":0" : ":") + seconds;

    html = '<div class="sneaker-ts">'+timeStr+'<\/div>';

    tooltip_ajax_call= "onmouseout=\"tooltip.clear(event);\"  onclick=\"tooltip.clear(this);\"";
    html += '<div class="sneaker-type"  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" >';
    switch (data.type) {
        case 'post':
            tooltip_ajax_call += " onmouseover=\"return tooltip.ajax_delayed(event, 'get_post_tooltip.php', '"+data.id+"', 10000);\"";
            html += '<img src="img/estructura/pixel.gif" width="24" height="20" style="background: url(\'img/iconos/coti.png\') 0 -220px;" alt="<?php echo _('notitas');?>" '+tooltip_ajax_call+'/><\/div>';
            html += '<div class="sneaker-votes">&nbsp;<\/div>';
            if (check_user_ping(data.title)) {
                text_style = 'style="font-weight: bold;"';
            }
            if (do_hoygan) data.title = to_hoygan(data.title);
            if (do_flip) data.title = flipString(data.title);
            html += '<div class="sneaker-story" '+text_style+'><a target="_blank" href="'+data.link+'">'+data.title+' <\/a><\/div>';
            html += '<div class="sneaker-who"  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" >';
            if (data.icon != undefined && data.icon.length > 0) {
                html += '<a target="_blank" href="'+base_url+'mafioso.php?login='+data.who+'"><img src="'+data.icon+'" width=20 height=20 onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '+data.uid+');" onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" /><\/a>';
            }
            html += '&nbsp;<a target="_blank" href="'+base_url+'mafioso.php?login='+data.who+'">'+data.who.substring(0,15)+'<\/a><\/div>';
            html += '<div class="sneaker-status">'+data.status+'<\/div>';
            return html;
            break;
        case 'poll':
            tooltip_ajax_call += " onmouseover=\"return tooltip.ajax_delayed(event, 'get_encuesta.php', '"+data.id+"', 10000);\"";
            html += '<img src="img/estructura/pixel.gif" width="24" height="20" style="background: url(\'img/iconos/coti.png\') 0 -240px;" alt="<?php echo _('encuesta');?>" '+tooltip_ajax_call+'/><\/div>'; //añadir imagen
            html += '<div class="sneaker-votes">&nbsp;<\/div>';
            if (check_user_ping(data.title)) {
                text_style = 'style="font-weight: bold;"';
            }

            html += '<div class="sneaker-story" '+text_style+'><a target="_blank" href="'+data.link+'">'+data.title+'<\/a><\/div>';
            html += '<div class="sneaker-who"  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" >';
            if (data.icon != undefined && data.icon.length > 0) {
                html += '<a target="_blank" href="'+base_url+'mafioso.php?login='+data.who+'"><img src="'+data.icon+'" width=20 height=20 onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '+data.uid+');" onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" /><\/a>';
            }
            html += '&nbsp;<a target="_blank" href="'+base_url+'mafioso.php?login='+data.who+'">'+data.who.substring(0,15)+'<\/a><\/div>';
            html += '<div class="sneaker-status">'+data.status+'<\/div>';
            return html;
            break;
//fin encuesta
        case 'chat':
            html += '<img src="img/estructura/pixel.gif" width="24" height="20" style="background: url(\'img/iconos/coti.png\') 0 -180px;" alt="<?php echo _('mensaje');?>" title="<?php echo _('mensaje');?>" '+tooltip_ajax_call+'/><\/div>';
            html += '<div class="sneaker-votes">&nbsp;<\/div>';
            // Change the style
            if (global_options.show_admin || data.status == 'admin') {
                chat_class = 'sneaker-chat-admin'
            } else if ( global_options.show_devel || data.status == '<?php echo _('devel'); ?>') {  //aqui para editar el estado<<<<<<<

                chat_class = 'sneaker-chat-devel'
}else if (global_options.show_friends || data.status == '<?php echo _('cosa nostra'); ?>') {  //aqui para editar el estado<<<<<<<
                // The sender is a friend and sent the message only to friends
                chat_class = 'sneaker-chat-friends'
            }
 if (check_user_ping(data.title)  || (is_admin && data.status != 'admin' && data.status != 'devel' && check_admin_ping(data.title))) {
                text_style += 'font-weight: bold;';
            }
            if (text_style.length > 0) {
                // Put the anchor in the same color as the rest of the text
                data.title = data.title.replace(/ href="/gi, ' style="'+text_style+'" href="');
                text_style = 'style="'+text_style+'"';
            }
            // Open in a new window
            data.title = data.title.replace(/(href=")/gi, 'target="_blank" $1');
            if (do_hoygan) data.title = to_hoygan(data.title);
            if (do_flip) data.title = flipString(data.title);
            html += '<div class="'+chat_class+'" '+text_style+'>'+data.title+'<\/div>';
            html += '<div class="sneaker-who"  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" >';
            if (data.icon != undefined &&  data.icon.length > 0) {
                html += '<a target="_blank" href="'+base_url+'mafioso.php?login='+data.who+'"><img src="'+data.icon+'" width=20 height=20 onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '+data.uid+');" onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" /><\/a>';
            }
            html += '&nbsp;<a target="_blank" href="'+base_url+'mafioso.php?login='+data.who+'">'+data.who.substring(0,15)+'<\/a><\/div>';
            html += '<div class="sneaker-status">'+data.status+'<\/div>';
            return html;
            break;
//editar aqui para editar el estado
        case 'vote':
            tooltip_ajax_call += " onmouseover=\"return tooltip.ajax_delayed(event, 'get_link.php', '"+data.id+"', 30000);\"";
            if (data.status == '<?php echo _('en portada');?>')
                html += '<img src="img/estructura/pixel.gif" width="24" height="20" style="background: url(\'img/iconos/coti.png\') 0 -140px;" alt="<?php echo _('voto');?>" '+tooltip_ajax_call+'/><\/div>';
            else
                html += '<img src="img/estructura/pixel.gif" width="24" height="20" style="background: url(\'img/iconos/coti.png\') 0 -100px;" alt="<?php echo _('voto');?>"  '+tooltip_ajax_call+'/><\/div>';
            break;
        case 'problem':
            tooltip_ajax_call += " onmouseover=\"return tooltip.ajax_delayed(event, 'get_link.php', '"+data.id+"', 30000);\"";
            html += '<img src="img/estructura/pixel.gif" width="24" height="20" style="background: url(\'img/iconos/coti.png\') 0 -200px;" alt="<?php echo _('problema');?>" '+tooltip_ajax_call+'/><\/div>';
            break;
        case 'comment':
            tooltip_ajax_call += " onmouseover=\"return tooltip.ajax_delayed(event, 'get_comment_tooltip.php', '"+data.id+"', 10000);\"";
            html += '<img src="img/estructura/pixel.gif" width="24" height="20" style="background: url(\'img/iconos/coti.png\');" alt="<?php echo _('comentario');?>" '+tooltip_ajax_call+'/><\/div>';
            break;

        case 'new':
            tooltip_ajax_call += " onmouseover=\"return tooltip.ajax_delayed(event, 'get_link.php', '"+data.id+"', 30000);\"";
            html += '<img src="img/estructura/pixel.gif" width="24" height="20" style="background: url(\'img/iconos/coti.png\') 0 -40px;" alt="<?php echo _('nueva');?>" '+tooltip_ajax_call+'/><\/div>';
            break;
        case 'published':
            tooltip_ajax_call += " onmouseover=\"return tooltip.ajax_delayed(event, 'get_link.php', '"+data.id+"', 30000);\"";
            html += '<img src="img/estructura/pixel.gif" width="24" height="20" style="background: url(\'img/iconos/coti.png\') 0 -120px;" alt="<?php echo _('publicada');?>" '+tooltip_ajax_call+'/><\/div>';
            break;
        case 'discarded':
            tooltip_ajax_call += " onmouseover=\"return tooltip.ajax_delayed(event, 'get_link.php', '"+data.id+"', 30000);\"";
            html += '<img src="img/estructura/pixel.gif" width="24" height="20" style="background: url(\'img/iconos/coti.png\') 0 -160px;" alt="<?php echo _('descartada');?>" '+tooltip_ajax_call+'/><\/div>';
            break;

        case 'poll_comment':
            tooltip_ajax_call += " onmouseover=\"return tooltip.ajax_delayed(event, 'get_opinion_tooltip.php', '"+data.id+"', 10000);\"";
            html += '<img src="img/estructura/pixel.gif" width="24" height="20" style="background: url(\'img/iconos/coti.png\');" alt="<?php echo _('opinion');?>" '+tooltip_ajax_call+'/><\/div>';
            html += '<div class="sneaker-votes" onmouseout="tooltip.clear(event);" onmouseover="tooltip.clear(event);">'+data.votes+'/'+data.com+'<\/div>';
            html += '<div class="sneaker-story"><a target="_blank" href="'+data.link+'">'+data.title+'<\/a><\/div>';
            html += '<div class="sneaker-who"  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" >';
            if (data.icon != undefined && data.icon.length > 0) {
                html += '<a target="_blank" href="'+base_url+'mafioso.php?login='+data.who+'"><img src="'+data.icon+'" width=20 height=20 onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '+data.uid+');" onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" /><\/a>';
            }
            html += '&nbsp;<a target="_blank" href="'+base_url+'mafioso.php?login='+data.who+'">'+data.who.substring(0,15)+'<\/a><\/div>';
            html += '<div class="sneaker-status">'+data.status+'<\/div>';
            return html;
            break;

        case 'edited':
            tooltip_ajax_call += " onmouseover=\"return tooltip.ajax_delayed(event, 'get_link.php', '"+data.id+"', 10000);\"";
            html += '<img src="img/estructura/pixel.gif" width="24" height="20" style="background: url(\'img/iconos/coti.png\') 0 -60px;" alt="<?php echo _('editada');?>" '+tooltip_ajax_call+'/><\/div>'; //sneak-edit01
            break;
        case 'cedited':
            tooltip_ajax_call += " onmouseover=\"return tooltip.ajax_delayed(event, 'get_comment_tooltip.php', '"+data.id+"', 10000);\"";
            html += '<img src="img/estructura/pixel.gif" width="24" height="20" style="background: url(\'img/iconos/coti.png\') 0 -20px;" alt="<?php echo _('comentario editado');?>" '+tooltip_ajax_call+'/><\/div>';
            break;
        default:
            html += data.type+'<\/div>';
    }

    html += '<div class="sneaker-votes" onmouseout="tooltip.clear(event);" onmouseover="tooltip.clear(event);">'+data.votes+'/'+data.com+'<\/div>';
    if ("undefined" != typeof(data.cid) && data.cid > 0) anchor='#c-'+data.cid;
    else anchor='';
    if (do_hoygan) data.title = to_hoygan(data.title);
    if (do_flip) data.title = flipString(data.title);
    html += '<div class="sneaker-story"><a target="_blank" href="'+data.link+anchor+'"> '+data.title+' <\/a><\/div>';
    if (data.type == 'problem') {
        html += '<div class="sneaker-who">';
        html += '<img src="'+data.icon+'" width=20 height=20 onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '+data.uid+');" onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" />';
        html += '<span class="sneaker-problem">&nbsp;'+data.who+'<\/span><\/div>';
    } else if (data.uid > 0)  {
        html += '<div class="sneaker-who"  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" >';
        if (data.icon != undefined && data.icon.length > 0) {
            html += '<a target="_blank" href="'+base_url+'mafioso.php?login='+data.who+'"><img src="'+data.icon+'" width=20 height=20  onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '+data.uid+');" onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);"/><\/a>';
        }
        html += '&nbsp;<a target="_blank" href="'+base_url+'mafioso.php?login='+data.who+'">'+data.who.substring(0,15)+'<\/a><\/div>';
    } else {
        html += '<div class="sneaker-who">&nbsp;'+data.who.substring(0,15)+'<\/div>';
    }
//editar aqui para editar el estado
    if (data.status == '<?php echo _('en portada');?>')
        html += '<div class="sneaker-status"><a target="_blank" href="'+base_url+'"><span class="sneaker-published">'+data.status+'<\/span><\/a><\/div>';
    else if (data.status == '<?php echo _('descartada');?>')
        html += '<div class="sneaker-status"><a target="_blank" href="'+base_url+'jonealas.php?meta=_descartadas"><span class="sneaker-discarded">'+data.status+'<\/span><\/a><\/div>';
    else
        html += '<div class="sneaker-status"><a target="_blank" href="'+base_url+'jonealas.php">'+data.status+'<\/a><\/div>';
    return html;
}


function check_user_ping(str) {
    if (user_login != '') {
        re = new RegExp('(^|[\\s:,\\?¿!¡;<>\\(\\)])'+user_login+'([\\s:,\\?¿!¡;<>\\(\\).]|$)', "i");
        return str.match(re);
    }
    return false;
}

function check_admin_ping(str) {
    re = new RegExp('(^|[\\s:,\\?¿!¡;<>\\(\\)])(admin|admins|administradora{0,1}|administrador[ae]s)([\\s:,\\?¿!¡;<>\\(\\).]|$)', "i");
    return str.match(re);
}

function to_hoygan(str)
{
    str=str.replace(/á/gi, 'a');
    str=str.replace(/é/gi, 'e');
    str=str.replace(/í/gi, 'i');
    str=str.replace(/ó/gi, 'o');
    str=str.replace(/ú/gi, 'u');

    str=str.replace(/yo/gi, 'io');
    str=str.replace(/m([pb])/gi, 'n$1');
    str=str.replace(/qu([ei])/gi, 'k$1');
    str=str.replace(/ct/gi, 'st');
    str=str.replace(/cc/gi, 'cs');
    str=str.replace(/ll([aeou])/gi, 'y$1');
    str=str.replace(/ya/gi, 'ia');
    str=str.replace(/yo/gi, 'io');
    str=str.replace(/g([ei])/gi, 'j$1');
    str=str.replace(/^([aeiou][a-z]{3,})/gi, 'h$1');
    str=str.replace(/ ([aeiou][a-z]{3,})/gi, ' h$1');
    str=str.replace(/[zc]([ei])/gi, 's$1');
    str=str.replace(/z([aou])/gi, 's$1');
    str=str.replace(/c([aou])/gi, 'k$1');

    str=str.replace(/b([aeio])/gi, 'vvv;$1');
    str=str.replace(/v([aeio])/gi, 'bbb;$1');
    str=str.replace(/vvv;/gi, 'v');
    str=str.replace(/bbb;/gi, 'b');

    str=str.replace(/oi/gi, 'oy');
    str=str.replace(/xp([re])/gi, 'sp$1');
    str=str.replace(/es un/gi, 'esun');
    str=str.replace(/(^| )h([ae]) /gi, '$1$2 ');
    str=str.replace(/aho/gi, 'ao');
    str=str.replace(/a ver /gi, 'haber ');
    str=str.replace(/ por /gi, ' x ');
    str=str.replace(/ñ/gi, 'ny');
    str=str.replace(/buen/gi, 'GÜEN');

        // benjami
    str=str.replace(/windows/gi, 'güindous');
    str=str.replace(/we/gi, 'güe');
    // str=str.replace(/'. '/gi, '');
    str=str.replace(/,/gi, ' ');
    str=str.replace(/hola/gi, 'ola');
    str=str.replace(/ r([aeiou])/gi, ' rr$1');
    return str.toUpperCase();
}

// From http://www.revfad.com/flip.html
function flipString(aString) {
    aString = aString.toLowerCase();
    var last = aString.length - 1;
    var result = "";
    for (var i = last; i >= 0; --i) {
        result += flipChar(aString.charAt(i))
    }
    return result;
}

function flipChar(c) {
    switch (c) {
    case 'á':
    case 'a':
    case 'à':
        return '\u0250';
    case 'b':
        return 'q';
    case 'c':
        return '\u0254'; //Open o -- copied from pne
    case 'd':
        return 'p';
    case 'e':
    case 'é':
        return '\u01DD';
    case 'f':
        return '\u025F'; //Copied from pne --
        //LATIN SMALL LETTER DOTLESS J WITH STROKE
    case 'g':
        return 'b';
    case 'h':
        return '\u0265';
    case 'i':
    case 'í':
        return '\u0131'; //'\u0131\u0323' //copied from pne
    case 'j':
        return '\u0638';
    case 'k':
        return '\u029E';
    case 'l':
        return '1';
    case 'm':
        return '\u026F';
    case 'n':
    case 'ñ':
        return 'u';
    case 'ó':
    case 'o':
        return 'o';
    case 'p':
        return 'd';
    case 'q':
        return 'b';
    case 'r':
        return '\u0279';
    case 's':
        return 's';
    case 't':
        return '\u0287';
    case 'u':
        return 'n';
    case 'v':
        return '\u028C';
    case 'w':
        return '\u028D';
    case 'x':
        return 'x';
    case 'y':
        return '\u028E';
    case 'z':
        return 'z';
    case '[':
        return ']';
    case ']':
        return '[';
    case '(':
        return ')';
    case ')':
        return '(';
    case '{':
        return '}';
    case '}':
        return '{';
    case '?':
        return '\u00BF'; //From pne
    case '\u00BF':
        return '?';
    case '!':
        return '\u00A1';
    case "\'":
        return ',';
    case ',':
        return "\'";
    }
    return c;
}

//]]>
</script>
<script src="<?php echo $globals['base_url']; ?>js/sneak14.js.php?2"></script>
<?php


// Check the tab options and set corresponging JS variables
if ($current_user->user_id > 0) {
    if (!empty($_REQUEST['friends'])) {
        $taboption = 2;
        echo '<script>global_options.show_friends = true;</script>';
    } elseif (!empty($_REQUEST['admin']) && $current_user->user_id > 0 && ($current_user->admin)) {
        $taboption = 3;
        echo '<script>global_options.show_admin = true;</script>';
    }elseif (!empty($_REQUEST['devel']) && $current_user->user_id > 0 && ($current_user->devel)) {
        $taboption = 4;
        echo '<script>global_options.show_devel = true;</script>';
    } else {
        $taboption = 1;
    }
    print_sneak_tabs($taboption);
}
//////


echo '<div class="sneaker" style="margin-top: 0px">';
echo '<div class="sneaker-legend fondo-caja redondo" onmouseout="tooltip.clear(event);" onmouseover="tooltip.clear(event);">';
echo '<form action="" class="sneaker-control" id="sneaker-control" name="sneaker-control">';
echo '<div class="cotillona-caja-larga">';

if ($current_user->user_id > 0)
    echo '<a href="#chat_box"><img id="jump" class="icon arrow-down" src="img/estructura/pixel.gif" alt="caja de chat" title="ir a la caja de chat"/></a>';

echo '<img id="play-pause-img" onclick="play_pause()" src="img/estructura/pixel.gif" alt="play/pause" title="play/pause" class="icon pause"/>&nbsp;&nbsp;';

echo '<input type="checkbox" checked="checked" name="sneak-pubvotes" id="pubvotes-status" onclick="toggle_control(\'pubvotes\')" /><img src="img/estructura/pixel.gif" class="icon sneak-icon vote-published" title="'._('joneos de publicadas').'" alt="'._('votos de publicadas').'" />';
echo '<input type="checkbox" checked="checked" name="sneak-vote" id="vote-status" onclick="toggle_control(\'vote\')" /><img src="img/estructura/pixel.gif" class="icon sneak-icon vote-queued" title="'._('joneos').'" alt="'._('votos').'" />';
echo '<input type="checkbox" checked="checked" name="sneak-problem" id="problem-status" onclick="toggle_control(\'problem\')" /><img src="img/estructura/pixel.gif" class="icon sneak-icon vote-negative" alt="'._('sensura').'" title="'._('sensura').'"/>';
echo '<input type="checkbox" checked="checked" name="sneak-comment" id="comment-status" onclick="toggle_control(\'comment\')" /><img src="img/estructura/pixel.gif" class="icon sneak-icon comment-new" alt="'._('chorrada').'" title="'._('chorrada').'"/>';
echo '<input type="checkbox" checked="checked" name="sneak-new" id="new-status" onclick="toggle_control(\'new\')" /><img src="img/estructura/pixel.gif" class="icon sneak-icon story-new" alt="'._('nueva').'" title="'._('nueva').'"/>';
echo '<input type="checkbox" checked="checked" name="sneak-published" id="published-status" onclick="toggle_control(\'published\')" /><img src="img/estructura/pixel.gif" class="icon sneak-icon story-published" alt="'._('publicada').'" title="'._('en portada').'"/>';


// Only registered users can see the chat messages

if ($current_user->user_id > 0) {
    $chat_checked = 'checked="checked"';
    echo '<input type="checkbox" '.$chat_checked.' name="sneak-chat" id="chat-status" onclick="toggle_control(\'chat\')" /><img src="img/estructura/pixel.gif" class="icon sneak-icon chat" alt="'._('mensaje').'" title="'._('mensaje mafioso').'"/>';
}

echo '<input type="checkbox" checked="checked" name="sneak-post" id="post-status" onclick="toggle_control(\'post\')" /><img src="img/estructura/pixel.gif" class="icon sneak-icon post-new" alt="'._('notitas').'" title="'._('notitas').'"/>';
echo '<input type="checkbox" checked="checked" name="sneak-encuesta" id="encuesta-status" onclick="toggle_control(\'encuesta\')" /><img src="img/estructura/pixel.gif" class="icon sneak-icon poll-new" alt="'._('encuesta').'" title="'._('encuestas').'"/>';

/* neiko: */
echo _('cotillas').  ': <strong><span style="font-size: 120%;" id="ccnt">0</span></strong> ';
echo '(<strong><span id="ccntu" title="cotillas registrados">0</span></strong>+';
echo '<strong><span id="ccnta" title="cotillas anónimos">0</span></strong>)';
echo '&nbsp;&nbsp;<abbr title="'._('tiempo en milisegundos para procesar cada petición a nuestro pobre microondas').'">ping-pong</abbr>: <strong><span style="font-size: 120%;" id="ping">∞</span></strong>';
echo '</div>';
echo "</form>\n";



if ($current_user->user_id > 0){
    echo '<a name="chat_box"></a>';
    echo '<form name="chat_form" action="" onsubmit="return send_chat(this);">';
    echo '<select name="donde" id="donde">
          <option value="" selected="selected">Todos</option>
          <option value="@">Amigos</option>';

    if ($current_user->admin)
        echo '<option value="#">Admin</option>';

    if ($current_user->devel)
        echo '<option value="%">Devels</option>';

    echo '</select>&nbsp;';
    echo '<input type="text" name="comment" id="comment-input" value="" style="width: 750px;" maxlength="230" autocomplete="off" />&nbsp;<span id="ttm"><input type="submit" value="'._('enviar').'" class="button"/></span>';
    echo '</form>';
    echo '<span class="testuzuri" id="onarpena"></span>';
    /* neiko: */
    /* echo '<a onclick="toggle_roster()" class="roster-toggler" title="Mostrar lista de usuarios conectados"></a>'; */
    if (baneatuta($current_user->user_id)) echo '<strong>'.razon_ban($current_user->user_id).'</strong>';
}


echo '</div>';

echo '<div id="singlewrap">';

echo '<div class="sneaker-item">';
echo '<div class="sneaker-title">';
echo '<div class="sneaker-ts"><strong>'._('hora').'</strong></div>';
echo '<div class="sneaker-type"><strong>'._('acción').'</strong></div>';
echo '<div class="sneaker-votes"><strong><abbr title="'._('joneos').'">j</abbr>/<abbr title="'._('comentarios').'">c</abbr></strong></div>';
echo '<div class="sneaker-story">&nbsp;<strong>'._('historia').'</strong></div>';
echo '<div class="sneaker-who">&nbsp;<strong>'._('mafioso/qué').'</strong></div>';
echo '<div class="sneaker-status"><strong>'._('estado').'</strong></div>';
echo "</div>\n";
echo "</div>\n";


echo '<div id="items">';
for ($i=0; $i<$max_items;$i++) {
    echo '<div class="sneaker-item">&nbsp;</div>';
}

echo '</div><br/>';


do_footer();

function print_sneak_tabs($option) {
    global $current_user, $globals;
    $active = array();

    // Avoid PHP warnings
    for ($n=1; $n <= 9; $n++) $active[$n] = '';

    $active[$option] = ' class="current"';
    echo '<ul class="tabmain">';

    echo '<li'.$active[1].'><a href="'.$globals['base_url'].'cotillona.php">'._('todos').'</a></li>';
    echo '<li'.$active[2].'><a href="'.$globals['base_url'].'cotillona.php?friends=1">'._('amigos').'</a></li>';

    if ($current_user->user_id > 0 && $current_user->devel) {
    echo '<li'.$active[4].'><a href="'.$globals['base_url'].'cotillona.php?devel=1">'._('devels').'</a></li>';
    }

    if ($current_user->user_id > 0 && $current_user->admin) {
    echo '<li'.$active[3].'><a href="'.$globals['base_url'].'cotillona.php?admin=1">'._('admin').'</a></li>';
    }

    if (isset($_GET['hoygan']) && $_GET['hoygan'] == '1')
      echo '<li><a href="'.$globals['base_url'].'cotillona.php?hoygan=1"><em>'._('cotillhoygan').'</em></a></li>';
    else
      echo '<li><a href="'.$globals['base_url'].'cotillona.php?hoygan=1">'._('cotillhoygan').'</a></li>';

    if (isset($_GET['flip']) && $_GET['flip'] == '1')
      echo '<li><a href="'.$globals['base_url'].'cotillona.php?flip=1"><em>'._('al revés').'</em></a></li>';
    else
      echo '<li><a href="'.$globals['base_url'].'cotillona.php?flip=1">'._('al revés').'</a></li>';

      if ($current_user->admin)
      echo '<li style="margin-left: 10px;"><a class="separada" href="'.$globals['base_url'].'admin/cotillona.php">'._('log cotillona').'</a></li>';

    echo '</ul>';
}
