<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('../config.php');
include(mnminclude.'user.php');

header('Content-Type: text/plain; charset=UTF-8');

if(!($to=intval($_REQUEST['id']))) {
    error(_('falta el código de usuario'));
}

if(!($src = intval($_REQUEST['type']))) {
    error(_('falta el código de usuario'));
}

if ($src != $current_user->user_id) {
    error(_('usuario incorrecto'). " ($src, $current_user->user_id)");
}

/*if (!check_security_key($_REQUEST['key'])) {
    error(_('clave de control incorrecta'));
}
*/
echo friend_add_delete($src, $to);

function error($mess) {
    echo "ERROR: $mess\n";
    die;
}