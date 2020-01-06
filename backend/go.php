<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('../config.php');

$id = intval($_GET['id']);
if ($id > 0) {

          $l = $db->get_row("select link_url as url, link_ip as ip from links where link_id = $id");
          if ($l) {

              if (! $globals['bot']
                 // && $globals['click_counter']
                  && $l->ip != $globals['user_ip']
                  && !id_visited($id)) {
                    $db->query("INSERT LOW_PRIORITY INTO link_clicks (id, counter) VALUES ($id,1) ON DUPLICATE KEY UPDATE counter=counter+1");
                }
        do_redirection($l->url);
                exit(0);
            }

}
require(mnminclude.'html1.php');
do_error(_('enlace inexistente'), 404);

function do_redirection($url) {
    header('HTTP/1.1 301 Moved');
    header('Location: ' . $url);
    header("Content-Length: 0");
    header("Connection: close");
    //flush();
}

function id_visited($id) {

    if (! isset($_COOKIE['visited']) || ! ($visited = preg_split('/x/', $_COOKIE['visited'], 0, PREG_SPLIT_NO_EMPTY)) ) {
        $visited = array();
        $found = false;
    } else {
        $found = array_search($id, $visited); //devuelve 0 o 1
        if (count($visited) > 10) {
            array_shift($visited);
        }
        if ($found !== false) {
            unset($visited[$found]);
        }
    }
    $visited[] = $id;
    $value = implode('x', $visited);
    setcookie('visited', $value);

    return $found !== false;
}