<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('../config.php');
include(mnminclude.'favorites.php');

header('Content-Type: text/plain; charset=UTF-8');

if(!($link=intval($_REQUEST['id']))) {
    error(_('falta el ID del enlace'). " $link");
}

if(!($user = intval($_REQUEST['type']))) {
    error(_('falta el código de usuario'));
}

if ($user != $current_user->user_id) {
    error(_('usuario incorrecto'));
}

/*if (! check_security_key($_REQUEST['key'])) {
    error(_('clave de control incorrecta'));
}*/


echo favorite_add_delete($user, $link);

function error($mess) {
    echo "ERROR: $mess\n";
    die;
}