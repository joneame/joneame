<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

require 'mailgun.php';

function send_mail($to, $subject, $message) {
    mg_send_mail($to, $subject, $message);
}

function send_recover_mail ($user) {
    global $site_key, $globals;

    require_once(mnminclude.'user.php');

    $now = time();
    $key = md5($user->id.$user->pass.$now.$site_key.get_server_name());
    $url = 'https://'.get_server_name().$globals['base_url'].'profile.php?login='.$user->username.'&t='.$now.'&k='.$key;

    $to      = $user->email;
    $subject = _('Recuperación de la contraseña de '). get_server_name();
    $message = $to .": para poder acceder sin la clave, conéctate a la siguiente dirección en menos de dos horas:\n".$url."\n\n";
    $message .= _('Pasado este tiempo puedes volver a solicitar acceso en: ') . "\nhttps://".get_server_name().$globals['base_url']."login.php?op=recover\n\n";
    $message .= _('Una vez en tu perfil, puedes cambiar la clave de acceso.');
    $message .= "\n\n". _('Este mensaje ha sido enviado a solicitud de la dirección: ') . $globals['user_ip'] . "\n\n";
    $message .= "-- " . _('la administración de joneame.net');

    mg_send_mail($to, $subject, $message);
    echo '<p><strong>' ._ ('Correo enviado, mira tu buzón, allí están las instrucciones. Mira también en la carpeta de spam.') . '</strong></p>';
    return true;
}

function send_verification_mail ($user) {
    global $site_key, $globals;

    require_once(mnminclude.'user.php');

    $now = time();
    $key = md5($user->id.$user->pass.$now.$site_key.get_server_name());
    $url = 'https://'.get_server_name().$globals['base_url'].'profile.php?login='.$user->username.'&t='.$now.'&k='.$key;

    $to      = $user->email;
    $subject = _('Verificación de tu cuenta en '). get_server_name();
    $message = $user->username .": para poder verificar tu cuenta, entra en el siguiente enlace durante el día de hoy:\n".$url."\n\n";
    $message .= _('Pasado este tiempo puedes volver a registrarte en: ') . "\nhttps://".get_server_name().$globals['base_url']."register.php\n\n";
    $message .= _('Una vez en tu perfil, puedes cambiar la clave de acceso.');
    $message .= "\n\n". _('Este mensaje ha sido enviado a solicitud de la dirección: ') . $globals['user_ip'] . "\n\n";
    $message .= "-- " . _('la administración de joneame.net');

    mg_send_mail($to, $subject, $message);
    echo '<p><strong>' ._ ('Correo enviado, mira tu buzón para verificar tu cuenta. Mira también en la carpeta de spam.') . '</strong></p>';
    return true;
}
