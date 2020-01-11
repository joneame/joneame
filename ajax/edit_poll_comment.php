<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

if (! defined('mnmpath')) {
    include('../config.php');
    include(mnminclude.'html1.php');
    include(mnminclude.'encuestas.php');
    require_once(mnminclude.'opinion.php');
}

if (!empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $id = $db->escape($_REQUEST['id']);
    $comment = new Opinion;
    $comment->id = $id;
    if (!$comment->read()) die('KO:El comentario no existe');
} else {
    die('KO:Error interno');
}

$encuesta = new Encuesta;
$encuesta->id = $comment->encuesta_id;

if (!$encuesta->read_basic()) die('KO:La encuesta no existe');

if ($_POST['process']=='editcomment') {
    save_comment();
} else {
    print_edit_form();
}

function print_edit_form() {
    global $encuesta, $comment, $current_user, $site_key, $globals;

    if ($current_user->user_level != 'god' && time() - $comment->date > $globals['comment_edit_time']) die;

    $rows = min(40, max(substr_count($comment->contenido, "\n") * 2, 8));
    echo '<div class="commentform">';
    echo '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="post">';
    echo '<fieldset class="fondo-caja redondo"><legend class="mini barra redondo">'._('editar comentario').'</legend>';
    print_simpleformat_buttons('comment_'.$comment->id);
    echo '<div style="clear: right"><textarea name="comment_content" id="comment_'.$comment->id.'" rows="'.$rows.'" cols="75">'.$comment->contenido.'</textarea></div>';
    echo '<input class="button" type="button" id="submit_com_'.$comment->id.'" onClick="edit_comment('.$comment->id.');" name="submit" value="'._('editar comentario').'" />';

    echo '<img id="spinner_'.$comment->id.'" class="blank" src="'.$globals['base_url'].'img/estructura/pixel.gif" width="16" height="16"/>';


    echo '<br/><span id="error_com_'.$comment->id.'"></span>';

    echo '<input type="hidden" id="process_'.$comment->id.'" name="process_'.$comment->id.'" value="editcomment" />';

    echo '<input type="hidden" id="poll_id_'.$comment->id.'" name="poll_id_'.$comment->id.'" value="'.$encuesta->id.'" />';
    echo '<input type="hidden" id="user_id_'.$comment->id.'" name="user_id_'.$comment->id.'" value="'.$current_user->user_id.'" />';
    echo '</fieldset>';
    echo '</form>';
    echo "</div>\n";
}

function save_comment () {
    global $db, $comment, $current_user, $globals;

    if(intval($_POST['id']) == $comment->id && $current_user->authenticated &&
        // Allow the author of the post
        ((intval($_POST['user_id']) == $current_user->user_id &&
        $current_user->user_id == $comment->por &&
        time() - $comment->date < $globals['comment_edit_time'] * 1.1) || $current_user->user_level == 'god') &&
        strlen(trim($_POST['poll_content'])) > 2 ) {

        $comment->contenido=clean_text($_POST['poll_content'], 0, false, 10000);

        if (strlen($comment->contenido) > 0 ) {
            $comment->store();

            $commentblock = '<ol class="comments-list">';
            $commentblock .= $comment->print_opinion();
            $commentblock .= "\n";
            $commentblock .= "</ol>\n";

            die('OK:'.$commentblock);
        }
    } else {
        die ('KO:'._('error actualizando, probablemente tiempo de edición excedido'));
    }
}

?>
