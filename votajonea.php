<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include 'config.php';
include mnminclude.'html1.php';
include mnminclude.'jonevision.class.php';

do_header('Jonevisión | Jonéame');

if ($current_user->user_id == 0) do_error('Espacio dedicado para joneantes registrados.', 403);

if ($globals['now'] < 1292014800 || $globals['now'] > 1292198340){

        do_error('Votos cerrados', 404);

        }



echo '<div id="singlewrap">';
echo '<h2 class="faq-title">Votaciones. Sí, nosotos también pensabamos que esto no acababa nunca.</h2><br/>';
echo '<div class="faq" style="margin: 0 30px 75px 150px;">';


if ($_POST['fase'] == 2) save(); //fase guardar
else fase1();


echo '</div></div>';
do_footer();


function fase2(){
    global $db, $current_user;


    echo '<h3><img src="'.get_cover_pixel().'" style="margin-top: 8px;" class="icon info"/><strong> Estos son los votos seleccionados:</strong></h3></br><br/>'."\n";

    $canciones = $db->get_col("SELECT id FROM jonevision ORDER BY id");

    echo '<form action="votajonea.php" method="POST">';

    foreach ($canciones as $id){
        $cancion = new Jonevision;
        $cancion->id = $id;
        $cancion->read();
        $joneavision[$cancion->id] = $_POST[$cancion->id];


        if ($joneavision[$cancion->id] == 1) $puntos = ' punto';
        else $puntos = ' puntos';

        echo $joneavision[$cancion->id].$puntos.' a la canción <strong>'.$cancion->titulo.'</strong> de '.$cancion->login.'<br/>'."\n";

        echo '<input type="hidden" name="'.$cancion->id.'" value="'.$joneavision[$cancion->id] .'">'."\n";
    }


    echo '<br/><br/>Confirma que deseas emitir la votación seleccionada (no hay vuelta atrás)<br/>';
    echo '<input type="hidden" name="fase" value="2">'."\n";
    echo '<input type="hidden" name="user_id" value="'.$current_user->user_id.'">'."\n";
    echo '<input type="submit" value="Confirmar">';

}

function save(){
    global $db, $globals, $current_user;

    $canciones = $db->get_col("SELECT id FROM jonevision ORDER BY id");
    foreach ($canciones as $id) $joneavision[$id] = $_POST[$id];
    if (!errors($joneavision) ){

    $fecha = $globals['now'];
        foreach ($joneavision as $id => $valor) {
            $id = intval($id);
            $valor = intval($valor);
            $user = intval($current_user->user_id);
$db->query("INSERT INTO jonevision_votes (jonevision_id, user_id, valor, date) VALUES ($id, $user, $valor, $fecha)");
$db->query("UPDATE jonevision SET votos=votos+1, puntos=puntos+$valor WHERE id=$id");
        }

echo 'Tus votos han sido registrados. Espera unos días a ver los resultados.';
    }
    }

function fase1(){
    global $db;

    /* Le pasamos a la ID */
    $canciones = $db->get_col("SELECT id FROM jonevision ORDER BY id");
    foreach ($canciones as $id) $joneavision[$id] = $_POST[$id];


    if (!errors($joneavision) ){
        fase2();

    }
}

function errors($joneavision){
    global $current_user, $db, $globals;

    $error = false;

    if ($_POST['user_id'] != $current_user->user_id) {
    error ('usuario incorrecto');
    $error = true;
    }

    if ($globals['now'] < 1292014800 || $globals['now'] > 1292198340){
        error('Votos cerrados');
        $error = true;
        }

    if ($db->get_var("SELECT count(user_id) FROM jonevision_votes WHERE user_id=".intval($current_user->user_id)) > 1){
            error ('ya has votado en esta edición, atento a la siguiente');
            $error = true;
    }

    if (!$error){
    $canciones = $db->get_col("SELECT id FROM jonevision ORDER BY id");
    $valores = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '10', '12');
    $valores_introducidos = array();
    $ceros = 0;
    /* Y esto lo real */
    foreach ($canciones as $id){
        $cancion = new Jonevision;
        $cancion->id = $id;
        $cancion->read();

        /* Comprobamos que no ha votado su propia canción */
        if ($current_user->user_id == $cancion->user_id && $joneavision[$id] != 0){
            error ('has votado a una canción propia');
            $error = true;

        }
        if ($error) break; // a tomar por fly

        if (in_array($joneavision[$id], $valores_introducidos) && $joneavision[$id] > 0){
            error ('tienes valores duplicados');
            $error = true;
        }

        $valores_introducidos[$cancion->id]=$joneavision[$id];

        if ($error) break; // a tomar por fly

        if ($valores_introducidos[$cancion->id] == 0) $ceros ++;

        if (!in_array($joneavision[$id], $valores)){
            error ('tienes valores incorrectos');
            $error = true;
        }

        if ($error) break; // a tomar por fly

    }

    if ($error) return $error;

        if ($ceros > 6){
                error ('¿estás seguro de haber votado todas las canciones?');
                $error = true;
        }
    }

    return $error;
}



function error($text){
    echo '<img src="'.get_cover_pixel().'" style="margin-top: 8px;" class="icon info"/> '.$text.'</br><br/>';
    echo '<input class="button" type="button" onclick="window.history.go(-1)" value="'._('« madre de dios (atrás)').'"/>&nbsp;&nbsp;'."\n";


}

