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


if ($_POST['process'] == 'show_box')
show_options();
else if ($_POST['process'] == 'save_settings')
save_options();


function show_options() {
    global $encuesta, $db, $current_user, $globals;

    echo '<input type="hidden" id="process-'.$encuesta->id.'" name="process-'.$encuesta->id.'" value="save_settings">';
    echo '<input type="hidden" id="cuenta'.$encuesta->id.'" name="cuenta" value="'. $encuesta->opciones['count'].'">';

    echo '<label>Titulo</label><br><input type="text" name="titulo" id="titulo" size="100" value="'.$encuesta->titulo.'" /><br/>'."\n";
    echo '<label>Descripción</label><br><input type="text" name="descripcion" id="descripcion" size="100" value="'.$encuesta->contenido.'" />'."\n";
    echo '<br/>';

    echo '<label>Opciones</label><br>'."\n";
    $cantidad = 0;
    for ($i=0; $i < $encuesta->opciones['count'];$i++) {

        $cuenta = $cuenta + 1;
        echo '<input type="hidden" name="opcion'.$cantidad.'" id="opcion'.$cantidad.'" value="'.$encuesta->opciones[$i]->poll->id.'">';

        echo 'Opción '.$cuenta.'<br/>';
        echo '<input type="text" size="100" name="valor'.$cantidad.'" id="valor'.$cantidad.'" value="'.$encuesta->opciones[$i]->poll->info.'"><br/>';

        $cantidad ++;
    }

    echo '<br/>';
    echo '<input class="button" type=button onclick="edit_poll('.$encuesta->id.')" value="'._('editar').'"/>';

    //echo '<a href="#" onclick="edit_poll('.$encuesta->id.')">Editar</a>';

}

function save_options() {
    global $encuesta, $db, $globals;

    echo '<input type="hidden" id="process-'.$encuesta->id.'" name="process" value="show_box">';

    echo '<input type="hidden" id="cuenta'.$encuesta->id.'" name="cuenta" value="'. $encuesta->opciones['count'].'">';

    $opciones = explode(',' , $_POST['opciones']);

    for ($i=0; $i < $encuesta->opciones['count'];$i++) {

        if (strlen($opciones[$i]) > 1 ) {

        $encuesta->info = $db->escape($opciones[$i]);
        $encuesta->option_id = $encuesta->opciones[$i]->poll->id;
        $encuesta->update_option();
        }
    }

    if (strlen($_POST['titulo']) > 12 && strlen($_POST['descripcion']) > 3){

        $encuesta->new_titulo = $db->escape($_POST['titulo']);
        $encuesta->new_description = $db->escape($_POST['descripcion']);
        $encuesta->update_info();
    }


    $encuesta->read();
    $encuesta->print_stats();
    echo ' Actualizado';

}
