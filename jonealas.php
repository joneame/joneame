<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');

header('Cache-Control: no-cache');

$globals['meta'] = false;

meta_get_current();

$page_size = 18;

$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = true;

$cat = check_integer('category');

if(isset($globals['meta'])){
switch ($globals['meta']) {

    case '_amigos':
        $title = 'Pendientes';
        $globals['noindex'] = true;
        $from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - $globals['time_enabled_votes']).'"';
        $from_where = "FROM links, friends WHERE link_date >  $from_time and link_status='queued' and link_sent=1 and friend_type='manual' and friend_from = $current_user->user_id and friend_to=link_author and friend_value > 0";
        $order_by = " ORDER BY link_date DESC ";
        $tab = 2;
        $globals['tag_status'] = 'queued';
        break;
    case '_populares':
        $title = 'Pendientes más votadas';
        $globals['noindex'] = true;
        $from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - 86400*2).'"';
        $from_where = "FROM links WHERE link_date > $from_time and link_status='queued' and link_sent=1 and link_karma > 10";
        $order_by = " ORDER BY link_karma DESC ";
        $tab = 3;
        $globals['tag_status'] = 'queued';
        break;
    case '_descartadas':
        $title = 'Descartadas';

        $globals['noindex'] = true;
        $globals['ads'] = false;
        if (!$current_user->admin)
        $from_time = 'link_date > "'.date("Y-m-d H:00:00", $globals['now'] - 86400*4).'" and ';
        else $from_time = ''; //todas las descartadas a admin
        $from_where = "FROM links WHERE $from_time link_status in ('discard', 'abuse', 'autodiscard', 'duplicated') and link_sent=1";
        $order_by = " ORDER BY link_date DESC ";
        $tab = 5;
        $globals['tag_status'] = 'discard';
        break;

    case '_enviandose':

    if ($current_user->user_level == 'god') {
            $title = 'Enviándose';
            $globals['noindex'] = true;
            $globals['ads'] = false;
            $from_where = "FROM links WHERE link_status='discard' and link_sent=0 ";
            $order_by = " ORDER BY link_date DESC ";
            $tab = 6;
            $globals['tag_status'] = 'discard';
            break;
    }

    else header("Location: jonealas.php?meta=_descartadas" );

    case '_all':
    default:
        $title = 'Pendientes';
        $globals['tag_status'] = 'queued';
        $order_by = " ORDER BY link_date DESC ";
        $from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - 864000 * 3).'"'; // 30 days

        if ($globals['meta_current'] > 0) {
            $from_where = "FROM links WHERE link_status='queued' and link_sent=1 and link_date > $from_time and link_category in (".$globals['meta_categories'].") ";
            $tab = false;
        } else {
            $from_where = "FROM links WHERE link_date > $from_time and link_status='queued' and link_sent=1";
            $tab = 1;
        }
        break;
}
}

do_header(_($title) . ' | Jonéame');
do_tabs("main","shakeit");
print_shakeit_tabs($tab);

echo '<br/>';
/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
echo '<br/>';
do_categories_new ('shakeit', $cat);
if ($globals['mostrar_caja_pendientes']) do_best_queued();
do_best_comments();
do_vertical_tags('queued');
echo '</div>';

/*** END SIDEBAR ***/

echo '<div id="newswrap">';

if ($cat) $from_where .= " AND link_category=$cat ";

$rows = -1;

$links = $db->get_col("SELECT link_id $from_where $order_by LIMIT $offset,$page_size");

if ($links) {
    foreach($links as $link_id) {
        $link = Link::from_db($link_id);

        if ($offset < 1000) {
            $link->print_summary('full', 16);
        } else {
            $link->print_summary('full');
        }
    }
}

do_pages($rows, $page_size);
echo '</div>';

do_footer();

function print_shakeit_tabs($option=-1) {
    global $globals, $current_user, $db, $cat;

echo '<ul class="barra redondo herramientas">';

// Do metas' list
$metas = $db->get_results("SELECT category_id, category_name, category_uri FROM categories WHERE category_parent = 0 ORDER BY category_id ASC");
if ($metas) {
    foreach ($metas as $meta) {
        if ($meta->category_id == $globals['meta_current']) {
            $active_meta = 'class="tabsub-this"';
            $globals['meta_current_name'] = $meta->category_name;
        } else {
            $active_meta = '';
            $toggle = '';
        }

    }
}

echo '<li '.$active[3].'><a href="'.$globals['base_url'].'jonealas.php?meta=_populares" class="icon popular">'._('populares'). '</a>'.$toggle_active[3].'</li>';

if ($current_user->user_id > 0) {
    echo '<li '.$active[2].'><a href="'.$globals['base_url'].'jonealas.php?meta=_amigos" class="icon heart">'._('amigos'). '</a>'.$toggle_active[2].'</li>';
}

if (!$globals['bot']) {
    echo '<li '.$active[5].'><a href="'.$globals['base_url'].'jonealas.php?meta=_descartadas" class="icon trash">'._('descartadas'). '</a>'.$toggle_active[5].'</li>';
}

if ($current_user->user_id > 0 && $current_user->user_level == 'god') {
    echo '<li '.$active[6].'><a href="'.$globals['base_url'].'jonealas.php?meta=_enviandose" class="icon story-queueing">'._('noticias que están siendo enviadas'). '</a>'.$toggle_active[5].'</li>';
}

if ($cat) $cat_rss = '&amp;category='.$cat;
else       $cat_rss = '';

// Print RSS teasers
switch ($option) {
    case 1: // All, queued
        echo '<li><a href="'.$globals['base_url'].'rss2.php?status=queued'.$cat_rss.'" rel="rss" class="icon rss">rss</a></li>';
        break;
    case 5: // Discard
        echo '<li><a href="'.$globals['base_url'].'rss2.php?status=discard'.$cat_rss.'" rel="rss" class="icon rss">rss</a></li>';
        break;
    case 7: // Personalised, queued
        echo '<li><a href="'.$globals['base_url'].'rss2.php?status=queued&amp;personal='.$current_user->user_id.'" rel="rss" class="icon rss">rss</a></li>';
        break;
    default:
        echo '<li><a href="'.$globals['base_url'].'rss2.php?status=queued&amp;meta='.$globals['meta_current'].$cat_rss.'" rel="rss" class="icon rss">rss</a></li>';
}

echo '<div style="clear: both;"></div>';
echo '</ul>';

}
