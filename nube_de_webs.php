<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');

$globals['ads'] = true;

$min_pts = 10;
$max_pts = 44;
$limit = 200;
$line_height = $max_pts * 0.75;

$range_names  = array(_('24 horas'), _('48 horas'), _('una semana'), _('un mes'), _('un año'), _('todas'));
$range_values = array(1, 2, 7, 30, 365, 0);



if(($from = check_integer('range')) >= 0 && $from < count($range_values) && $range_values[$from] > 0 ) {
    // we use this to allow sql caching
    $from_time = '"'.date("Y-m-d H:00:00", time() - 86400 * $range_values[$from]).'"';
    $from_where = "FROM blogs, links WHERE  link_date > $from_time and link_status = 'published' and link_lang = '$dblang' and link_blog = blog_id";
} else {
    $from_where = "FROM blogs, links WHERE link_status = 'published' and link_lang = '$dblang' and link_blog = blog_id";
}
$from_where .= " GROUP BY blog_id";

$max = max($db->get_var("select count(*) as count $from_where order by count desc limit 1"), 2);
//echo "MAX= $max\n";

$coef = ($max_pts - $min_pts)/($max-1);


do_header(_('nube de sitios web') . ' | Jonéame');
do_tabs("main", _('+ webs'), true);
print_period_tabs();

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_vertical_tags('published');
echo '</div>';
/*** END SIDEBAR ***/

echo '<div id="newswrap">';

echo '<div class="topheading"><h2>Los sitios más enlazados</h2></div>';
echo '<div style="margin: 20px 0 20px 0; line-height: '.$line_height.'pt; margin-left: 50px;">';
$res = $db->get_results("select blog_url, count(*) as count $from_where order by count desc limit $limit");
if ($res) {
    foreach ($res as $item) {
        $blogs[$item->blog_url] = $item->count;
    }
    ksort($blogs);
    foreach ($blogs as $url => $count) {
        $text = preg_replace('/http:\/\//', '', $url);
        $text = preg_replace('/^www\./', '', $text);
        $text = preg_replace('/\/$/', '', $text);
        $size = intval($min_pts + ($count-1)*$coef);
        echo '<span style="font-size: '.$size.'pt"><a href="'.$url.'">'.$text.'</a></span>&nbsp;&nbsp; ';
    }
}

echo '</div>';
echo '</div>';
do_footer();


function print_period_tabs() {
    global $globals, $range_values, $range_names;

    if(!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) $current_range = 0;
    echo '<ul class="tabsub-shakeit">';
    for($i=0; $i<count($range_values)-1; $i++) {
        if($i == $current_range)  {
            $active = ' class="tabsub-this"';
        } else {
            $active = "";
        }
        echo '<li'.$active.'><a href="nube_de_webs.php?range='.$i.'">' .$range_names[$i]. '</a></li>';
    }
    echo '</ul>';
}