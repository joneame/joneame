<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Aritz <aritz@itxaropena.org> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'ban.php');
include(mnminclude.'xtea.class.php');
include(mnminclude.'mezuak.class.php');
include_once(mnminclude.'mprivados.inc.php');
include(mnminclude.'user.php');

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

$md5a =  md5($current_user->user_id.$current_user->user_date);

if($md5a != $_REQUEST['md5']){
    error(_('clave de control incorrecta'));
}

    // SQL injection FIX, thank you Jose <jose@eyeos.org>
    $id = intval($_REQUEST['mid']);
    $nondik = $db->escape($_REQUEST['mota']);

    $mezua = new Mezu;
    $mezua->id = $current_user->user_id;
    $mezua->jaso_mezua($id, $nondik);
    $mezu = $mezua->datuak;

    // Leemos los datos y más de lo mismo.
    if (!$mezua->error) {
        $datos_mensaje = $mezu[0];

        if ($_REQUEST['eg'] && !$datos_mensaje->mensaje_global)
            $avatar = $mezu[4];
        else if ($datos_mensaje->mensaje_global && $_REQUEST['eg']) {
            $avatar = get_admin_avatar(20);
            $izena = get_server_name();
            }
        else
            $avatar = $mezu[1];

        $usuario = $mezu[2];
        $id_usr = $mezu[3];

        if ($datos_mensaje->mensaje_global && $_REQUEST['eg'])
        $usuario = get_server_name();

                $datos_mensaje->data = date("d/m/Y H:i", $datos_mensaje->data); //convertimos la hora

        $ezabatu = '<a href="javascript:void(0)" title="'._('eliminar mensaje').'" onclick="ezabatu_mezua(\''.$id.'\', \''.$current_user->user_id.'\', \''.$md5a.'\', \''.$nondik.'\')"><img class="icon delete" src="'.get_cover_pixel().'" alt="'._('eliminar mensaje').'" title="'._('eliminar mensaje').'"/></a>';
        $irudiak = $ezabatu.'<a title="'._('responder mensaje').'" href="javascript:void(0)" onclick="document.erantzun'.$id.'.submit()"><img class="icon message-reply" src="'.get_cover_pixel().'" alt="'._('responder al mensaje').'" title="'._('responder al mensaje').'"/></a> </form>';

        if (pribatuetako_sarbidea($current_user->user_id, $id_usr))
            $asunt='    <a href="'.get_mensajes_uri($usuario).'"><img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$erab->id.');" onmouseout="tooltip.clear(event);" class="avatar" src="'.$avatar.'" alt="'.$izena.'" /></a>';
        else
            $asunt='    <img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$erab->id.');" onmouseout="tooltip.clear(event);" class="avatar" src="'.$avatar.'" alt="'.$izena.'" />';


        $por = ($nondik == 'inbox') ? 'por' : 'a';
        if (!$datos_mensaje->mensaje_global)
            $usr_image = '<a href="'.get_mensajes_uri($usuario).'"><img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$id_usr.');" onmouseout="tooltip.clear(event);" class="avatar" src="'.$avatar.'" width="40" height="40" alt="'.$usuario.'"/></a>';
        else {
            $usr_image = '<a href="'.get_user_uri(get_server_name()).'"><img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', 73);" onmouseout="tooltip.clear(event);" class="avatar" src="'.get_admin_avatar('40').'" width="40" height="40" alt="admin"/></a>';
            $usuario = 'joneame.net';
        }

        if ($_REQUEST['eg'])
            $bidali = '<span id="mezuak'.$datos_mensaje->id.'"><div class="mezuko_normala" href="javascript:void(0)"  onclick="irakurri_mezua(\''.$id.'\', \''.$current_user->user_id.'\', \''.$md5a.'\', \''.$nondik.'\', \'0\')" onmouseover="className=\'mezuko_aldatuta\';"  onmouseout="className=\'mezuko_normala\';">'.$asunt.'  '.$ezabatu.' ('.$datos_mensaje->data.' Europe/Madrid) '.str_replace("(GLOBAL)", '<img src="'.get_cover_pixel().'" alt="'._('mensaje global').'" title="'._('mensaje global').'" class="icon global"/>', clean_text($datos_mensaje->titulua)).'</div></span>';
        else
            $bidali .= '<div class="mezuko_normala2 fondo-caja redondo" onclick="irakurri_mezua(\''.$id.'\', \''.$current_user->user_id.'\', \''.$md5a.'\', \''.$nondik.'\', \'1\')">'.$usr_image.'<div class="mezuko_zein">'.$asunto.'enviado '.$por.' <strong><a href="'.get_user_uri($usuario).'">'.$usuario.'</a></strong> el <strong>'.$datos_mensaje->data.'</strong></div> '.put_smileys(do_post_video(clean_text($datos_mensaje->testua))).' <form method="post" name="erantzun'.$id.'" action="'.get_mensajes_uri($usuario).'?env_pst=1"><input name="titu_bidali" type="hidden" id="titu_bidali" value="'.clean_text($datos_mensaje->titulua).'" /></form><ul>'.$irudiak.'</ul></div>';
    } else return;


    echo text_to_html($bidali);

// Funtzio orokorrak
function error($mess) {
    $dict['error'] = $mess;
    echo json_encode($dict);
    die;
}