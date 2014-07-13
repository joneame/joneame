<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (! defined('mnmpath')) {
    include('../config.php');
    include(mnminclude.'html1.php');
    include_once(mnminclude.'post.php');
}

$post = new Post;
if (!empty($_REQUEST['user_id'])) {
    $post_id = intval($_REQUEST['post_id']);
    if ($post_id > 0) {
        save_post($post_id);
    } else {
        save_post(0);
    }
} else {
    if (!empty($_REQUEST['id'])) {
        // She wants to edit the post
        $post->id = intval($_REQUEST['id']);
        if ($post->read()) $post->print_edit_form();
    } else {
        // A new post
        if ((!$post->read_last($current_user->user_id) || time() - $post->date > $globals['tiempo_entre_notitas']) || $current_user->admin ) {
            $post = new Post;
            $post->author=$current_user->user_id;
        if ($_REQUEST['reference']) // si tiene referencia (respuesta), añade la respuesta y un espacio en blanco
        $post->content = clean_text($_REQUEST['reference']). ' ';
            $post->print_edit_form();
        } else {
            echo 'Error: ' . _('debes esperar un poco más antes de poner otra notita');
            die;
        }
    }
}

function save_post ($post_id) {
    global $db, $post, $current_user, $globals, $site_key;


    $post = new Post;
    $_POST['post'] = clean_text($_POST['post'], 0, false, $globals['longitud_notitas']);
    if (mb_strlen($_POST['post']) < 5) {
        echo 'ERROR: ' . _('texto muy corto');
        die;
    }
    if ($post_id > 0) {
        $post->id = $post_id;
        if (! $post->read()) die;
        if(
            // Allow the author of the post
            ((intval($_POST['user_id']) == $current_user->user_id &&
            $current_user->user_id == $post->author &&
            time() - $post->date < 3600) ||
            // Allow the admin
            ($current_user->user_level == 'god' && time() - $post->date < 2147483647)) && $_POST['key']  == $post->randkey ) {
            $post->content= normalize_smileys($_POST['post']);

                        if (($_POST['type']) == 'admin')
                               $post->tipo= 'admin';
                        else if ($post->tipo == 'encuesta') $post->tipo = 'encuesta';
            else  $post->tipo='normal';

            if (strlen($post->content) > 0 ) {
                $post->store();
            }
        } else {
            echo 'ERROR: ' . _('no tienes permisos para guardar la notita');
            die;
        }
    } else {

        if ($current_user->user_id != intval($_POST['user_id'])) die;

        if ($current_user->user_karma < $globals['min_karma_for_posts']) {
            echo 'ERROR: ' . _('tu carisma es demasiado bajo como para poner una notita');
            die;
        }

        // Check the post wasn't already stored
        $post->randkey=intval($_POST['key']);
        $post->author=$current_user->user_id ;
        $post->content=$_POST['post'];

        // check source
        if ($globals['mobile']) $post->src='mobile';
        else $post->src='web';

        $already_stored = intval($db->get_var("select count(*) from posts where post_user_id = $current_user->user_id and post_date > date_sub(now(), interval 12 hour) and post_randkey = $post->randkey")) + $post->same_text_count();
        if (!$already_stored) {
            // Verify that there are a period of 30 seconds between posts.
            if(intval($db->get_var("select count(*) from posts where post_user_id = $current_user->user_id and post_date > date_sub(now(), interval 2 minutes)"))> 0) {
                echo 'ERROR: ' . _('debes esperar 2 minutos entre notitas');
                die;
            };

            $same_links = $post->same_links_count();
            if ($same_links > 2) {
                require_once(mnminclude.'user.php');
                $user = new User;
                $user->id = $current_user->user_id;
                $user->read();
                $reduction = $same_links * 0.2;
                $user->karma = $user->karma - $reduction;
                syslog(LOG_NOTICE, "Joneame: reducción de carisma de $reduction al usuario $user->username por texto repetido (ahora $user->karma)");
                $user->store();

                require_once(mnminclude.'annotation.php');
                $annotation = new Annotation("karma-$user->id");
                $annotation->append(_('demasiados enlaces al mismo dominio en las notas').": -$reduction, carisma: $user->karma\n");

            }
            $post->store();
        } else {
            echo 'ERROR: ' . _('notita guardada previamente');
            die;
        }
    }

    /* Imprime a la derecha si es una respuesta */
    if ($post->is_answer() && $post_id == 0){
        echo '<div style="padding-left: 40px;>'."\n";
        echo '<ol class="notitas-list">';
    }

    $post->can_answer = true;

    $post->print_summary();

    if ($post->is_answer() && $post_id == 0){
        echo "</ol>\n";
        echo '</div>'."\n";
        echo '<div id="respuesta-'.$post->id.'"></div>';
    }
}

?>
