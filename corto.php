<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// David Martí <neikokz@gmail.com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'user.php');
include(mnminclude.'cortos.class.php');

if (!empty($globals['lounge_cortos'])) {
    header('Location: https://'.get_server_name().$globals['base_url'].$globals['lounge_cortos']);
    die;
}

if (!$globals['cortos_activados'])
    do_error('Cortos desactivados', 403, false);

$corto = new Corto;

if (!isset($_REQUEST['id']) && $globals['base_corto_url'] && $_SERVER['PATH_INFO']) {
    $url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
    array_shift($url_args); // The first element is always a "/"
    $corto->id = intval($url_args[0]);
} else {
    $url_args = preg_split('/\/+/', $_REQUEST['id']);
    $corto->id=intval($url_args[0]);
    if($corto->id > 0 && $globals['base_corto_url']) {
        // Redirect to the right URL if the link has a "semantic" uri
        header ('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $corto->get_relative_individual_permalink());
        die;
    }
}

if ($corto->id)
    $corto->get_single();
else {
    $corto->get_random();
    header('Location: ' . $corto->get_relative_individual_permalink());
    die;
}

if (!$corto->texto) do_error('el corto no existe', 404);

if ($corto->id_autor != $current_user->user_id && !$corto->activado) do_error('El corto no existe', 404);

do_header(_('Corto de '.$corto->por.': '.htmlspecialchars(text_to_summary($corto->texto)).' | Jonéame'));

$corto->do_corto();

do_footer();
