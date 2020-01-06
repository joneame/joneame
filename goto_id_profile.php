<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');

$id = intval($_REQUEST['id']);

$login = $db->get_row("SELECT user_login FROM users WHERE user_id=$id");

header("Location: ".get_user_uri($login->user_login));