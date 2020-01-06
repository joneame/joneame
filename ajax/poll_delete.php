<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jon Arano <arano.jon@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include_once('../config.php');
include_once(mnminclude.'encuestas.php');

$id = intval($_POST['poll_id']);
$encuesta = new Encuesta;
$encuesta->id = $id;
$encuesta->read();

if (!$encuesta->read)
    die(_('la encuesta no existe'));


if ($current_user->user_level != 'god')
    die(_('no puedes acceder a este apartado'));

$encuesta->delete_poll();
