<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

define('FAV_YES', '<img src="'.get_cover_pixel().'" class="img-flotante icon heart-on" title="'._('en favoritos').'" alt="del"/>');
define('FAV_NO', '<img src="'.get_cover_pixel().'" class="img-flotante icon heart-off" title="'._('agregar a favoritos').'" alt="add"/>');
define('FAV_POST_YES', FAV_YES);
define('FAV_POST_NO', FAV_NO);

function favorite_icon($status, $type='link') {
    switch ($type) {
        case 'post':
            if ($status) return FAV_POST_YES;
            else return FAV_POST_NO;
            break;
    case 'comment':
            if ($status) return FAV_POST_YES;
            else return FAV_POST_NO;
            break;
        case 'link':
        default:
            if ($status) return FAV_YES;
            else return FAV_NO;
    }
}

function favorite_exists($user, $link, $type='link') {
        global $db;
        return intval($db->get_var("SELECT SQL_NO_CACHE count(*) FROM favorites WHERE favorite_user_id=$user and favorite_type='$type' and favorite_link_id=$link"));
}

function favorite_insert($user, $link, $type='link') {
    global $db;
    return $db->query("REPLACE INTO favorites (favorite_user_id, favorite_type, favorite_link_id) VALUES ($user, '$type', $link)");
}

function favorite_delete($user, $link, $type='link') {
        global $db;
        return $db->query("DELETE FROM favorites WHERE favorite_user_id=$user and favorite_type='$type' and favorite_link_id=$link");
}

function favorite_add_delete($user, $link, $type='link') {

    if(favorite_exists($user, $link, $type)) {
        favorite_delete($user, $link, $type);
        return favorite_icon(false, $type);
    } else {
        favorite_insert($user, $link, $type);
        return favorite_icon(true, $type);
    }
}

function favorite_teaser($user, $link, $type='link') {

    if (favorite_exists($user, $link, $type)) {
        return favorite_icon(true, $type);
    } else {
        return favorite_icon(false, $type);
    }
}


