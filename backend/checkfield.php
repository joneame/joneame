<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('../config.php');

header('Content-Type: text/plain; charset=UTF-8');
$type=clean_input_string($_REQUEST['type']);
$name=clean_input_string($_GET['name']);

switch ($type) {
    case 'username':
        if (!check_username(trim($_GET['name']))) {
            echo _('caracteres inválidos o no comienzan con una letra');
            return;
        }
        if (strlen($name)<3) {
            echo _('nombre demasiado corto');
            return;
        }
        if (strlen($name)>24) {
            echo _('nombre demasiado largo');
            return;
        }
        if(!($current_user->user_id > 0 && $current_user->user_login == $name) && user_exists($name)) {
            echo _('el usuario ya existe');
            return;
        }
        echo "OK";
        break;
    case 'email':
        if (!check_email($name)) {
            echo _('dirección de correo no válida');
            return;
        }
        if(!($current_user->user_id > 0 && $current_user->user_email == $name) && email_exists($name)) {
            echo _('ya existe otro usuario con esa dirección de correo');
            return;
        }
        echo "OK";
        break;

    case 'ban_hostname':
        if (strlen($name)>64) {
            echo _('nombre demasiado largo');
            return;
        }
        require_once(mnminclude.'ban.php');

        if(($ban = check_ban($name, 'hostname'))) {
            echo $ban['comment'];
            return;
        }
        echo "OK";
        break;
    case 'ban_punished_hostname':
        if (strlen($name)>64) {
            echo _('nombre demasiado largo');
            return;
        }
        require_once(mnminclude.'ban.php');

        if(($ban = check_ban($name, 'punished_hostname'))) {
            echo $ban['comment'];
            return;
        }

        echo "OK";
        break;
    case 'ban_email':
        if (strlen($name)>64) {
            echo _('nombre demasiado largo');
            return;
        }
        if (check_email($name)) {
            echo _('dirección de correo no válida');
            return;
        }
        require_once(mnminclude.'ban.php');
        if(($ban = check_ban($name, 'email'))) {
            echo $ban['comment'];
            return;
        }
        echo "OK";
        break;
    case 'ban_ip':
        if (strlen($name)>64) {
            echo _('nombre demasiado largo');
            return;
        }
        require_once(mnminclude.'ban.php');
        if(($ban = check_ban($name, 'ip'))) {
            echo $ban['comment'];
            return;
        }
        echo "OK";
        break;
    case 'ban_proxy':
        if (strlen($name)>64) {
            echo _('nombre demasiado largo');
            return;
        }
        require_once(mnminclude.'ban.php');
        if(($ban = check_ban($name, 'proxy'))) {
            echo $ban['comment'];
            return;
        }
        echo "OK";
        break;
    case 'ban_words':
        if (strlen($name)>64) {
            echo _('nombre demasiado largo');
            return;
        }
        require_once(mnminclude.'ban.php');
        if(($ban = check_ban($name, 'words'))) {
            echo $ban['comment'];
            return;
        }
        echo "OK";
        break;

    default:
        echo "KO $type";
}