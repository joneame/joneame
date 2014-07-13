<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'encuestas.php');
include(mnminclude.'html1.php');

if (!empty($globals['lounge_encuestas'])) {
    header('Location: http://'.get_server_name().$globals['base_url'].$globals['lounge_encuestas']);
    die;
}

$page_size = 8;
$page = get_current_page();
$offset = ($page - 1) * $page_size;

if ($_GET['edit'] && $_POST) {

    if (!empty($_GET['edit'])) {
    $enc = new Encuesta;
    $enc->id = intval($_GET['edit']);
    $enc->read();

    if ($enc->autor == $current_user->user_id || $current_user->user_level == 'god') // se puede hacer el update
        {
        $enc->titulo = clean_text($_POST['titulo']);
        $enc->contenido = clean_text($_POST['contenido']);
        $enc->almacenar();
        }

    }

} else if ($_GET['delete']  && $current_user->user_level == 'god') {
    $db->query("DELETE FROM encuestas WHERE encuesta_id = '".intval($_GET['delete'])."'");
    $db->query("DELETE FROM encuestas_opts WHERE encid = '".intval($_GET['delete'])."'");
    $db->query("DELETE FROM encuestas_votes WHERE pollid = '".intval($_GET['delete'])."'");

    }

if (isset($_REQUEST['id'])) {
    $id = intval($_REQUEST['id']);
        $globals['is_permalink'] = true;
    $from_where = " FROM encuestas WHERE encuesta_id='$id' ";
    $sql = "SELECT encuesta_id $from_where LIMIT 1";
} elseif ($_REQUEST['fecha_fin']== 1) {
    //encuestas ordenadas por finalizacion (solo las abiertas)
    $from_where = " FROM encuestas WHERE encuesta_finish > now() ORDER BY encuesta_finish ASC";
    $sql = "SELECT encuesta_id $from_where";
} else {
    $from_where = " FROM encuestas ORDER BY encuesta_id DESC";
    $sql = "SELECT encuesta_id $from_where  LIMIT $offset,$page_size";

    if (isset($_REQUEST['unvoted']) && $current_user->user_id > 0)
        $no_votadas = 1;
    else    $no_votadas = 0;

}

$rows = $db->get_var("SELECT count(*) $from_where");

if ($rows == 0)
    do_error(_('no encontrado'), 404);

$encuestas = $db->get_col($sql);

$globals['extra_js'] = array('polls.js');

do_header("Encuestas | Jonéame");

echo '<div id="sidebar">';
do_last_questions ();
encuestas_mas_votadas();
echo '</div>' . "\n";

echo '<div id="newswrap"><div class="notes">';

encuestas_utils();

foreach($encuestas as $id) {
    $encuesta = new Encuesta;
    $encuesta->id = $id;
    $encuesta->read();
    if ($no_votadas == 0)

            $encuesta->print_encuesta();
    else if ($no_votadas == 1 && !$encuesta->userVoted())
        $encuesta->print_encuesta();
    $encuesta->destroyData();
}

echo '</div>';

do_pages($rows, $page_size);

echo '</div>';
do_footer();

// must be the same as in encuesta.php!!
function encuestas_utils(){
    global $globals, $current_user;

    echo '<div style="margin-top: 25px;">'; // :D
    echo '<ul class="barra redondo herramientas">';
    if ($current_user->user_id > 0)
    echo '<li><a href="'.$globals['base_url'].'nueva_encuesta.php" class="icon poll-new">enviar nueva encuesta</a></li>';
    if (!$_REQUEST['fecha_fin'])
    echo '<li><a href="'.$globals['base_url'].'encuestas.php?fecha_fin=1" class="icon permalink">ordenar por fecha de finalización</a></li>';
    if (!$_REQUEST['unvoted'])
    echo '<li><a href="'.$globals['base_url'].'encuestas.php?unvoted=1" class="icon permalink">no votadas</a></li>';

        echo '<li><a href="'.$globals['base_url'].'encuestas_rss.php" class="icon rss">encuestas por RSS</a></li>';
    echo '</ul></div><br/>';

}