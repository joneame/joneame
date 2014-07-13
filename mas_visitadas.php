<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
$globals['ads'] = true;

$page_size = 20;

$range_names  = array(_('48 horas'), _('una semana'), _('un mes'));
$range_values = array(2, 7, 30);

$current_page = min(4, get_current_page());
$offset=($current_page-1)*$page_size;

// Select a month and year
if (!empty($_GET['month']) && !empty($_GET['year']) && ($month = (int) $_GET['month']) > 0 && ($year = (int) $_GET['year'])) {
    $sql = "SELECT SQL_CACHE link_id, link_votes as votes FROM links WHERE YEAR(link_date) = $year AND MONTH(link_date) = $month AND link_status = 'published' ORDER BY link_votes DESC ";
    $time_link = "YEAR(link_date) = $year AND MONTH(link_date) = $month";
} else {
    // Select from a start date
    $from = intval($_GET['range']);
    if ($from >= count($range_values) || $from < 0 ) $from = 0;

    if ($range_values[$from] > 0) {
        // we use this to allow sql caching
        $from_time = '"'.date("Y-m-d H:i:00", time() - 86400 * $range_values[$from]).'"';
        if ($from > 0) {
            $status = "AND link_status = 'published'";
        } else {
            $status = "AND link_status in ('published', 'queued')";
        }
        $sql = "SELECT link_id, counter FROM links, link_clicks WHERE link_date > $from_time $status AND link_clicks.id = link_id ORDER BY counter DESC ";
        $time_link = "link_date > $from_time";
    }
}

do_header(_('Más visitadas') . ' | ' . _('Jonéame'));
$globals['tag_status'] = 'published';
do_tabs('main', 'topclicked');
print_period_tabs();

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();

if ($globals['mostrar_caja_publicadas']) do_best_stories();
do_best_comments();
do_vertical_tags('published');
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

$links = $db->get_results($sql);

if ($links) {
    $counter = 0;
    foreach($links as $dblink) {
       $link = Link::from_db($dblink->link_id);
       $link->print_summary();
    }
}
do_pages($rows, $page_size);
echo '</div>'."\n";

do_footer();

function print_period_tabs() {
    global $range_values, $range_names;

    if(!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) $current_range = 0;
    echo '<ul class="tabsub-shakeit">'."\n";
    for($i=0; $i<count($range_values) /*&& $range_values[$i] < 40 */; $i++) {
        if($i == $current_range)  {
            $active = ' class="tabsub-this"';
        } else {
            $active = "";
        }
        echo '<li'.$active.'><a href="mas_visitadas.php?range='.$i.'">' .$range_names[$i]. '</a></li>'."\n";
    }
    echo '</ul>'."\n";
}