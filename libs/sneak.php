<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

$sneak_version = 3;
$max_items = 30;

function init_sneak() {
    global $globals, $db;

    $db->query("delete from sneakers where sneaker_id like '".$globals['user_ip']."%'");
}
