<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');

meta_get_current();

$page_size = 23;

$page = get_current_page();
$offset=($page-1)*$page_size;
$globals['ads'] = true;

$cat= check_integer('category');

do_header(_('Jonéame') . '');
do_tabs('main','published');

if ($globals['meta_current'] > 0) {
    $from_where = "FROM links WHERE link_status='published' and link_category in (".$globals['meta_categories'].") ";

} else if (isset($globals['meta']) && $current_user->user_id > 0) { // Check authenticated

    switch ($globals['meta']) {
        case '_friends':
            $from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - 86400*4).'"';
            $from_where = "FROM links, friends WHERE link_date >  $from_time and link_status='published' and friend_type='manual' and friend_from = $current_user->user_id and friend_to=link_author and friend_value > 0";

        break;
        default:

            $from_where = "FROM links WHERE link_status='published' ";
    }

} else {

    $from_where = "FROM links WHERE link_status='published' ";
}


/*** SIDEBAR ****/
echo '<div id="sidebar">';
//do_info();
do_saved_searches();
do_banner_right();
echo '<br/>';
do_categories_new ('index', $cat);
if ($globals['mostrar_caja_pron'])  do_pron_stories();
if ($globals['mostrar_caja_publicadas']) do_best_stories();

if ($page < 2) {
    do_best_comments();
        do_last_comments();
    do_vertical_tags('published');
}

echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

/* teaser */
?>
<?php
if($cat) {
    $from_where .= " AND link_category=$cat ";
}

$order_by = " ORDER BY link_date DESC ";

$rows = $db->get_var("SELECT count(*) $from_where");

$links = $db->get_col("SELECT link_id $from_where $order_by LIMIT $offset,$page_size");

if ($links) {
    foreach($links as $link_id) {
        $link = Link::from_db($link_id);
        $link->print_summary();
    }
}

do_pages($rows, $page_size);

echo '</div>'."\n";

do_footer();