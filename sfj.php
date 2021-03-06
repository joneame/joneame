<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');

// Warning, it redirects to the content of the variable
if (!empty($globals['lounge_portada'])) {
    header('Location: https://'.get_server_name().$globals['base_url'].$globals['lounge_portada']);
    die;
}
$page_size = 27;
$page = get_current_page();
$offset=($page-1)*$page_size;
$globals['ads'] = true;

$cat= $db->escape($_REQUEST['category']);

do_header(_('Jonéame | Publicadas por la mafia') . '');
do_tabs('main','published');


$from_where = "FROM links WHERE link_status='published' AND link_category != 207 ";


/*** SIDEBAR ****/
echo '<div id="sidebar">';

do_saved_searches();
do_banner_right();
echo '<br/>';
do_categories_new ('index', $cat);

if ($globals['mostrar_caja_publicadas']) do_best_stories();

if ($page < 2) {
    do_best_comments();
        do_last_comments();
    do_vertical_tags('published');
}

echo '</div>';
/*** END SIDEBAR ***/

echo '<div id="newswrap">';


$order_by = " ORDER BY link_date DESC ";

$rows = $db->get_var("SELECT count(*) $from_where");

$links = $db->get_col("SELECT link_id $from_where $order_by LIMIT $offset,$page_size");
if ($links) {
    foreach($links as $link_id) {
        $link = Link::from_db($link_id);

        if (preg_match('/\bjonarano\b/i', $link->title) || preg_match('/\bjonarano\b/i', $link->content)) continue;

        $link->print_summary();
    }
}


do_pages($rows, $page_size);
echo '</div>';

do_footer();
