<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include('funciones.jopi.php');

array_push($globals['extra_js'], 'jquery-form.pack.js');
array_push($globals['extra_js'], 'posts01.js');

$page_size = 20;

$range_names  = array(_('24 horas'), _('48 horas'), _('una semana'), _('un mes'), _('un año'), _('todas'));
$range_values = array(1, 2, 7, 30, 365, 0);

$current_page = get_current_page();
$offset=($current_page-1)*$page_size;

// Select a month and year
if (!empty($_GET['month']) && !empty($_GET['year']) && ($month = (int) $_GET['month']) > 0 && ($year = (int) $_GET['year'])) {
    $sql = "SELECT SQL_CACHE post_id, post_votes as votes FROM posts WHERE YEAR(post_date) = $year AND MONTH(post_date) = $month ORDER BY post_karma DESC ";
    $time_link = "YEAR(post_date) = $year AND MONTH(post_date) = $month AND";
} else {
    // Select from a start date
    $from = intval($_GET['range']);
    if ($from >= count($range_values) || $from < 0 ) $from = 0;


    if ($range_values[$from] > 0) {
        // we use this to allow sql caching
        $from_time = '"'.date("Y-m-d H:i:00", time() - 86400 * $range_values[$from]).'"';
        $sql = "SELECT SQL_CACHE post_id, post_votes as votes FROM posts WHERE  post_date > $from_time  ORDER BY post_karma DESC ";
        $time_link = "post_date > $from_time AND";
    } else {
        // Default
        $sql = "SELECT SQL_CACHE post_id, post_votes as votes FROM posts WHERE post_id > 1 ORDER BY post_karma DESC ";
        $time_link = '';
    }
}


    $rows = $db->get_var("SELECT count(*) FROM posts WHERE $time_link post_id > 1");
    if ($rows == 0) {
        do_error(_('no hay notitas'), 404);
        die;
    }



$globals['ads'] = true;
do_header(_('Mejores notitas') . ' | Jonéame');
do_posts_tabs($tab_option, $user->username);
print_period_tabs(); //barra para elegir la fecha

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
echo '<br/>';
do_best_stories();
do_best_comments();

echo '</div>' . "\n";
/*** SIDEBAR ****/

echo '<div id="newswrap">'."\n";

echo '<div class="notes">';
$nota = new Post;
$notas = $db->get_results("$sql LIMIT $offset,$page_size");
$nota->print_post_teaser($rss_option);

echo '<ol class="notitas-list">';

foreach ($notas as $dbpost) {
    $nota = Post::from_db($dbpost->post_id);
    $nota->print_summary();
}

echo "</ol>\n";

echo '</div>'."\n";
do_pages($rows, $page_size);
echo '</div>'."\n";

do_footer();

function print_period_tabs() { //funcion de la barra
    global $range_values, $range_names, $month, $year;

    if(!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) $current_range = 0;
    echo '<ul class="tabsub-shakeit">'."\n";
    if ($month> 0 && $year > 0) {
        echo '<li class="tabsub-this"><a href="mejores_notitas.php?month='.$month.'&amp;year='.$year.'">' ."$month-$year". '</a></li>'."\n";
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
        echo '<li'.$active.'><a href="mejores_notitas.php?range='.$i.'">' .$range_names[$i]. '</a></li>'."\n";
    }
    echo '</ul>'."\n";
}
