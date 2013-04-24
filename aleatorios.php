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

$page_size = 18;

do_header(_('Aleatorios') . ' | Jonéame');
$globals['tag_status'] = 'published';
do_tabs('main', 'aleatorios');

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
echo '<br/>';
do_best_comments();
do_vertical_tags('published');
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

$links = $db->get_col("SELECT link_id FROM links WHERE link_status != 'autodiscard' AND link_status != 'discard' AND link_status != 'abuse' ORDER BY RAND() LIMIT $page_size");
$link = new Link;
if ($links) {

	foreach($links as $link_id) {
		$link = Link::from_db($link_id);
		$link->print_summary();
		
	}
}
echo "\n"."\n"."\n";
echo '<br>';
echo '</div>'."\n";
echo '<ul class="barra redondo herramientas" style="margin: 0 0 12px 12px;">';
echo '<li><a href="aleatorios.php" class="icon reload">¿Quieres todavía más? ¡Haz clic aquí!</a></li>';
echo '</ul><br/>';
do_footer();