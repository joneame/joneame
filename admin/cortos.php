<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net), Aritz <aritz@itxaropena.org>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'html1.php');
include(mnminclude.'cortos.class.php');
include(mnminclude.'cortos.inc.php');

$page_size = 25;

// We need it because we modify headers
ob_start();

if (!$globals['cortos_activados'])
    do_error('Cortos desactivados', 403, false);

$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = false;


// un arreglo temporal -aritz
if (!$_REQUEST['admin']) $_REQUEST['admin'] = "pendientes";


/*
 * Bakarrik god mailako erabiltzaileak sar daitezke. - aritz
 */
if (($current_user->user_level=="god" && $current_user->user_id > 0)) {
    do_header(_('Administración de cortos'));
    admin_tabs($_REQUEST["admin"]);
    echo '<div id="singlewrap">' . "\n";
    cargacortos($_REQUEST["admin"]);
    do_pages($rows, $page_size, false);

} else {
     do_error(_('Esta página es sólo para administradores'), 403);
}


echo "</div>";
echo "</div>"; // singlewrap
do_footer();


function admin_tabs($tab_selected = false) {
    global $globals;
    $active = ' class="current"';
    echo '<ul class="tabmain">' . "\n";

    $tabs=array('pendientes', 'aceptadas', 'configuracion');

    foreach($tabs as $tab) {
        if ($tab_selected == $tab) {
            echo '<li'.$active.'><a href="'.$globals['base_url'].'admin/cortos.php?admin='.$tab.'" title="'.$reload_text.'"><b>'._($tab).'</b></a></li>' . "\n";
        } else {
            echo '<li><a  href="'.$globals['base_url'].'admin/cortos.php?admin='.$tab.'"><b>'._($tab).'</b></a></li>' . "\n";
        }
    }

    echo '<li><a  href="'.$globals['base_url'].'admin/ediciones_cortos.php"><b>'._('ediciones').'</b></a></li>' . "\n";
    echo '</ul>' . "\n";
}

/*
 * Datu basetik balio bat guztiz ezabatzeko funtzioa - aritz
 */

function ezabatu_balioa($id) {
    global $db;

    if (!empty($id)) {

        $eskaera = $db->query("SELECT * FROM cortos WHERE id = '".intval($id)."' ORDER BY id");

        if ($eskaera) ezabatu_id_tablan($id, 'cortos');

    }
}

/*
 * Datu basean egoera berritu. - aritz
 */


function onartu_balioa($id) {
    global $db;

    if (!empty($id)) {
    $eskaera = $db->query("SELECT * FROM cortos WHERE id = '".intval($id)."' ORDER BY id");
    //existitu bada
    if ($eskaera) $db->query("UPDATE cortos SET activado = '1' WHERE (id = '".intval($id)."')"); // existiu bada aldatu
    } // empty
}

function onartu_guztiak() {
    global $db;

    if ($db->query("UPDATE cortos SET activado = '1' WHERE (activado = '0')"))
    return true;

    return false;
}

/*
 * Karga-funtzio orokorra
 */

