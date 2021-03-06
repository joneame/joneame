<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'link.php');

$index_size = 5000;

header('Content-Type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';

if (empty($_SERVER['QUERY_STRING'])) {
    do_master($index_size);
} else {
    if (isset($_REQUEST['statics'])) {
        do_statics();
    } else {
        $page = (int) $_REQUEST['page'];
        if ($_REQUEST['type'] == 'links') {
            do_published($page);
        } elseif ($_REQUEST['type'] == 'posts') {
            do_posts($page);
        } elseif ($_REQUEST['type'] == 'comments') {
            do_comments($page);
        } elseif ($_REQUEST['type'] == 'quotes') {
            do_quotes($page);
        } elseif ($_REQUEST['type'] == 'users') {
            do_users($page);
        } elseif ($_REQUEST['type'] == 'polls') {
            do_polls($page);
        } elseif ($_REQUEST['type'] == 'poll_comments') {
            do_poll_comments($page);
        }
    }
}

function do_master($size) {
    global $globals, $db;

    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    echo '<sitemap>';
    echo '<loc>https://'.get_server_name().$globals['base_url'].'sitemap.php?statics</loc>';
    echo '</sitemap>';

    $count = (int) $db->get_var("select count(*) from links where link_status = 'published'");
    $indexes = ceil($count/$size);
    for ($i = 0; $i < $indexes; $i++) {
        echo '<sitemap>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].'sitemap.php?type=links&amp;page='.$i.'</loc>';
        echo '</sitemap>';
    }

    $count = (int) $db->get_var("select count(*) from posts join users on users.user_id = posts.post_user_id and users.user_level != 'disabled' where post_type = 'normal'");
    $indexes = ceil($count/$size);
    for ($i = 0; $i < $indexes; $i++) {
        echo '<sitemap>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].'sitemap.php?type=posts&amp;page='.$i.'</loc>';
        echo '</sitemap>';
    }

    $count = (int) $db->get_var("select count(*) from comments join users on users.user_id = comments.comment_user_id and users.user_level != 'disabled'");
    $indexes = ceil($count/$size);
    for ($i = 0; $i < $indexes; $i++) {
        echo '<sitemap>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].'sitemap.php?type=comments&amp;page='.$i.'</loc>';
        echo '</sitemap>';
    }

    $count = (int) $db->get_var("select count(*) from cortos where activado = 1");
    $indexes = ceil($count/$size);
    for ($i = 0; $i < $indexes; $i++) {
        echo '<sitemap>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].'sitemap.php?type=quotes&amp;page='.$i.'</loc>';
        echo '</sitemap>';
    }

    $count = (int) $db->get_var("select count(*) from users where user_level != 'disabled' and user_validated_date is not null");
    $indexes = ceil($count/$size);
    for ($i = 0; $i < $indexes; $i++) {
        echo '<sitemap>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].'sitemap.php?type=users&amp;page='.$i.'</loc>';
        echo '</sitemap>';
    }

    $count = (int) $db->get_var("select count(*) from encuestas join users on users.user_id = encuestas.encuesta_user_id and users.user_level != 'disabled'");
    $indexes = ceil($count/$size);
    for ($i = 0; $i < $indexes; $i++) {
        echo '<sitemap>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].'sitemap.php?type=polls&amp;page='.$i.'</loc>';
        echo '</sitemap>';
    }

    $count = (int) $db->get_var("select count(*) from polls_comments join users on users.user_id = polls_comments.autor and users.user_level != 'disabled'");
    $indexes = ceil($count/$size);
    for ($i = 0; $i < $indexes; $i++) {
        echo '<sitemap>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].'sitemap.php?type=poll_comments&amp;page='.$i.'</loc>';
        echo '</sitemap>';
    }
    echo '</sitemapindex>';
}

function do_statics() {
    global $globals;

    $urls = ['jonealas.php', 'cotillona.php', 'notitas/', 'nube.php', 'las_mejores.php', 'mas_comentadas.php',
             'mas_visitadas.php', 'aleatorios.php', 'corto.php', 'ultimos_comentarios.php', 'ayuda.php', 'encuestas.php',
             'mejores_comentarios.php', 'mejores_mafiosos.php', 'mapa.php', 'mejores_notitas.php', 'nube_de_webs.php'];

    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    // The index
       echo '<url>';
    echo '<loc>https://'.get_server_name().$globals['base_url'].'</loc>';
    echo '<priority>1.0</priority>';
    echo '</url>';
    // Secondary pages
    foreach ($urls as $url) {
        echo '<url>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].$url.'</loc>';
        echo '<priority>0.8</priority>';
        echo '</url>';
    }
    echo '</urlset>';
}

function do_published($page) {
    global $globals, $index_size, $db;
    $start = $page * $index_size;

    $urls = $db->get_col("SELECT SQL_NO_CACHE link_uri from links where link_status='published' order by link_date asc limit $start, $index_size");
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $url) {
        echo '<url>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].$globals['base_story_url'].$url.'</loc>';
        echo '</url>';
    }
    echo '</urlset>';
}

