<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');

$globals['ads'] = true;

if ($_GET['rango'] == 'todas') {
    $sql = "SELECT link_id  FROM links WHERE link_negatives > 0  and link_karma < 0 ORDER BY link_karma ASC LIMIT 50 ";
} else {
    $sql = "SELECT link_id  FROM links WHERE  link_date > date_sub(now(), interval 1 month) and link_negatives > 0  and link_karma < 0 ORDER BY link_karma ASC LIMIT 50 ";
}

do_header(_('las peores :-)'));

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_best_stories();
do_best_comments();
do_vertical_tags('published');
echo '</div>';
/*** END SIDEBAR ***/

echo '<div id="newswrap">';

echo '<div class="topheading"><h2>'._('¿noticias?').' :-) </h2></div>';


$links = $db->get_results($sql);
if ($links) {
    foreach($links as $dblink) {
        $link = Link::from_db($dblink->link_id);
        $link->print_summary();
    }
}
echo '</div>';
do_footer();
