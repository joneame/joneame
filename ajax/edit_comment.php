<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

if (! defined('mnmpath')) {
    include('../config.php');
    include(mnminclude.'html1.php');
    include(mnminclude.'link.php');
    require_once(mnminclude.'comment.php');
}

if (!empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $id = intval($_REQUEST['id']);
    $comment = Comment::from_db($id);
    if (!$comment->read) die('KO:El comentario no existe');
} else {
    die('KO:Error interno');
}

$link = Link::from_db($comment->link);

if (!$link->read) die('KO:La historia no existe');

if ($_POST['process']=='editcomment') {
    save_comment();
} else {
    print_edit_form();
}

function print_edit_form() {
    global $link, $comment, $current_user, $site_key, $globals;

    if ($current_user->user_level != 'god' && time() - $comment->date > $globals['comment_edit_time']) die;

    $rows = min(40, max(substr_count($comment->content, "\n") * 2, 8));
    echo '<div class="commentform">';
    echo '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="post">';
    echo '<fieldset class="fondo-caja redondo"><legend class="mini barra redondo">'._('editar comentario').'</legend>';
    print_simpleformat_buttons('comment_'.$comment->id);
    echo '<div style="clear: right"><textarea name="comment_content" id="comment_'.$comment->id.'" rows="'.$rows.'" cols="75">'.$comment->content.'</textarea></div>';
    echo '<input class="button" type="button" id="submit_com_'.$comment->id.'" onClick="edit_comment('.$comment->id.');" name="submit" value="'._('editar comentario').'" />';

    echo '<img id="spinner_'.$comment->id.'" class="blank" src="'.$globals['base_url'].'img/estructura/pixel.gif" width="16" height="16"/>';

    // Allow gods to put "admin" comments which does not allow votes
    if ($current_user->admin) {
        $checked = 'checked="true"';
        echo '<div style="float: right; margin-right: 10px;">';
    } else {
        echo '<div style="display: none;">';
    }
    if ($comment->type == 'admin') {
        echo '&nbsp;&nbsp;&nbsp;&nbsp;<input name="type" type="checkbox" id="comentario-admin_'.$comment->id.'" '.$checked.'/>&nbsp;<label for="comentario-admin_'.$comment->id.'">'._('comentario admin').' </label>';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;<input name="especial" type="checkbox" id="comentario-especial_'.$comment->id.'" '.$checked.'/>&nbsp;<label for="comentario-especial_'.$comment->id.'">'._('no mostrar mi nick').' </label>';
    } elseif ($comment->type == 'especial') {
        echo '&nbsp;&nbsp;&nbsp;&nbsp;<input name="type" type="checkbox" id="comentario-admin_'.$comment->id.'" '.$checked.'/>&nbsp;<label for="comentario-admin_'.$comment->id.'">'._('comentario admin').' </label>';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;<input name="especial" type="checkbox" id="comentario-especial_'.$comment->id.'"/>&nbsp;<label for="comentario-especial_'.$comment->id.'">'._('no mostrar mi nick').' </label>';
    } else {
        echo '&nbsp;&nbsp;&nbsp;&nbsp;<input name="type" type="checkbox" id="comentario-admin_'.$comment->id.'"/>&nbsp;<label for="comentario-admin_'.$comment->id.'">'._('comentario admin').' </label>';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;<input name="especial" type="checkbox" id="comentario-especial_'.$comment->id.'"/>&nbsp;<label for="comentario-especial_'.$comment->id.'">'._('no mostrar mi nick').' </label>';
    }
    echo '</div>';

    echo '<br/><span id="error_com_'.$comment->id.'"></span>';

    echo '<input type="hidden" id="process_'.$comment->id.'" name="process_'.$comment->id.'" value="editcomment" />';
    echo '<input type="hidden" id="key_'.$comment->id.'" name="key_'.$comment->id.'" value="'.md5($comment->randkey.$site_key).'" />';
    echo '<input type="hidden" id="link_id_'.$comment->id.'" name="link_id_'.$comment->id.'" value="'.$link->id.'" />';
    echo '<input type="hidden" id="user_id_'.$comment->id.'" name="user_id_'.$comment->id.'" value="'.$current_user->user_id.'" />';
    echo '</fieldset>';
    echo '</form>';
    echo "</div>\n";
}

function save_comment () {
    global $link, $db, $comment, $current_user, $globals, $site_key;

    if(intval($_POST['id']) == $comment->id && $current_user->authenticated &&
        // Allow the author of the post
        ((intval($_POST['user_id']) == $current_user->user_id &&
        $current_user->user_id == $comment->author &&
        time() - $comment->date < $globals['comment_edit_time'] * 1.1) || $current_user->user_level == 'god' || $comment->type == 'admin') &&
        $_POST['key']  == md5($comment->randkey.$site_key)  &&
        strlen(trim($_POST['comment_content'])) > 2 ) {
        $comment->content=clean_text($_POST['comment_content'], 0, false, 1000000);

        if ($current_user->admin) {
            if ($_POST['type'] == 'true' && $_POST['especial'] == 'true') {
                $comment->type = 'admin';
            } elseif ($_POST['type'] == 'true') {
                $comment->type = 'especial';
            } else {
                $comment->type = 'normal';
            }
        }

        if (strlen($comment->content) > 0 ) {
            $comment->store();
            $comment->edited = true;
            echo 'OK:';
            $comment->return_summary($link, 700, true);
        }
    } else {
        die ('KO:'._('error actualizando, probablemente tiempo de edición excedido'));
    }
}

?>