function do_posts($page) {
    global $globals, $index_size, $db;
    $start = $page * $index_size;

    $posts = $db->get_results("SELECT SQL_NO_CACHE users.user_login as user_login, posts.post_id as post_id from posts join users on users.user_id = posts.post_user_id and users.user_level != 'disabled'
                               where post_type = 'normal'
                               order by posts.post_id asc limit $start, $index_size");
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($posts as $post) {
        echo '<url>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].$globals['base_sneakme_url'].$post->user_login.'/'.$post->post_id.'</loc>';
        echo '</url>';
    }
    echo '</urlset>';
}

function do_comments($page) {
    global $globals, $index_size, $db;
    $start = $page * $index_size;

    $comments = $db->get_col("SELECT SQL_NO_CACHE comment_id from comments join users on users.user_id = comments.comment_user_id and users.user_level != 'disabled'
                              order by comments.comment_id asc limit $start, $index_size");
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($comments as $comment_id) {
        echo '<url>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].$globals['base_comment_url'].$comment_id.'</loc>';
        echo '</url>';
    }
    echo '</urlset>';
}

function do_quotes($page) {
    global $globals, $index_size, $db;
    $start = $page * $index_size;

    $quotes = $db->get_col("SELECT SQL_NO_CACHE id from cortos where activado = 1 order by id asc limit $start, $index_size");
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($quotes as $quote_id) {
        echo '<url>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].$globals['base_corto_url'].$quote_id.'</loc>';
        echo '</url>';
    }
    echo '</urlset>';
}

function do_users($page) {
    global $globals, $index_size, $db;
    $start = $page * $index_size;

    $users = $db->get_col("SELECT SQL_NO_CACHE user_login from users where user_level != 'disabled' and user_validated_date is not null
                           order by user_id asc limit $start, $index_size");
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($users as $user_login) {
        echo '<url>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].$globals['base_user_url'].$user_login.'</loc>';
        echo '</url>';
    }
    echo '</urlset>';
}

function do_polls($page) {
    global $globals, $index_size, $db;
    $start = $page * $index_size;

    $polls = $db->get_col("SELECT SQL_NO_CACHE encuesta_id from encuestas join users on users.user_id = encuestas.encuesta_user_id and users.user_level != 'disabled'
                           order by encuesta_id asc limit $start, $index_size");
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($polls as $poll_id) {
        echo '<url>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].$globals['base_encuesta_url'].$poll_id.'</loc>';
        echo '</url>';
    }
    echo '</urlset>';
}

function do_poll_comments($page) {
    global $globals, $index_size, $db;
    $start = $page * $index_size;

    $poll_comments = $db->get_col("SELECT SQL_NO_CACHE id from polls_comments join users on users.user_id = polls_comments.autor and users.user_level != 'disabled'
                                   order by id asc limit $start, $index_size");
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($poll_comments as $poll_comment_id) {
        echo '<url>';
        echo '<loc>https://'.get_server_name().$globals['base_url'].$globals['base_poll_comment_url'].$poll_comment_id.'</loc>';
        echo '</url>';
    }
    echo '</urlset>';
}
