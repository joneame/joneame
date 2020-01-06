<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'encuestas.php');
include(mnminclude.'html1.php');
include(mnminclude.'opinion.php');

$encuesta = new Encuesta;

if (!isset($_REQUEST['id']) && $globals['base_encuesta_url'] && $_SERVER['PATH_INFO']) {
    $url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
    array_shift($url_args); // The first element is always a "/"
    $encuesta->id = intval($url_args[0]);
} else {
    $url_args = preg_split('/\/+/', $_REQUEST['id']);
    $encuesta->id=intval($url_args[0]);
    if($encuesta->id > 0 && $globals['base_encuesta_url']) {
        // Redirect to the right URL if the link has a "semantic" uri
        header ('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $encuesta->get_relative_individual_permalink());
        die;
    }
}

$encuesta->read(); //no eliminar, da error al votar

if (!$encuesta->read)
    do_error(_('no existe la encuesta'), 404);

$globals['extra_js'] = array('polls.js');

do_header(_('Encuesta: '.htmlspecialchars(text_to_summary($encuesta->titulo)).' | Jonéame'));

echo '<div id="sidebar">';
do_last_questions ();
encuestas_mas_votadas();
echo '</div>' . "\n";

echo '<div id="newswrap"><div class="notes">';

encuestas_utils();

$encuesta->print_encuesta();


// Print polls comments

$sql = "SELECT id FROM polls_comments WHERE encuesta_id=$encuesta->id ORDER BY orden ASC";
$poll_comment = $db->get_col($sql);

if ($poll_comment) {

    echo '<div class="comments">';
    echo '<ol class="comments-list">';

    foreach ($poll_comment as $dbanswer) {

        $answer = new Opinion;
    $answer->id = $dbanswer;
    $answer->read();

        echo $answer->print_opinion();

    }
    echo "</ol>\n";
    echo '</div>';

}

// User can comment
if ( $current_user->user_id > 0 ){
    echo '<div id="ajaxcontainer"><div id="ajaxcomments"></div></div>';
    print_comment_form();

} else {
        echo '<div class="barra redondo">'."\n";
        echo '<a href="'.$globals['base_url'].'login.php?return='.$_SERVER['REQUEST_URI'].'">'._('Entra con tu cuenta de usuario').'</a> '._('si deseas escribir tu opinión a esta encuesta').'. '._('O crea tu cuenta haciendo clic'). ' <a href="'.$globals['base_url'].'register.php">aquí</a>'."\n";
        echo '</div>'."\n";
}


echo '</div></div>'; //newswrap notes

do_footer();

function print_comment_form() {
    global $encuesta, $current_user, $globals;

    echo '<div class="commentform">'."\n";
    echo '<form action="" method="post">'."\n";
    echo '<h4>'._('escribe un comentario').'</h4><fieldset class="fondo-caja">'."\n";
    echo '<div style="float: right;">'."\n";
    print_simpleformat_buttons('poll_content');
    echo '</div>'."\n";
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

// must be the same as in encuestas.php!!
function encuestas_utils(){
    global $globals, $current_user;

    echo '<div style="margin-top: 25px;">'; // :D
    echo '<ul class="barra redondo herramientas">';
    if ($current_user->user_id > 0)
    echo '<li><a href="'.$globals['base_url'].'nueva_encuesta.php" class="icon poll-new">enviar nueva encuesta</a></li>';
    if (!$_REQUEST['fecha_fin'])
    echo '<li><a href="'.$globals['base_url'].'encuestas.php?fecha_fin=1" class="icon permalink">ordenar por fecha de finalización</a></li>';
    if (!$_REQUEST['unvoted'])
    echo '<li><a href="'.$globals['base_url'].'encuestas.php?unvoted=1" class="icon permalink">no votadas</a></li>';

        echo '<li><a href="'.$globals['base_url'].'encuestas_rss.php" class="icon rss">encuestas por RSS</a></li>';
    echo '</ul></div><br/>';
}
