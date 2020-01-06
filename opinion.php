<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'opinion.php');
include(mnminclude.'encuestas.php');

$globals['ads'] = true;

if (!isset($_REQUEST['id']) && $globals['base_poll_comment_url'] && $_SERVER['PATH_INFO']) {
    $url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
    array_shift($url_args); // The first element is always a "/"
    $id = intval($url_args[0]);
} else {
    $url_args = preg_split('/\/+/', $_REQUEST['id']);
    $id=intval($url_args[0]);
    if($id > 0 && $globals['base_poll_comment_url']) {
        // Redirect to the right URL if the link has a "semantic" uri
        header ('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $comment->get_relative_individual_permalink());
        die;
    }
}

$comment = new Opinion;
$comment->id = $id;

if (!$comment->read()) {
    do_error(_('opinion no encontrada'), 404);
}

$encuesta = new Encuesta;
$encuesta->id=$comment->encuesta_id;
$encuesta->read();

$globals['encuesta'] = $encuesta;

// Change to a min_value is times is changed for the current link_status
if ($globals['time_enabled_comments_status'][$link->status]) {
    $globals['time_enabled_comments'] = min($globals['time_enabled_comments_status'][$link->status],
                                            $globals['time_enabled_comments']);
}

$globals['extra_js'] = array('polls.js');

do_header(_('Opinión de') . ' ' . $comment->user_login . ' (' . $comment->id .') | Jonéame');

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_last_questions();

echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

echo '<h1><a href="'.$encuesta->get_relative_individual_permalink().'" class="titular">'. $encuesta->titulo. '</a></h1>';
$encuesta->print_encuesta();
echo '<div class="comments">';
echo '<ol class="comments-list">';
echo '<li>';
echo $comment->print_opinion();
echo "</li>\n";
echo "</ol>\n";

if ($current_user->authenticated) {
    echo '<div id="ajaxcontainer"><div id="ajaxcomments"></div></div>';
        print_comment_form();
    echo '</div>';
} else {
        echo '<div class="barra redondo">'."\n";
        echo '<a href="'.$globals['base_url'].'login.php?return='.$_SERVER['REQUEST_URI'].'">'._('Entra con tu cuenta de usuario').'</a> '._('si deseas escribir tu opinión a esta encuesta').'. '._('O crea tu cuenta haciendo clic'). ' <a href="'.$globals['base_url'].'register.php">aquí</a>'."\n";
        echo '</div>'."\n";
}

echo '</div>';

do_footer();

function print_comment_form() {
    global $encuesta, $current_user, $globals;

    // esto debería estar en do_header pero me parece que paso. además así sólo se carga cuando sea necesario
    echo '<script src="'.$globals['base_url'].'js/poll_com.js"></script>';

    echo '<div class="commentform">'."\n";
    echo '<form action="" method="post">'."\n";
    echo '<h4>'._('escribe un comentario').'</h4><fieldset class="fondo-caja">'."\n";
    echo '<div style="float: right;">'."\n";
    print_simpleformat_buttons('poll_content');
    echo '</div><span class="note"><strong>'._('¡eh tío!').':</strong> '._('comentarios serios, constructivos, xenófobos, racistas o difamatorios causarán el baneo de la cuenta de usuario y expulsión de la mafia').'</span></label>'."\n";
    echo '<div style="margin-top: 10px;"><textarea name="poll_content" id="poll_content" cols="75" rows="12"></textarea></div>'."\n";
    echo '<input type="button" class="button" name="submit" id="submit_com" value="'._('enviar comentario').'" onClick="submit_comment();"/>'."\n";

    echo '<img id="spinner" class="blank" src="'.$globals['base_url'].'img/estructura/pixel.gif" width="16" height="16"/>';

    echo '<br/><span id="error_com"></span>';

    echo '<input type="hidden" id="process" name="process" value="newcomment" />'."\n";
    echo '<input type="hidden" id="poll_id" name="poll_id" value="'.$encuesta->id.'" />'."\n";
    echo '<input type="hidden" id="user_id" name="user_id" value="'.$current_user->user_id.'" />'."\n";
    echo '</fieldset>'."\n";
    echo '</form>'."\n";
    echo "</div>\n";

}