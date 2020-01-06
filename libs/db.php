<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include_once(mnminclude.'rgdb.php');

global $globals;
$db = new RGDB($globals['db_user'], $globals['db_password'], $globals['db_name'], $globals['db_server']);

// we now do "lazy connection".
$db->persistent = $globals['mysql_persistent'];
$db->master_persistent = $globals['mysql_master_persistent'];

// For production servers
$db->hide_errors();

// Cache expiry
$db->cache_timeout = 2; // Note: this is hours

$db->cache_dir = $globals['mysql_cache_dir'];

$db->use_disk_cache = true;

// By wrapping up queries you can ensure that the default
// is NOT to cache unless specified
$db->cache_queries = false;
