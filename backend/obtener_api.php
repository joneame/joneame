<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jon Arano <arano.jon@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include_once('../config.php');
header('Content-Type: text/html; charset=utf-8');

include_once(mnminclude.'user.php');
include_once(mnminclude.'post.php');


if (empty($_GET['id'])) {
    echo _('¿usuario?');
    die;
}

$id = intval($_GET['id']);

if ($id != $current_user->user_id && $current_user->user_level != 'god' ) {
    echo _('no tienes privilegios para leer esta información');
    die;
}

$user = new User;
$user->id=$id;
if (! $user->read()) die;
echo $user->give_api_key();