<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Aritz <aritz@itxaropena.org> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('../config.php');
include(mnminclude.'xtea.class.php');
include(mnminclude.'mezuak.class.php');
include_once(mnminclude.'ban.php');
include_once(mnminclude.'user.php');

header('Content-Type: text/plain; charset=UTF-8');

// Egiaztapenak
if(check_ban_proxy()) {
    error(_('IP no permitida'));
}

if(empty($_REQUEST['id']) or $_REQUEST['id'] == '0' ) {
    error(_('Falta el código de usuario'));
}

if (empty($_REQUEST['md5'])) {
    error(_('Falta la clave de control'));
}
if (empty($_REQUEST['mid']) or empty($_REQUEST['mota'])) {
    error(_('Faltan datos sobre el mensaje'));
}

$user_id = intval($_REQUEST['id']);

if ($current_user->user_id != $user_id){
    error(_('usuario incorrecto'));
}

$md5a =  md5($current_user->user_id.$current_user->user_date);

if($md5a != $_REQUEST['md5']){
    error(_('clave de control incorrecta'));
}

$id = intval($_REQUEST['mid']);
$nondik = $db->escape($_REQUEST['mota']);

if ($nondik == 'inbox') $posta = 'recipient';
else if ($nondik == 'outbox') $posta = 'sender';

$mezua = new Mezu;
$mezua->id = $current_user->user_id;
$mezua->jaso_mezua($id, $nondik);

// Borrar mensaje
if (!$mezua->error) {
    ezabatu_id_tablan($id, 'mezuak', $posta);
}

// Funtzio orokorrak
function error($mess) {
    $dict['error'] = $mess;
    echo json_encode($dict);
    die;
}

function ezabatu_id_tablan($id, $tabla, $posta) {
            global $db;
            $id = $db->escape($id);
            $tabla = $db->escape($tabla);

            $sqldel = "DELETE FROM $tabla WHERE id='$id' AND posta='$posta'"; // Por seacaso revisamos el buzón
            if($db->query($sqldel)) return true;
            return false;
}

?>
