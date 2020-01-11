<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include '../config.php';
include mnminclude.'html1.php';
//ini_set('display_errors', true);

if (!$current_user->admin) do_error('No tienes permisos para entrar aquí', 403);

do_header('Herramientas por IP');

if ($_POST['buscar'] == 1) buscar();
else formulario();

function buscar(){
global $db, $current_user;

$donde = $_POST['donde'];
$peticion = $db->escape($_POST['IP']);

switch ($donde) {

//case 'votos':

case 'votos_historia':


    $sql = "select user_login, vote_date, vote_value from users, votes where vote_type='links' and vote_link_id=$peticion and user_id=vote_user_id order by vote_date asc";

    $votos_historia = $db->get_results($sql);

        if (!$votos_historia) {

             echo "No se ha encontrado";
             die;

        }

    echo "Votos a la historia número ".$peticion.'<br/><br/>';

    foreach ($votos_historia as $voto) {

        echo "Login: ".$voto->user_login." Fecha: ".$voto->vote_date." Valor: ".$voto->vote_value."<br/>";

        }

die;

case 'clones':

    if ($current_user->user_level != 'god'){

          echo 'Mal, muy mal';
         die;

    }

$clones = $db->get_results("select distinct user_id, user_login, user_avatar from clones, users where clon_from = $peticion and user_id = clon_to and clon_date > date_sub(now(), interval 30 day) order by clon_date desc");

if (! $clones) {
        echo _('no hay clones para este usuario');
        die;
}

foreach ($clones as  $clon) {
        $highlight = '';
        $details = '';
        $ips = $db->get_col("select distinct clon_ip from clones where clon_from =$peticion and clon_to = $clon->user_id $from");
        foreach ($ips as $ip) {
                $details .= preg_replace('/\.[0-9]+$/', '', $ip).', ';
                if (preg_match('/COOK:/', $ip)) {
                        $highlight = 'style="color:#ff0000"';
                }
        }
        echo '<div class="item" '.$highlight.'>';
        echo '<a '.$highlight.' href="'.get_user_uri($clon->user_login).'" title="'.$details.'" target="_blank">';
        echo '<img src="'.get_avatar_url($clon->user_id, $clon->user_avatar, 20).'" width="20" height="20" alt=""/>';
        echo $clon->user_login.'</a>';
        echo '</div>';
}



die;

case 'ip_votos_usuario':

    require_once mnminclude.'user.php';
    require_once mnminclude.'link.php';

    if ($current_user->user_level != 'god'){

          echo 'Mal, muy mal';
         die;
    }

    $usuario = new User;
    $usuario->id = $peticion;

        if (!$usuario->read() ) {

        echo "usuario no encontrado";
        die;

        }

    echo $usuario->username. " Email: ".$usuario->email."<br/>";

    $sql = $db->get_results("SELECT link_id, vote_value, vote_date FROM links, votes WHERE vote_type='links' and vote_user_id=$usuario->id AND vote_link_id=link_id ORDER BY vote_date DESC LIMIT 500");

    if (!$sql) {

        echo "No hay votos";
        die;

        }

    foreach ($sql as $voto) {

        $link = new Link;
        $link->id = $voto->link_id;
        $link->read();
        echo "Voto a ".$link->title. " ->".$link->get_permalink()." Valor: ".$voto->vote_value." el día ".$voto->vote_date."<br/>";
        }
        die;

case 'buscar_ip':

    if ($current_user->user_level != 'god'){

          echo 'Mal, muy mal';
         die;
    }


default:

     echo 'Wrong hole';
     die;



}
}

function formulario(){
global $current_user;

echo '<form action=herramientas.php method=POST>';
echo '<input type=text name=IP>';
echo '&nbsp;&nbsp;&nbsp;&nbsp;';
echo '<select name="donde">';
echo '<option value="votos_historia">votos de una historia</option>';

         if ($current_user->user_level == 'god') {

        echo '<option value="clones">clones de una IP</option>';
        echo '<option value="ip_votos_usuario">IP de los votos de un usuario</option>';
        //echo '<option value="buscar_ip">buscar una IP</option>';

    }


echo '<input type="hidden" name="buscar" value="1" />';
echo '<input class="button" type="submit" value="'._('buscar').'" />';

}