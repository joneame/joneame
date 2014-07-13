<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include_once(mnminclude.'post.php');

$globals['search_options'] = array('w' => 'posts');

array_push($globals['extra_js'], 'jquery-form.pack.js');
array_push($globals['extra_js'], 'posts01.js');

do_header('Últimas notitas | Jonéame');

echo '<h2> Notitas escritas las últimas 24 horas </h2><br/>';

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
echo '<br/>';
do_best_posts();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

$min_date = date("Y-m-d H:00:00", time() - 86000);

$posts = $db->get_col("SELECT post_id FROM posts WHERE post_date > '$min_date' ORDER BY post_date DESC");

    if ($posts) {

        echo '<div id="newswrap">'."\n";
        echo '<div class="notes">';
        echo '<ol class="notitas-list">';

        foreach($posts as $post_id) {
            $post = Post::from_db($post_id);
            $post->print_summary();
        }

        echo "</ol>\n";
        echo '</div>';
        echo '</div>';
    }

do_footer();
