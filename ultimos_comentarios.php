<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

do_header('Últimos comentarios | Jonéame');

$comments = $db->get_results("SELECT comment_id, link_id FROM comments,links WHERE link_id=comment_link_id ORDER BY comment_date DESC");

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
