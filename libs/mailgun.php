<?php
// The source code packaged with this file is Free Software, Copyright (C) 2019 by
// the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

define('MAILGUN_URL', 'https://api.mailgun.net/v3/');

function mg_send_mail($to, $subject, $message, $headers=null) {
    global $globals;

    $domain = get_server_name();
    if (!$domain || $domain == 'localhost') {
        $domain = $globals['mailgun_domain'];
    }

    $array_data = array(
        'from' => 'noreply@' . $domain,
        'to' => $to,
        'subject' => $subject,
        'text' => wordwrap($message, 70),
    );

    $session = curl_init(MAILGUN_URL . $globals['mailgun_domain'] . '/messages');
    curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($session, CURLOPT_USERPWD, 'api:' . $globals['mailgun_key']);
    curl_setopt($session, CURLOPT_POST, true);
    curl_setopt($session, CURLOPT_POSTFIELDS, $array_data);
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_ENCODING, 'UTF-8');
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($session);
    curl_close($session);
    $results = json_decode($response, true);
    return $results;
}
