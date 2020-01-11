<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');

$globals['ads'] = true;

$range_names  = array(_('24 horas'), _('48 horas'), _('una semana'), _('un mes'), _('un año'), _('todas'));
$range_values = array(1, 2, 7, 30, 365, 0);

$page_size = 23;

$offset=(get_current_page()-1)*$page_size;

$from = intval($_GET['range']);

if ($from >= count($range_values) || $from < 0 ) $from = 0;


if ($range_values[$from] > 0) {
    // we use this to allow sql caching
    $from_time = '"'.date("Y-m-d H:00:00", time() - 86400 * $range_values[$from]).'"';
    $sql = "SELECT link_id FROM links WHERE  link_date > $from_time ORDER BY link_comments DESC ";
    $time_link = "link_date > FROM_UNIXTIME($from_time)";
} else {
    $sql = "SELECT link_id FROM links ORDER BY link_comments DESC ";
    $time_link = '';
}

if ($time_link) {
    $rows = min(100, $db->get_var("SELECT count(*) FROM links WHERE $time_link"));
} else {
    $rows = min(100, $db->get_var("SELECT count(*) FROM links"));
}

if ($rows == 0) {
     do_error(_('no hay noticias'), 404);
}

do_header(_('historias más comentadas') . ' | Jonéame');
do_tabs('main', _('más comentadas'), true);
print_period_tabs();

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_best_stories();
do_best_comments();
do_vertical_tags('published');
echo '</div>';
/*** END SIDEBAR ***/

echo '<div id="newswrap">';

$links = $db->get_results("$sql LIMIT $offset,$page_size");

if ($links) {
    foreach($links as $dblink) {
        $link = Link::from_db($dblink->link_id);
        $link->print_summary('short');
    }
}
do_pages($rows, $page_size);
echo '</div>';
do_footer();

function print_period_tabs() {
    global $range_values, $range_names;

    if(!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) $current_range = 0;
    echo '<ul class="tabsub-shakeit">';
    for($i=0; $i<count($range_values) /*&& $range_values[$i] < 40 */; $i++) {
        if($i == $current_range)  {
            $active = ' class="tabsub-this"';
        } else {
            $active = "";
        }
        echo '<li'.$active.'><a href="mas_comentadas.php?range='.$i.'">' .$range_names[$i]. '</a></li>';
    }
    echo '</ul>';
}