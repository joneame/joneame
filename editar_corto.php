<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jon Arano (arano.jon@gmail.com)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'cortos.class.php');
include(mnminclude.'cortos.inc.php');

if ($_REQUEST['id']) {
    $id = intval($_REQUEST['id']);
}

$key = get_security_key();

$corto = new Corto;
$corto->id = $id;

if (!$corto->get_single() || !$corto->id ) {
 do_error(_('El corto no existe'.$corto->id), 403);
}

if ( $current_user->user_id != $corto->id_autor && $current_user->user_level != 'god' )
 do_error(_('no tienes permiso para entrar aquí'), 404);


if ( $corto->numero_ediciones() >= $globals['ediciones_max_cortos'] && $current_user->user_level != 'god')

 do_error(_('número de ediciones para este corto excedido'.$corto->numero_ediciones()), 403);



if ($_POST['process'] == 'editcomment') {

    if ($current_user->user_level == 'god') {
        save_corto();
        }

        if ($current_user->user_id == $corto->id_autor && $current_user->user_level != 'god'){
        guardar_copia($corto);
    }


} else if($_REQUEST['editar'] ) {

    do_header(_('Edición de corto'));
        print_edit_form($corto);

}


do_footer();

function get_security_key() {
        global $globals, $current_user, $site_key;
        return md5($globals['user_ip'].$current_user->user_id.$site_key);
}