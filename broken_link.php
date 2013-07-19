<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jon Arano (arano.jon@gmail.com)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');

$globals['ads'] = true;

do_header(_('Reportar historias erróneas') . ' | Jonéame');

do_tabs('main', 'report_links');

$page_size = 23;

$page = get_current_page();
$offset=($page-1)*$page_size;

print_sidebar();

echo '<div id="newswrap">'."\n";

$id = $db->escape($_REQUEST['id']);

if ((isset($id) && $id > 0) || $_POST['enviando'] > 0 ) {

	if ($_POST['enviando'] > 0) $id = $db->escape($_POST['enviando']);

	$link = Link::from_db($id);

	if ($link->read) {

	echo '<h2>Reportar link</h2><br/>';

	
	if ($_POST['enviando'] == 0) {
		echo '<div class="genericform" style="margin:10px; text-align: center">';
		echo '<form action="broken_link.php" method="post" id="enviar" name="enviar">';
		echo '<p>Estás a punto de reportar a la comunidad este enlace como inválido, es decir, que el mismo ha dejado de funcionar y no carga. La comunidad podrá encargarse de buscar un enlace sustituto para esta historia. ¿Estás seguro?</p>';
		echo '</div>';
		$link->print_summary();
		echo '<input type="hidden" name="enviando" value="'.$id.'">';

		echo '<div class="genericform" style="margin:10px; text-align: center">';
		echo '</br><p><input class="button" type="submit" value="'._('Sí, reportar historia').'"</p>';
		echo '</div>';

		echo '&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span></p>';
		echo '</form>';

	} else {
	
		echo '<div class="genericform" style="margin:10px; text-align: center">';
		
		if ($link->broken_link > 0) {

		echo '<p>Algún usuario ya ha reportado esta historia. Gracias de todas maneras.</p>';
		echo '</br><p><a href="broken_link.php"><input class="button" value="'._('Ver todas las historias').'"</a></p>';
		echo '</div>';
		
		} else {

		$link->broken_link = $current_user->user_id;
		$link->store();
		echo '<p>Historia reportada. Gracias.</p>';
		echo '</br><p><a href="broken_link.php"><input class="button" value="'._('Ver todas las historias').'"</a></p>';
		echo '</div>';
		}

	}
	

	} else {

	echo '<h2>El link no existe</h2><br/>'; 

	

	}

} else {


$links = $db->get_col("SELECT link_id FROM links WHERE link_broken_link > 0 ORDER BY link_sent_date ASC LIMIT $offset, $page_size");
$rows = $db->get_var("SELECT count(*) FROM links WHERE link_broken_link > 0");

$link = new Link;

echo '<h2>Historias con enlaces inválidos reportados</h2><br/>';

if ($links) {

	echo '<p>¿Nos ayudas a buscar enlaces correctos para estas historias? Déjanos un comentario en los comentarios con un enlace alternativo para que algún administrador pueda corregirlo.</p><br/>';

	foreach($links as $link_id) {
		$link = Link::from_db($link_id);
		$link->print_summary();
		
	}

	do_pages($rows, $page_size);
} else {
echo '<p>No hay historias inválidas reportadas. Utiliza el icono al final del titular si ves que algún enlace no funciona y la comunidad pueda buscar enlaces alternativos. ¡Gracias!</p><br/>';
}


}

echo '</div>'."\n";
do_footer();

function print_sidebar(){

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
echo '<br/>';
do_best_comments();
do_vertical_tags('published');
echo '</div>' . "\n";

/*** END SIDEBAR ***/

}