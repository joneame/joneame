<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('../config.php');
include(mnminclude.'html1.php');
include(mnminclude.'cortos.class.php');
include(mnminclude.'cortos.inc.php');


if (!$globals['cortos_activados'])
    do_error('Cortos desactivados', 403, false);

$page_size = 20;

$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = false;


/*
 * Bakarrik god mailako erabiltzaileak sar daitezke. - aritz
 */
if (($current_user->user_level != "god" || $current_user->user_id == 0)) {

    do_error(_('Esta página es sólo para administradores'), 403);
}

    $key = get_security_key();

    if ($_REQUEST['id'])
    $id = intval($_REQUEST['id']);

    if ($id > 0) {

        $corto = new Corto;
        $corto->id = $id;
        if (!$corto->get_single() ) {
            do_error(_('El corto no existe'), 404);

        }

    }

    if ($_REQUEST['key'] == $key) {

        if ($_REQUEST['aceptar_propuesta']) aceptar_propuesta($key, $corto); //aceptar propuesta de edicion
        elseif ($_REQUEST['borrar_propuesta']) borrar_propuesta($key, $corto); //borrar propuesta de edicion
        elseif ($_REQUEST['ezabatu']) borrar_corto_y_propuesta($key, $corto); //borrar propuesta de edicion
        else die;
    }


    /*HEADER*/
    do_header(_('Administración de cortos'));

    admin_tabs();

    echo '<div id="singlewrap">' . "\n";

    ediciones_pendientes($key);
    echo '</table>';

    do_pages($rows, $page_size, false);

    echo "</div>";
    echo "</div>"; // singlewrap
    do_footer();

    function admin_tabs() {
        global $globals;
        $active = ' class="current"';

        echo '<ul class="tabmain">' . "\n";

        $tabs=array("pendientes", "aceptadas", "configuracion");

        foreach($tabs as $tab) {

            echo '<li><a  href="'.$globals['base_url'].'admin/cortos.php?admin='.$tab.'"><b>'._($tab).'</b></a></li>' . "\n";
        }
                echo '<li class="current"><a  href="'.$globals['base_url'].'admin/ediciones_cortos.php"><b>'._('ediciones').'</b></a></li>' . "\n";

        echo '</ul>' . "\n";
    }


    //ediciones pendientes de aprobar
    function ediciones_pendientes($key) {
    global $globals, $current_user, $db, $offset, $page_size;

        //maneja las ediciones propuestas por los usuarios
        $rows = $db->get_var("SELECT count(*) FROM edicion_corto");
        $eskaera = $db->get_results("SELECT * FROM edicion_corto ORDER BY autoid DESC LIMIT $offset,$page_size");
        //nuestro propio menu para ediciones
            echo '<div class="genericform" style="margin:0">';

            echo '<div style="float:right;">'."\n";

            echo '</div>';
            echo '<table style="font-size: 10pt">';
            echo '<tr><th width="30">'._('ID').'</th>';
            echo '<th>'._('corto anterior').'</th>';
            echo '<th>'._('propuesta').'</th>';
            echo '<th width="10%">'._('editado por').'</th>';
            echo '<th width="60">'._('acciones').'</th></tr>';
        //solo si tenemos ediciones pendientes
        if ($eskaera){
            foreach ($eskaera as $testu) {
                    $user=new User();

                    $user->id = $testu->autor;

                    // buscar el usuario
                if ($user->read())    $izena = $user->username; else $izena = "(eliminado)";
                        echo '<tr>';
                        echo '<td width="30">'.clean_text($testu->autoid).'</td>';
                        echo '<td style="overflow: hidden; "> <s>'.clean_text($testu->texto_copia).'</td> ';
            echo '<td style="overflow: hidden;">'.clean_text($testu->texto_propuesta).'</td>';
                        if ($izena != "(eliminado)")
                        echo '<td width="10%"><a href="'.get_user_uri($user->username).'">'.clean_text($izena).'</a></td>';
                        else
                        echo '<td>'.clean_text($izena).'</td>';
                        echo '<td width="60">';
                        // badazpadan
                        if ($current_user->user_level=="god" ) {
                            //lapiz para aceptar la edicion
                            echo '<a href="'.$globals['base_url'].'admin/ediciones_cortos.php?aceptar_propuesta=1&amp;id='.$testu->id_corto.'&amp;key='.$key.'" title="'._('Aceptar').'"><img class="icon tick img-flotante" src="'.get_cover_pixel().'" alt="aceptar" title="aceptar la edicion"/></a>&nbsp;';//maneja las ediciones propuestas por los usuarios

                            //borrar la propuesta
                echo '<a href="/admin/ediciones_cortos.php?borrar_propuesta=1&id='.$testu->id_corto.'&amp;key='.$key.'" title="'._('borrar propuesta').'"><img class="icon delete img-flotante" src="'.get_cover_pixel().'" alt="'.('borrar propuesta').'"/></a>&nbsp;';
                                                  //si piden para eliminar
                echo '<a href="'.$globals['base_url'].'admin/ediciones_cortos.php?ezabatu=1&id='.$testu->id_corto.'&amp;key='.$key.'" title="'._('Eliminar').'"><img class="icon trash img-flotante" src="'.get_cover_pixel().'" alt="denegar" title="borrar el corto"/></a>';

                        }
                        echo '</td>';
                        echo '</tr>';

                    }
        }

                        echo '<tr>';
                        echo '<td>-</td>';
                        echo '<td style="overflow: hidden;"></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<td></td>';
                        echo '<td style="overflow: hidden;">Cantidad de propuestas pendientes: '.$rows.'</td>';
                        if ($rows > 0)
                        echo '<td style="overflow: hidden;"></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '</tr>';


    }

function get_security_key() {
    global $globals, $current_user, $site_key;
    return md5($globals['user_ip'].$current_user->user_id.$site_key);
}