<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');

do_header('Últimos comentarios | Jonéame');

$page_size = (int)$globals['comments_page_size'];

$comments = $db->get_results("SELECT comment_id, link_id FROM comments,links WHERE link_id=comment_link_id ORDER BY comment_date DESC LIMIT $page_size");

    if ($comments) {

        echo '<h2>Últimos comentarios</h2><br/>';
        echo '<ol class="comments-list">';
        require_once(mnminclude.'comment.php');
        require_once(mnminclude.'link.php');

        foreach($comments as $comment_id) {

             if ($last_link != $comment_id->link_id) {
                $link = Link::from_db($comment_id->link_id);
                echo '<h4 class="izquierda">';
                echo '<a href="'.$link->get_permalink().'">'. $link->title. '</a>';
                echo ' ['.$link->comments.']';
                echo '</h4>';
                $last_link = $comment_id->link_id;
             }

                    $comment = Comment::from_db($comment_id->comment_id);
            $comment->print_summary($link, 700, true);
            echo "\n";
        }
        echo "</ol>\n";
    }
do_footer();
