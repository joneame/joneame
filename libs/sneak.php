<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

$sneak_version = 2;

$max_items = 30;

header('Connection: close');

function init_sneak() {
    global $globals, $db, $current_user;


    // Check number of users if it's annonymous
    if ($current_user->user_id == 0) {
        $nusers= $db->get_var("select count(*) from sneakers");
        if ($nusers > $globals['max_sneakers']) {
            header('Location: http://'.get_server_name().$globals['base_url'].'toomuch.html');
            die;
        }
    }

    // Check number of connections from the same IP addres
    // if it comes from Netvibes, allow more
    if (preg_match('/Netvibes Ajax/' , $_SERVER["HTTP_USER_AGENT"])) $max_conn = 50;
    else if ($current_user->user_id > 0) $max_conn = 3; else $max_conn = 5;


    $nusers= $db->get_var("select count(*) from sneakers where sneaker_id like '".$globals['user_ip']."-%'");

    if ($nusers > $max_conn) {
        header('Location: http://' . get_server_name().$globals['base_url'].'toomuch.html');
        die;
    }

    // Delete all connections from the same IP, just to avoid stupid cheating
    $db->query("delete from sneakers where sneaker_id like '".$globals['user_ip']."%'");


}