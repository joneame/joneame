<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'comment.php');
include(mnminclude.'link.php');

$globals['ads'] = true;

$page_size = 50;

if (!isset($_REQUEST['id']) && $globals['base_comment_url'] && $_SERVER['PATH_INFO']) {
    $url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
    array_shift($url_args); // The first element is always a "/"
    $id = intval($url_args[0]);
} else {
    $url_args = preg_split('/\/+/', $_REQUEST['id']);
    $id=intval($url_args[0]);
    if($id > 0 && $globals['base_comment_url']) {
        // Redirect to the right URL if the link has a "semantic" uri
        header ('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $comment->get_relative_individual_permalink());
        die;
    }
}

$comment = new Comment;
$comment->id = $id;

if (!$comment->read()) {
    do_error(_('comentario no encontrado'), 404);
}

$link = new Link;
$link->id=$comment->link;
$link->read();

$globals['link'] = $link;

// Change to a min_value is times is changed for the current link_status
if ($globals['time_enabled_comments_status'][$link->status]) {
    $globals['time_enabled_comments'] = min($globals['time_enabled_comments_status'][$link->status],
                                            $globals['time_enabled_comments']);
}

$username = $comment->type == 'admin'?'admin':$comment->username;

do_header(_('comentario de') . ' ' . $username . ' (' . $comment->id .') | Jonéame');

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
//do_best_stories();
//do_best_comments();
echo '</div>';
/*** END SIDEBAR ***/

echo '<div id="newswrap">';

echo '<h1><a href="'.$link->get_permalink().'" class="titular">'. $link->title. '</a></h1>';

echo '<ol class="comments-list">';
echo '<li>';
$comment->print_summary($link, 10000, true);
echo "</li>\n";
echo "</ol>\n";

// Print answers to the comment
$sql = "SELECT conversation_from as comment_id FROM conversations, comments WHERE conversation_type='comment' and conversation_to = $comment->id and comment_id = conversation_from ORDER BY conversation_from asc LIMIT $page_size";
$answers = $db->get_results($sql);
if ($answers) {

    echo '<div style="padding-left: 40px; padding-top: 10px">';
    echo '<ol class="comments-list">';
    foreach ($answers as $dbanswer) {
        $answer = Comment::from_db($dbanswer->comment_id);
        echo '<li>';
        $answer->print_summary($link);
        echo '</li>';
    }
    echo "</ol>\n";
    echo '</div>';
}

if($link->comentarios_permitidos == 0) {

        echo '<h4 class="redondo">';
        echo _('comentarios cerrados temporalmente');
        echo '</h4>';

} else if ($current_user->authenticated) {
        print_comment_form();
} else {
        echo '<div class="barra redondo">';
        echo '<a href="'.$globals['base_url'].'login.php?return='.$_SERVER['REQUEST_URI'].'">'._('Entra con tu cuenta de usuario').'</a> '._('si deseas escribir comentarios').'. '._('O crea tu cuenta haciendo clic'). ' <a href="'.$globals['base_url'].'register.php">aquí</a>';
        echo '</div>';
}

echo '</div>';

do_footer();

function print_comment_form() {
    global $link, $current_user;

    if (!$link->sent) return;

    echo '<div class="commentform">';
    echo '<form action="" method="post">';
    echo '<h4>'._('escribe un comentario').'</h4><fieldset class="fondo-caja">';
    echo '<div>';
    print_simpleformat_buttons('comment');
    echo '<div style="margin-top: 10px;"><textarea name="comment_content" id="comment" cols="75" rows="12"></textarea></div>';
    echo '<input class="button" type="submit" name="submit" value="'._('enviar comentario').'" />';

    // Allow gods to put "admin" comments which does not allow votes
    if ($current_user->admin ) {

     echo '&nbsp;&nbsp;&nbsp;&nbsp;<input name="type" type="checkbox" value="admin" id="comentario-admin"/>&nbsp;<label for="type">'._('comentario admin').'</strong></label>';
     echo '&nbsp;&nbsp;&nbsp;&nbsp;<input name="especial" type="checkbox" value="1" id="comentario-especial"/>&nbsp;<label for="type">'._('no mostrar mi nick').'</strong></label>';

    }


    echo '<input type="hidden" name="process" value="newcomment" />';
    echo '<input type="hidden" name="randkey" value="'.rand(1000000,100000000).'" />';
    echo '<input type="hidden" name="link_id" value="'.$link->id.'" />';
    echo '<input type="hidden" name="user_id" value="'.$current_user->user_id.'" />';
    echo '</fieldset>';
    echo '</form>';
    echo "</div>\n";

}