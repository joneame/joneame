<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include 'config.php';
include mnminclude.'html1.php';

if (!$current_user->admin) do_error('cotilla! bu! bu! fuera!', 403);

do_header('info premios joneame');

$encuestas_joneame = $db->get_results("SELECT * FROM encuestas where encuesta_id >= 320 AND encuesta_id < 341");

foreach ($encuestas_joneame as $encuesta) {

$opciones = $db->get_results("SELECT info, id FROM encuestas_opts WHERE encid=$encuesta->encuesta_id");


echo '<h1>'.$encuesta->encuesta_title.'</h1><br/>';
$numero = 1;
foreach ($opciones as $opcion) {

$votos = $db->get_results("SELECT *  FROM encuestas_votes, users WHERE  optid=$opcion->id AND pollid=$encuesta->encuesta_id AND user_id=uid");

echo '<strong>Opción numero'.$numero.': '.$opcion->info.' </strong><br/><br/>';
$numero ++;

    foreach ($votos as $voto){
    echo 'Votado por: '.$voto->user_login.'<br/>';

        }
    echo '<br/>';
}
echo '<br/><br/>';
}

do_footer();