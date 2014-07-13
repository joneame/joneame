<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (! defined('mnmpath')) {
    include_once('../config.php');
    header('Content-Type: text/html; charset=utf-8');
}

include_once(mnminclude.'html1.php');

if (!empty($_GET['id'])) {
    if (preg_match('/(.+)-(\d+)/u', $_GET['id'], $matches) > 0) {
        $id = 0;
        $user_id = explode(',', $matches[1]);
        if (count($user_id) == 2) {
            $user = $db->escape($user_id[0]);
            $post_id = $user_id[1];
        } else {
            $user = $db->escape($matches[1]);
            $date = $matches[2];
            $post_id = 0;
        }

    /* Nos pide una nota, verificamos que existe */
        if ($post_id) {
            $id = (int) $db->get_var("select post_id from posts where post_id = $post_id order by post_date desc limit 1");
        }

        // No existe o no nos han dado ID, buscamos por fecha y login
        if (! $id) {
            $id = (int) $db->get_var("select post_id from posts, users where user_login = '$user' and post_user_id = user_id and post_date < FROM_UNIXTIME($date) order by post_date desc limit 1");
        }

    /* Existe usuario pero no tiene notas */
    if ($user && $id == 0) {
        header('Location:  http://'.get_server_name().get_user_uri($user));
        die;
    }

    } else {
        $id = intval($_GET['id']);
    }

} else {
    die;
}

/* Buscamos el login */
$login = $db->get_col("SELECT user_login from users,posts where post_id=$id and post_user_id=user_id");

/* No hay ni nota ni usuario */
if(!$login && $id ==0 ) {
    do_error(_('<strong>Error: </strong>' . _('usuario o nota no encontrada')), 403);
    die;
}

header('Location:  http://'.get_server_name().post_get_base_url($login) . "/$id");