<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jon Arano (arano.jon@gmail.com)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

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
echo '</div>';
/*** END SIDEBAR ***/

echo '<div id="newswrap">';

$links = $db->get_col("SELECT link_id FROM links WHERE link_status != 'autodiscard' AND link_status != 'discard' AND link_status != 'abuse' ORDER BY RAND() LIMIT $page_size");
$link = new Link;
if ($links) {

    foreach($links as $link_id) {
        $link = Link::from_db($link_id);
        $link->print_summary();

    }
}
echo "\n"."\n";
echo '<br>';
echo '</div>';
echo '<ul class="barra redondo herramientas" style="margin: 0 0 12px 12px;">';
echo '<li><a href="aleatorios.php" class="icon reload">¿Quieres todavía más? ¡Haz clic aquí!</a></li>';
echo '</ul><br/>';
do_footer();