function cargacortos($ban_type) {
    global $db, $globals, $offset, $page_size, $current_user, $user;

    $key = get_security_key();
    if (($current_user->user_level=="god") && ($_REQUEST["key"] == $key)) {
        if ($_REQUEST['ezabatu']) ezabatu_balioa($_REQUEST['ezabatu']);
        if ($_REQUEST['onartu']) onartu_balioa($_REQUEST['onartu']);
    }

    if (($_REQUEST['admin'] == 'pendientes' ||  $_REQUEST['admin'] == 'aceptadas')) {

        // ex container-wide
        echo '<div class="genericform" style="margin:0">';

        echo '<div style="float:right;">'."\n";

        echo '</div>';
        echo '<table style="font-size: 10pt">';
        echo '<tr><th width="30">'._('ID').'</th>';
        echo '<th>'._('corto').'</th>';
        echo '<th width="10%">'._('enviado por').'</th>';
        echo '<th width="60">'._('acciones').'</th></tr>';
    }



    if ($_POST['process'] == 'editcomment' && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id']))
    save_corto();

    // zer eskaturau?
    if ($_REQUEST['admin'] == "aceptadas") karga_onartutakoak($key);
    else if ($_REQUEST['admin'] == "configuracion") karga_konfigurazioa($key);
    else karga_onartugabeak($key);

    echo '</table>';

    //do_pages($rows, $page_size, false);
}


function karga_konfigurazioa($key) {
    global $db, $current_user, $_POST, $_GET;

    $smu = new Corto;

    if ($_POST['ada'] && $_POST['texto']) // cambiar corto?
     $smu->change_special_message(1, intval($_POST['ada']), $_POST['texto']);

    else if ($_GET['desac'])
     $smu->change_special_message(0, 0);


    echo '<div class="genericform"><fieldset><legend><span class="sign">configuración de cortos</span></legend><div align="center">';

    echo '<form action="cortos.php?admin=configuracion" method="post" id="bidali" name="bidali">';

    $id = $smu->special_message_active();

    if ($id) {
        $usr = new User;
        $usr->id = $id->value1;

        if ($usr->read()) $usuario = $usr->username;
        else           $usuario = "Unknown";

        echo "Cortos especiales activos para el usuario <strong>".$usuario."</strong> (<a href=\"cortos.php?admin=configuracion&desac=1\">Desactivar</a>)<br/><br/>";
    }

    else     echo "Cortos especiales desactivados<br/><br/>";

    // Usuarios
    echo "<label>Activar cortos para: </label>";
    echo '<select name="ada" id="ada">';

    // Menuda liada que viene aquí
    $usuarios = $db->get_results("SELECT * FROM users ORDER BY user_login ASC");

    foreach ($usuarios as $usuario)
        echo '<option value="'.$usuario->user_id.'">'.$usuario->user_login.' ('.$usuario->user_login_register.')</option>';

    echo '</select><br/><br/>';

    echo 'Introduce un mensaje especial (Incluye también el usuario) <br/><textarea style="width: 500px; height: 100px;" name="texto"></textarea>';
    echo '<input type="hidden" name="phase" value="1" />';
    echo '<br/><br/>';

    echo '<p><input class="button" type="submit" value="'._('¡chachi!').', '._('¡chachi!').', '._('¡chachi!').'" ';

    echo '/>&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span></p>';
    echo '</form>';
    echo '</fieldset>';
    echo '</div></div>';
}

/*
 * Onartu gabeko testuen karga.
 */

function karga_onartugabeak($key) {
    global $globals, $current_user, $db, $offset, $page_size, $rows;

if ($_REQUEST['onartuguztiak']) onartu_guztiak();


$rows = $db->get_var("SELECT count(*) FROM cortos WHERE activado=0");
$eskaera = $db->get_results("SELECT * FROM cortos WHERE activado = '0' ORDER BY id DESC LIMIT $offset,$page_size");

$cantidad = 0;

    foreach ($eskaera as $testu) {

        $user=new User();

        $user->id = $testu->por;

        // usuaxu topa
        if ($user->read())    $izena = $user->username;
        else $izena = "(eliminado)";

        echo '<tr>';
        echo '<td width="30">'.clean_text($testu->id).'</td>';
        echo '<td style="overflow: hidden;">'.clean_text($testu->texto).'</td>';

        if ($izena != '(eliminado)')
            echo '<td width="10%"><a href="'.get_user_uri($user->username).'">'.clean_text($izena).'</a></td>';
        else
            echo '<td>'.clean_text($izena).'</td>';

        echo '<td width="60">';

        // badazpadan
        if ($current_user->user_level=='god' ) {

            echo '<a href="'.$globals['base_url'].'admin/cortos.php?admin=pendientes&amp;onartu='.$testu->id.'&amp;key='.$key.'" title="'._('Aceptar').'"><img class="icon tick img-flotante" src="'.get_cover_pixel().'" alt="aceptar" title="aceptar el corto"/></a>&nbsp;';
            echo '<a href="'.$globals['base_url'].'editar_corto.php?id='.$testu->id.'&editar=1" title="'._('Editar corto').'"><img class="icon edit img-flotante" src="'.get_cover_pixel().'" alt="'.('Editar corto').'"/></a>&nbsp;';
            echo '<a href="'.$globals['base_url'].'admin/cortos.php?admin=pendientes&amp;ezabatu='.$testu->id.'&amp;key='.$key.'" title="'._('Eliminar').'"><img class="icon delete img-flotante" src="'.get_cover_pixel().'" alt="denegar" title="denegar el corto"/></a>';

        }

        echo '</td>';
        echo '</tr>';
        $cantidad++;
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
    echo '<td style="overflow: hidden;">Cantidad de cortos pendientes: '.$rows.' <a href="'.$globals['base_url'].'admin/cortos.php?admin=pendientes&amp;onartuguztiak=1&amp;key='.$key.'" title="'._('Aceptar').'">(Aceptar todos)</a></td>';

    if ($rows > 0)
    echo '<td style="overflow: hidden;"></td>';

    echo '<td></td>';
    echo '<td></td>';
    echo '</tr>';


}

function karga_onartutakoak($key) {
    global $globals, $current_user, $db, $offset, $page_size, $rows;

$rows = $db->get_var("SELECT count(*) FROM cortos WHERE activado=1");
$eskaera = $db->get_results("SELECT * FROM cortos WHERE activado = '1' ORDER BY id DESC LIMIT $offset,$page_size");

$cantidad = 0;

    foreach ($eskaera as $testu) {

        $cantidad++;
        $user=new User();

        $user->id = $testu->por;

        if ($user->read())    $izena = $user->username;
        else $izena = '(eliminado)';

        echo '<div id="corto-'.$testu->id.'">';
        echo '<tr>';
        echo '<td>'.clean_text($testu->id).'</td>';
        echo '<td style="overflow: hidden;">'.clean_text($testu->texto).'</td>';

        if ($izena != "(eliminado)")
            echo '<td><a href="'.get_user_uri($user->username).'">'.clean_text($izena).'</a></td>';
        else
            echo '<td>'.clean_text($izena).'</td>';

        echo '<td>';

        if ($current_user->user_level=="god") {
            echo '<a href="'.$globals['base_url'].'editar_corto.php?editar=1&id='.$testu->id.'" title="'._('Editar corto').'"><img class="icon edit img-flotante" src="'.get_cover_pixel().'" title="'._('Editar corto').'"/></a>&nbsp;';
            echo '<a href="'.$globals['base_url'].'admin/cortos.php?admin=aceptadas&amp;ezabatu='.$testu->id.'&amp;key='.$key.'" title="'._('Eliminar').'"><img class="icon delete img-flotante" src="'.get_cover_pixel().'" title="'._('Denegar corto').'"/></a>';
        }

        echo '</td>';
        echo '</tr>';
    }

echo '<tr>';
echo '<td>-</td>';
echo '<td style="overflow: hidden;"></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td>';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td></td>';
echo '<td style="overflow: hidden;">Cantidad de cortos: '.$rows.'</td>';
echo '<td></td>';
echo '<td></td>';
echo '<td>';
echo '</td>';
echo '</tr>';
echo '</div>';


}

/*
 * Balioaren ezabapena
 */
function ezabatu_id_tablan($id, $tabla) {
    global $db;

        //borrar  todas las propuestas de ediciones si existen
        $ediciones = $db->get_results("SELECT * FROM ediciones_corto WHERE corto_id=".intval($id));

        if ($ediciones){
            $sqldelpropuesta = "DELETE FROM ediciones_corto WHERE corto_id=".intval($id);
        $db->query($sqldelpropuesta);
        }

        $sqldel = "DELETE FROM $tabla WHERE id=".intval($id);

    if  ($db->query($sqldel)) return true;

    return false;
}


function get_security_key() {
    global $globals, $current_user, $site_key;
    return md5($globals['user_ip'].$current_user->user_id.$site_key);
}