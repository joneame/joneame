<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'busquedas.php');

header('Content-Type: text/plain; charset=UTF-8');

if(!($texto= clean_text($_REQUEST['id']))) {
        error(_('falta el texto de la busqueda'). " {$_REQUEST['id']}");
}

if(!($user = intval($_REQUEST['type']))) {
        error(_('falta el código de usuario'));
}

if ($user != $current_user->user_id) {
        error(_('usuario incorrecto'));
}



echo anadir($user, $texto);

function error($mess) {
        echo "ERROR: $mess\n";
        die;
}