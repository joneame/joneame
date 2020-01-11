<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jon Arano <arano.jon@gmail.com>, Aritz Olea <aritz@itxaropena.org>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'jonevision.class.php');

do_header('Jonevisión | Jonéame');

//do_joneinfo();
//echo 'Los ganadores aquí, en notitas, y en la cotillona a partir de las 21.00 y hasta las 22.00<br/>';
$votado = $db->get_var("SELECT count(user_id) FROM jonevision_votes WHERE user_id=".intval($current_user->user_id));

echo '<div class="genericform">'; // gotta hate u!

$canciones = $db->get_results("SELECT id FROM jonevision ORDER BY puntos DESC LIMIT 16");

/*

    echo '<div id="singlewrap">';
    echo '<h2 class="faq-title">Votaciones</h2><br/>';
    echo '<div class="faq" style="margin: 0 30px 75px 150px;">';

    echo '<h3><img src="'.get_cover_pixel().'" style="margin-top: 8px;" class="icon info"/><strong> Cosas que debes saber antes de votar</strong></h3></br>';

    echo '<p>-Utilizaremos el clásico sistema de eurovisión. 12 al que más y 0 al que menos.</p><p>-Hay 14 canciones osea que a 6 canciones debes darle 0 puntos.</p>-Recuerda que no podrás cambiar tu voto, osea que piénsatelo bien.</p><p>-Si eres participante NO puedes votarte a ti mismo, pon 0 puntos a las tuyas y reparte los puntos entre los demás.';

    echo '<h3><img src="'.get_cover_pixel().'" style="margin-top: 8px;" class="icon info"/><strong> El premio</strong></h3></br>';
    echo '<p>-El usuario ganador (en caso de ser anónimo, será para el primer usuario "conocido") se llevará <strong>gratis</strong> una camiseta de Jonéame (firmada por Jonarano)</p>';

    echo '<h3><img src="'.get_cover_pixel().'" style="margin-top: 8px;" class="icon info"/><strong> Cómo votar</strong></h3></br>';

    echo '<p>-Selecciona la puntuación deseada para cada canción (6 DEBEN ser 0). Asegúrate que no repites puntuaciones, sino tendrás que volver a empezar. Una vez has terminado, pulsa el botón "¡Trampolín a la fama!"</p>';

    echo '<h3><img src="'.get_cover_pixel().'" style="margin-top: 8px;" class="icon info"/><strong> Dudas</strong></h3></br>';
    echo '<p>-Si todavía tienes dudas ponte en contacto con nosotros antes de votar en la <a href="cotillona.php">cotillona</a> o lee <a href="http://blog.joneame.net/2010/12/10/jonevision-votacione/"> nuestro blog</a> </p>';

    echo '<br/>';

    echo '<h3><img src="'.get_cover_pixel().'" style="margin-top: 8px;" class="icon info"/><strong> ¿Y las canciones?</strong></h3></br>';
    echo '<p>-Echa un vistazo a las canciones si estás indeciso o no las recuerdas.</p>';

}
*/
$puesto = 1;
    foreach ($canciones as $jonevision){
        $cancion = new Jonevision;
        $cancion->id = $jonevision->id;
        $cancion->read();
        echo 'Puesto '.$puesto.'<br/>';
        $puesto = $puesto +1;
        $cancion->print_jonevision();
        echo "\n";
    }

/*
if ($current_user->user_id == 1){

    echo '<h3><img src="'.get_cover_pixel().'" style="margin-top: 8px;" class="icon info"/><strong> Lo importante</strong></h3></br>';
    echo '<p>-Aquí tienes tu libreta. Piénsatelo bien y asegúrate que todo sea correcto (acuérdate que no puedes cambiar el voto).</p>';
    echo '<h3><img src="'.get_cover_pixel().'" style="margin-top: 8px;" class="icon info"/><strong> ¡Que gane el mejor!</strong></h3></br>';

    $canciones = $db->get_results("SELECT id FROM jonevision ORDER BY id");
    echo '<form action="votajonea.php" method="POST">';
        foreach ($canciones as $jonevision){
            $cancion = new Jonevision;
            $cancion->id = $jonevision->id;
            $cancion->read();
    echo '<p><select name="'.$cancion->id.'"';

        $n = array('-1', '0', '1', '2', '3', '4', '5', '6', '7', '8', '10', '12');

        foreach ($n as $m){

        if ($m == 0) $selected = "selected='selected'";
            else $selected = '';
        if ($m == 1) $puntos = "punto";
        else $puntos = "puntos";
    echo '<option value="'.$m.'" '.$selected.'>'.$m.' '.$puntos.'</option>';

        }

    echo '</select> ';
    echo '<strong>'.$cancion->titulo.'</strong> por '.$cancion->login.'</p>';
    echo "\n";
        }

    echo '<br/><br/><input type="submit" value="¡Trampolín a la fama!"><br/>';
    echo '<input type="hidden" name="user_id" value="'.$current_user->user_id.'">';
    echo '</div></div>';

}*/

echo '</div>';

echo "</ol>\n";

do_footer();