<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>
// David <neikokz at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include_once('../config.php');
include(mnminclude.'link.php');

// Sanear un poco. Porsiaca...

if ($current_user->user_level == 'disabled')
    die(_('estás baneado'));

if (!is_numeric($_POST['link_id']))
    die(_('número de historia no válido'));

if (!is_numeric($_POST['user_id']))
    die(_('usuario incorrecto'));

if (!is_numeric($_POST['randkey']))
    die(_('clave incorrecta'));

if (!$current_user->admin && ( $_POST['type'] == 'true' || $_POST['especial'] == 'true' ) )
    die(_('no eres administrador'));


$id = intval($_POST['link_id']);
$link = Link::from_db($id);

if (!$link->title)
    die(_('la historia no existe'));

if($link->comentarios_permitidos == 0) {
    die(_('comentarios bloqueados temporalmente'));
}

// Vamos al lío xD
echo insert_comment();

function insert_comment () {
    global $link, $db, $current_user, $globals;

    $error = '';

    if ($globals['comments_page_size'] > 0 ) $limit = 'LIMIT ' . $globals['comments_page_size'];

    require_once(mnminclude.'ban.php');
    if(check_ban_proxy()) return _('dirección IP no permitida');

    // Check if is a POST of a comment
    if(( $link->votes > 0 || $link->status == "queued" ) && $link->date > $globals['now']-$globals['time_enabled_comments'] &&
            $link->comments < $globals['max_comments'] &&
            intval($_POST['link_id']) == $link->id && $current_user->authenticated &&
            intval($_POST['user_id']) == $current_user->user_id &&
            ($current_user->user_karma > $globals['min_karma_for_comments'] || $current_user->user_id == $link->author) &&
            intval($_POST['randkey']) > 0 &&
            mb_strlen(trim($_POST['comment_content'])) > 2 ) {

        require_once(mnminclude.'comment.php');
        $comment = new Comment;
        $comment->link=$link->id;
        $comment->randkey=intval($_POST['randkey']);
        $comment->author=intval($_POST['user_id']);
        $comment->karma=round($current_user->user_karma);
        $comment->content=clean_text($_POST['comment_content'], 0, false, 1000000);
        // Check if is an admin comment
        if ($current_user->admin && $_POST['type'] == 'true' && $_POST['especial'] == 'false' ) { //mostrar nombre
            $comment->karma = 20;
            $comment->type = 'especial';
        }
        elseif ($current_user->admin && $_POST['type'] == 'true' && $_POST['especial'] == 'true' ) { //ocultar nombre
            $comment->karma = 20;
            $comment->type = 'admin';
        }

        if (mb_strlen($comment->content) > 0 && preg_match('/[a-zA-Z:-]/', $_POST['comment_content'])) { // Check there are at least a valid char
            $already_stored = intval($db->get_var("select count(*) from comments where comment_link_id = $comment->link and comment_user_id = $comment->author and comment_randkey = $comment->randkey"));
            // Check the comment wasn't already stored
            if (!$already_stored) {
                if ($comment->type != 'admin' || $comment->type != 'especial') {
                    // Lower karma to comments' spammers
                    $comment_count = (int) $db->get_var("select count(*) from comments where comment_user_id = $current_user->user_id and comment_date > date_sub(now(), interval 3 minute)");
                    // Check the text is not the same
                    $same_count = $comment->same_text_count() + $comment->same_links_count();
                } else {
                    $comment_count  = $same_count = 0;
                }
                $comment_limit = round(min($current_user->user_karma/6, 2) * 2.5);
                if (($comment_count > $comment_limit || $same_count > 2) && !$current_user->admin) {
                    require_once(mnminclude.'user.php');
                    $reduction = 0;
                    if ($comment_count > $comment_limit) {
                        $reduction += ($comment_count-3) * 0.1;
                    }
                    if ($same_count > 1) {
                        $reduction += $same_count * 0.25;
                    }
                    if ($reduction > 0) {
                        $user = new User;
                        $user->id = $current_user->user_id;
                        $user->read();
                        $user->karma = $user->karma - $reduction;
                        syslog(LOG_NOTICE, "Joneame: story decreasing $reduction of karma to $current_user->user_login (now $user->karma)");
                        $user->store();
                        require_once(mnminclude.'annotation.php');
                        $annotation = new Annotation("karma-$user->id");
                        $annotation->append(_('texto repetido o abuso de enlaces en comentarios').": -$reduction, carisma: $user->karma\n");
                        $error .= ' ' . ('comentario añadido, pero con penalización de carisma por texto repetido, abuso de enlaces o demasiados comentarios en un espacio de tiempo muy breve');
                    }
                }
                $comment->store();
                $comment->insert_vote();
                $link->update_comments();
                // Re read link data
                $link->read();
            } else {
                $error .= ' ' . ('duplicado');
            }
        } else {
            $error .= ' ' . ('caracteres no válidos');
        }

    } else {
        $error .= ' ' . ('texto muy breve, carisma bajo o usuario incorrecto');
    }
    if ($error)
        return 'KO:'.$error;

    $comments = $db->get_col("SELECT comment_id FROM comments WHERE comment_link_id=$link->id AND comment_user_id=$current_user->user_id ORDER BY comment_id desc LIMIT 1");

    if ($comments) {

        require_once(mnminclude.'comment.php');
        $comment = new Comment;
        foreach($comments as $comment_id) {
            $comment->id=$comment_id;
            $comment->edited = true;
            $comment->read();
            echo 'OK:';
            $comment->return_summary($link, 700, true);
        }

    }
}

?>
