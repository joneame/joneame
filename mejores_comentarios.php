<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
include(mnminclude.'comment.php');


$globals['ads'] = true;
$range_names  = array(_('24 horas'), _('48 horas'), _('una semana'), _('un mes'), _('un año'));
$range_values = array(1, 2, 7, 30, 365);
$page_size = 20;

do_header(_('mejores chorradas') . ' | Jonéame');
do_tabs('main', _('mejores comentarios'), true);
print_period_tabs();

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
//do_best_stories();
//do_best_comments();
do_vertical_tags('published');
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";


//echo '<div class="topheading"><h2>'._('comentarios más valorados 24 horas').'</h2></div>';


$last_link = 0;
$counter = 0;


// Select a month and year
if (!empty($_GET['month']) && !empty($_GET['year']) && ($month = (int) $_GET['month']) > 0 && ($year = (int) $_GET['year'])) {
    $sql = "SELECT comment_id, link_id FROM comments, links WHERE comment_date > '$min_date' and link_id=comment_link_id ORDER BY comment_karma desc, link_id asc limit 25";
    $time_link = "YEAR(link_date) = $year AND MONTH(link_date) = $month AND";
} else {
    // Select from a start date
    $from = intval($_GET['range']);
    if ($from >= count($range_values) || $from < 0 ) $from = 0;

    // Use memcache if available
    if ($globals['memcache_host'] && $current_page < 4) {
        $memcache_key = 'topcomments_'.$from.'_'.$current_page;
    }

    if ($range_values[$from] > 0) {
        // we use this to allow sql caching
            $min_date = date("Y-m-d H:00:00", time() - 86000 * $range_values[$from] ); //  about 24 hours
$comments = $db->get_results("SELECT comment_id, link_id FROM comments, links WHERE comment_date > '$min_date' and link_id=comment_link_id ORDER BY comment_karma desc, link_id asc limit 25");
}
    else {
        // Default
        $min_date = date("Y-m-d H:00:00", time() - 86000); //  about 24 hours
$comments = $db->get_results("SELECT comment_id, link_id FROM comments, links WHERE comment_date > '$min_date' and link_id=comment_link_id ORDER BY comment_karma desc, link_id asc limit 25");

    }



}
if ($comments) {
    echo '<div style="margin-top: 25px;">';
    foreach ($comments as $dbcomment) {

        $comment = Comment::from_db($dbcomment->comment_id);
        $link = Link::from_db($dbcomment->link_id);

        if ($last_link != $link->id) {
            echo '<h3 class="barra" style="margin-bottom: 0 !important;">';
            echo '<a href="'.$link->get_relative_permalink().'">'. $link->title. '</a>';
            echo '</h3>';
        }
            echo '<ol class="comments-list">';
        $comment->print_summary($link, 2000, false);
        if ($last_link != $link->id) {
            $last_link = $link->id;
            $counter++;
        }
        echo "</ol>\n";
    }
    echo '</div>';
}

echo '</div>';
do_footer();



function print_period_tabs() {
    global $range_values, $range_names, $month, $year;

    if(!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) $current_range = 0;
    echo '<ul class="tabsub-shakeit">'."\n";
    if ($month> 0 && $year > 0) {
        echo '<li class="tabsub-this"><a href="mejores_comentarios.php?month='.$month.'&amp;year='.$year.'">' ."$month-$year". '</a></li>'."\n";
        $current_range = -1;
    } elseif(!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) {
        $current_range = 0;
    }

    for($i=0; $i<count($range_values) /* && $range_values[$i] < 60 */; $i++) {
        if($i == $current_range)  {
            $active = ' class="tabsub-this"';
        } else {
            $active = "";
        }
        echo '<li'.$active.'><a href="mejores_comentarios.php?range='.$i.'">' .$range_names[$i]. '</a></li>'."\n";
    }
    echo '</ul>'."\n";
}