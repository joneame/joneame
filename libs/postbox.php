<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'mprivados.inc.php');
include(mnminclude.'user.php');



if (!empty($globals['base_mensajes_url']) && !empty($_SERVER['PATH_INFO'])) {
    $url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
    array_shift($url_args);
    $_REQUEST['login'] = clean_input_string($url_args[0]);
} else {
    $_REQUEST['login'] = clean_input_string($_REQUEST['login']);
    if (!empty($globals['base_mensajes_url']) && !empty($_REQUEST['login'])) {
        header('Location: '.get_mensajes_uri($_REQUEST['login']));
        die;
    }
}

if ($current_user->user_id == 0)  do_error(_('debes ser usuario registrado para enviar mensajes privados'), 403);

do_header('Privados | Jonéame');

$login = $db->escape($_REQUEST['login']);

if(empty($login)){

        header('Location: '.get_server_name().$globals['base_url']);
        die;

}

$user=new User();
$user->username = $login;

if(!$user->read()) {
     do_error(_('el usuario no existe o se ha dado de baja'), 404, false, false);
}


if ($current_user->user_id == $user->id)
    do_privados(1);
else {
    if (pribatuetako_sarbidea($current_user->user_id, $user->id))
        do_privados(0);
    }

    do_pages($rows, $page_size);

do_footer();
