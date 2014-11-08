<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function do_vertical_tags($what=false) {
    global $db, $globals, $dblang;

    if (!$globals['buscador_activado'] || $globals['mobile'] ) return;

    if (!empty($what)) {
        $status = '= "'.$what. '"';
    } else {
        $status = "!= 'discarded'";
    }
    if(!empty($globals['meta_categories'])) {
        $meta_cond = 'and link_category in ('.$globals['meta_categories'].')';
    }

    $cache_key = 'tags_4_'.$status.$meta_cond;

    $min_pts = 8;
    $max_pts = 30;
    $line_height = $max_pts * 0.70;

    $min_date = date("Y-m-d H:i:00", $globals['now'] - 172800); // 48 hours (edit! 2zero)
    $from_where = "FROM tags, links WHERE tag_lang='$dblang' and tag_date > '$min_date' and link_id = tag_link_id and link_status $status $meta_cond GROUP BY tag_words";
    $max = max($db->get_var("select count(*) as words $from_where order by words desc limit 1"), 3);
    $coef = ($max_pts - $min_pts)/($max-1);

    $res = $db->get_results("select tag_words, count(*) as count $from_where order by count desc limit 30");
    if ($res) {
        $output = '<div class="tags-box">';
        $output .= '<h4><a href="'.$globals['base_url'].'nube.php">'._('etiquetas').'</a></h4><p class="nube">'."\n";
        foreach ($res as $item) {
            $words[$item->tag_words] = $item->count;
        }
        ksort($words);
        $contador = 0;
        foreach ($words as $word => $count) {
            $size = round($min_pts + ($count-1)*$coef, 1);
            if ($size == 8) continue;
            $contador ++;
            $output .= '<a style="font-size: '.$size.'pt" href="';
            if (isset($globals['base_search_url']) && $globals['base_search_url']) {
                $output .= $globals['base_url'].$globals['base_search_url'].'tag:';
            } else {
                $output .= $globals['base_url'].'search.php?p=tag&amp;q=';
            }
            $output .= urlencode($word).'">'.$word.'</a>  ';
        }
        $output .= '</p></div>';
        if ($contador > 5) // At least 5 words
            echo $output;
        else return;

    }
}


/*
function do_best_sites() {
    global $db, $globals;

    $output = '';
    $min_date = date("Y-m-d H:i:00", $globals['now'] - 129600); // about 36 hours
    // The order is not exactly the votes counts
    // but a time-decreasing function applied to the number of votes
    $res = $db->get_results("select sum(link_votes+link_anonymous-link_negatives)*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.8/129600) as coef, sum(link_votes+link_anonymous-link_negatives) as total, blog_url from links, blogs where link_date > '$min_date' and link_status='published' and link_blog = blog_id group by link_blog order by coef desc limit 10;
");
    if ($res) {
        $i = 0;
        $output .= '<div class="sidebox"><h4>'._('sitios más votados').'</h4><ul class="topcommentsli fondo-caja espaciador" style="list-style-type: none;">'."\n";
        foreach ($res as $site) {
            $i++;
            $parsed_url = parse_url($site->blog_url);
            $output .= '<li><strong>'.$i.'. <a href="'.$globals['base_url'].'search.php?q=site:'.rawurlencode($site->blog_url).'+period:36+status:published" title="'._('votos 36 horas').': '.$site->total.' Coef: '.$site->coef.'">'.$parsed_url['host'].'</a></strong></li>'."\n";
        }
        $output .= '</ul></div>';
        echo $output;

    }
}
*/

function do_best_comments() {
    global $db, $globals;

    if (isset($globals['mobile']) && $globals['mobile']) return;

    require_once(mnminclude.'link.php');
    $foo_link = new Link();
    $output = '';

    $numero = 0;
    $min_date = date("Y-m-d H:i:00", $globals['now'] - 60 * 60 * 24 * 30);
    // The order is not exactly the comment_karma
    // but a time-decreasing function applied to the number of votes
    $res = $db->get_results("select comment_id, comment_order, user_id, user_login, user_avatar, link_id, link_uri, link_title, link_comments,  comment_karma*(1-(unix_timestamp(now())-unix_timestamp(comment_date))*0.7/43000) as value from comments, links, users  where comment_date > '$min_date' and comment_karma > 50 and comment_link_id = link_id and comment_user_id = user_id order by value desc limit 12");
    if ($res) {
        $output .= '<div class="sidebox"><h4><a href="'.$globals['base_url'].'mejores_comentarios.php">'._('mejores comentarios').'</a></h4><ul class="topcommentsli fondo-caja espaciador">'."\n";
        foreach ($res as $comment) {
            $numero = $numero + 1;
            $foo_link->uri = $comment->link_uri;
            $link = $foo_link->get_relative_permalink().get_comment_page_suffix($globals['comments_page_size'], $comment->comment_order, $comment->link_comments).'#comment-'.$comment->comment_order;
                        $output .= '<li><a href="'.get_user_uri($comment->user_login).'"><img src="'.get_avatar_url($comment->user_id, $comment->user_avatar, 20).'" alt="" width="20" height="20" onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$comment->user_id.');" onmouseout="tooltip.clear(event);"/></a>';
            $output .= '<p><strong>'.'#'.$numero. ' <a href="'.get_user_uri($comment->user_login, 'comentarios').'">'.$comment->user_login.'</a></strong>'._(' en ').' <a onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_comment_tooltip.php\', \''.$comment->comment_id.'\', 10000);" href="'.$link.'">'.$comment->link_title.'</a></p></li>'."\n";
        }
        $output .= '</ul></div>';
        echo $output;

    }
}

function do_last_comments() {
    global $db, $globals;

    if (isset($globals['mobile']) && $globals['mobile']) return;

    require_once(mnminclude.'link.php');
    $foo_link = new Link();
    $output = '';

    $numero = 0;
    $min_date = date("Y-m-d H:i:00", $globals['now'] - 86400*4); // about 24*4 hours

    // The order is not exactly the comment_karma
    // but a time-decreasing function applied to the number of votes
    $res = $db->get_results("SELECT comment_id, comment_order, user_login, link_id, link_uri, link_title, link_comments FROM comments, users, links WHERE comment_user_id = user_id  AND comment_link_id = link_id ORDER BY comment_date DESC LIMIT 12");
    if ($res) {
        $output .= '<div class="sidebox"><h4><a href="'.$globals['base_url'].'ultimos_comentarios.php">'._('últimos comentarios').'</a></h4><ul class="topcommentsli fondo-caja espaciador">'."\n";
        foreach ($res as $comment) {
            $numero = $numero + 1;
            $foo_link->uri = $comment->link_uri;
            $link = $foo_link->get_relative_permalink().get_comment_page_suffix($globals['comments_page_size'], $comment->comment_order, $comment->link_comments).'#comment-'.$comment->comment_order;
            $output .='<li><strong>'.'#'.$numero. ' '. _($comment->user_login).'</strong>'._(' en ').' <a onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_comment_tooltip.php\', \''.$comment->comment_id.'\', 10000);" href="'.$link.'">'.$comment->link_title.'</a></li>'."\n";
        }
        $output .= '</ul></div>';
        echo $output;

    }
}

//muestra historias con contenido pornografico
function do_pron_stories() {
    global $db, $globals;

    if (isset($globals['mobile']) && $globals['mobile']) return;

    require_once(mnminclude.'link.php');
    $foo_link = new Link();

    $title = _('¡guarradas!');
    $output = '<div class="sidebox"><h4><a href="'.$globals['base_url'].'search.php?q=nsfw">'.$title.'</a></h4><div class="fondo-caja column-list mnm-pop-container">';

    $min_date = date("Y-m-d H:i:00", $globals['now'] - 172800); // 36 hours

    // The order is not exactly the votes
    // but a time-decreasing function applied to the number of votes
     $res = $db->get_results("SELECT  * FROM `links` WHERE link_date > '$min_date' and ( link_status='published' or link_status='queued') and (link_title LIKE '%[NSFW]%' OR link_title LIKE '%[+18]% ') or link_category=207 ORDER  BY link_id DESC LIMIT 0,11");
    if ($res) {
$n = 0;
        foreach ($res as $link) {

            $link->votes = $link->link_votes + $link->link_anonymous;
            $foo_link->uri = $link->link_uri;
            $url = $foo_link->get_relative_permalink();
            $qued = ' queued';
                         if ($link->link_status == 'published')$qued = ' ';
                        $output .= '<div class="mnm-pop'.$qued.'">'.$link->votes.'</div>';
            if ($n == 0) $output .= '<h5>';
            else $output .= '<h5>';
            $output .= '<a href="'.$url.'" onmouseover="return tooltip.ajax_delayed(event, \'get_link.php\', '.$link->link_id.');" onmouseout="tooltip.clear(event);">'.$link->link_title.'</a></h5>';
            $output .= '<div class="mini-pop"></div>'."\n";
            $n++;

        }
        $output .= '</div></div>'."\n";
        echo $output;

    }
}

function do_best_story_comments($link) {
    global $db, $globals;

    if (isset($globals['mobile']) && $globals['mobile']) return;

    $output = '';

    if ($link->comments > 30 && $globals['now'] - $link->date < 86400*4) $do_cache = true;

    $limit = min(25, intval($link->comments/5));
    $res = $db->get_results("select comment_id, user_id, user_avatar, comment_order, user_login, substring(comment_content, 1, 60) as content from comments, users  where comment_link_id = $link->id and comment_karma > 25 and comment_user_id = user_id order by comment_karma desc limit $limit");
    if ($res) {
        $output .= '<div class="sidebox"><h4><a href="'.$link->get_relative_permalink().'/mejores-comentarios">'._('mejores comentarios').'</a></h4><ul class="topcommentsli fondo-caja espaciador">'."\n";
        foreach ($res as $comment) {
            $url = $link->get_relative_permalink().get_comment_page_suffix($globals['comments_page_size'], $comment->comment_order, $link->comments).'#comment-'.$comment->comment_order;
              $output .= '<li><a href="'.get_user_uri($comment->user_login).'"><img src="'.get_avatar_url($comment->user_id, $comment->user_avatar, 20).'" alt="" width="20" height="20" onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$comment->user_id.');" onmouseout="tooltip.clear(event);"/></a>';
                        $output .= '<p><strong><a href="'.get_user_uri($comment->user_login, 'comentarios').'">'.$comment->user_login.'</a></strong>'._('  ').' <a onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_comment_tooltip.php\', \''.$comment->comment_id.'\', 10000);"  href="'.$url.'"><em>'.text_to_summary($comment->content, 60).'</em></a></p></li>'."\n";
                }

        $output .= '</ul></div>';
        echo $output;

    }
}

function do_best_stories() {
    global $db, $globals, $current_user;

    if (isset($globals['mobile']) && $globals['mobile']) return;

    require_once(mnminclude.'link.php');
    $foo_link = new Link();

    if ($globals['meta_current'] && $globals['meta_categories']) {
            $category_list = 'and link_category in ('.$globals['meta_categories'].')';
            $title = _('populares de').' <em>'.$globals['meta_current_name'].'</em>';
    } else {
        $category_list  = '';
        $title = _('populares');
    }

    $output = '<div class="sidebox"><h4><a href="'.$globals['base_url'].'las_mejores.php">'.$title.'</a></h4><div class="fondo-caja column-list mnm-pop-container">';

    $min_date = date("Y-m-d H:i:00", $globals['now'] - 129600*4); // 36*4 hours
    // The order is not exactly the votes
    // but a time-decreasing function applied to the number of votes
    $res = $db->get_results("select  link_id, (link_votes+link_anonymous-link_negatives)*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.8/129600) as value from links where link_status='published' $category_list and link_date > '$min_date' order by value desc limit 10");
    if ($res) {
        $n = 0;

        foreach ($res as $l) {
            $link = Link::from_db($l->link_id);
            $url = $link->get_relative_permalink();
            $output .= '<div class="mnm-pop">'.($link->votes+$link->anonymous).'</div>';

            if (($n == 0 && ! $link->has_thumb() ) && $current_user->thumb == 0)  $output .= '<h5 style="font-size:100%">';
            else  $output .= '<h5>';

        if (!$link->is_nsfw() &&$link->has_thumb() && ($current_user->thumb == 1 || $current_user->user_id == 0) ) {
                $link->thumb_x = round($link->thumb_x / 2);
                $link->thumb_y = round($link->thumb_y / 2);
                $output .= "<img src='"."$link->thumb' width='$link->thumb_x' height='$link->thumb_y' alt='' class='thumbnail'/>";
            }

            $output .= '<a href="'.$url.'" onmouseover="return tooltip.ajax_delayed(event, \'get_link.php\', '.$l->link_id.');" onmouseout="tooltip.clear(event);">'.$link->title.'</a></h5>';
            $output .= '<div class="mini-pop"></div>'."\n";
            $n++;
        }
        $output .= '</div></div>'."\n";
        echo $output;

    }
}

function do_best_queued() {
    global $db, $globals, $current_user;

    if (isset($globals['mobile']) && $globals['mobile']) return;

    require_once(mnminclude.'link.php');

    if ($globals['meta_current'] && $globals['meta_categories']) {
            $category_list = 'and link_category in ('.$globals['meta_categories'].')';
            $title = _('candidatas en').' <em>'.$globals['meta_current_name'].'</em>';
    } else {
        $category_list  = '';
        $title = _('candidatas');
    }

    $output = '<div class="sidebox"><h4><a href="'.$globals['base_url'].'promote.php">'.$title.'</a></h4><div class="fondo-caja column-list mnm-pop-container">';

    $min_date = date("Y-m-d H:i:00", $globals['now'] - 86400*3); // 72 hours
    // The order is not exactly the votes
    // but a time-decreasing function applied to the number of votes
     $res = $db->get_results("select link_id from links where link_status='queued' and link_date > '$min_date' $category_list order by link_karma desc limit 10");
    if ($res) {

        foreach ($res as $l) {
            $link = Link::from_db($l->link_id);
            $url = $link->get_relative_permalink();
            $output .= '<div class="mnm-pop queued">'.($link->votes+$link->anonymous).'</div>';
           if ( !$link->is_nsfw() && $link->has_thumb()  && ($current_user->thumb == 1 || $current_user->user_id == 0)  ) {
                $link->thumb_x = (int) $link->thumb_x / 2;
                $link->thumb_y = (int) $link->thumb_y / 2;
                $output .= "<img src='"."$link->thumb' width='$link->thumb_x' height='$link->thumb_y' alt='' class='thumbnail'/>";
            }
            $output .= '<h5><a href="'.$url.'" onmouseover="return tooltip.ajax_delayed(event, \'get_link.php\', '.$l->link_id.');" onmouseout="tooltip.clear(event);" >'.$link->title.'</a></h5>';
            $output .= '<div class="mini-pop"></div>'."\n";
        }
        $output .= '</div></div>'."\n";
        echo $output;

    }
}

function do_best_posts() {
    global $db, $globals, $current_user;

    if (isset($globals['mobile']) && $globals['mobile']) return;

    $output = '';
    $min_date = date("Y-m-d H:i:00", $globals['now'] - 60 * 60 * 24 * 30);
    $res = $db->get_results("select post_id, user_login, post_content, user_avatar, user_id from posts, users where post_date > '$min_date' and  post_user_id = user_id and post_karma > 0 order by post_karma desc limit 10");
    if ($res) {
        $output .= '<div class="sidebox"><h4><a href="'.$globals['base_url'].'mejores_notitas.php">'._('mejores notitas').'</a></h4><ul class="topcommentsli fondo-caja espaciador">'."\n";
        foreach ($res as $p) {
            $output .= '<li><a href="'.get_user_uri($p->user_login).'"><img src="'.get_avatar_url($p->user_id, $p->user_avatar, 20).'" onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$post->author.');" onmouseout="tooltip.clear(event);"/></a>';
            $output .= '<p><a href="'.post_get_base_url($p->user_login).'"><strong>'.$p->user_login.'</strong></a>: <a onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_post_tooltip.php\', \''.$p->post_id.'\', 10000);" href="'.post_get_base_url($p->user_login).'/'.$p->post_id.'"><em>'.text_to_summary(preg_replace('/(@[\S.-]+)(,\d+)/','$1',$p->post_content), 80).'</em></a></p></li>'."\n"; // clean user references
        }
        $output .= '</ul></div>';
        echo $output;

    }
}

function encuestas_mas_votadas() {
    global $db, $globals;

    $output = '<ul>' . "\n";
    $min_date = date("Y-m-d H:i:00", $globals['now'] - 86400*15); // 15 dias
    $res = $db->get_results("select encuesta_id, user_login, encuesta_title from encuestas, users where encuesta_start > '$min_date' and  encuesta_user_id = user_id and encuesta_total_votes > 0 order by encuesta_total_votes desc limit 10");

    if ($res) {

$output .= '<h4 style="margin-top: 45px"><a href="'.$globals['base_url'].'encuestas.php">'._('encuestas más votadas').'</a></h4>';
        $output .= '<ul class="topcommentsli fondo-caja espaciador">'."\n";

        foreach ($res as $p) {

            $output .= '<li><strong>'.$p->user_login.'</strong>: <a onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_encuesta.php\', \''.$p->encuesta_id.'\', 10000);" href="'.get_encuesta_uri($p->encuesta_id).'"><em>'.$p->encuesta_title.'</em></a></li>'."\n";
        }
            $output .= '</ul>';
        echo $output;

    }
}


function do_last_questions() {
    global $db, $globals;

    $output = '<ul>' . "\n";

    $res = $db->get_results("select encuesta_id, encuesta_title, user_login from encuestas, users where encuesta_user_id = user_id order by encuesta_id desc limit 8");

    if ($res) {
    $output .= '<h4 style="margin-top: 45px"><a href="'.$globals['base_url'].'encuestas.php">'._('últimas encuestas').'</a></h4>';
        $output .= '<ul class="topcommentsli fondo-caja espaciador">'."\n";
        foreach ($res as $p) {
            $output .= '<li><strong>'.$p->user_login.'</strong>: <a onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_encuesta.php\', \''.$p->encuesta_id.'\', 10000);" href="'.get_encuesta_uri($p->encuesta_id).'"><em>'.$p->encuesta_title.'</em></a></li>'."\n";
        }
        $output .= '</ul>';
        echo $output;

    }
}

function do_categories_new($what_cat_type, $what_cat_id) {
    global $db,  $globals;

    if (isset($globals['mobile']) && $globals['mobile']) return;

    echo '<h4>categorías</h4>';
    echo '<div class="column-list fondo-caja">' . "\n";
    echo '<ul>' . "\n";

    $query=preg_replace('/category=[0-9]*/', '', $_SERVER['QUERY_STRING']);
    // Always return to page 1
    $query=preg_replace('/page=[0-9]*/', '', $query);
    $query=preg_replace('/^&*(.*)&*$/', "$1", $query);
    if(!empty($query)) {
        $query = htmlspecialchars($query);
        $query = "&amp;$query";
    }


    $categories = $db->get_results("SELECT SQL_CACHE category_id, category_name FROM categories ORDER BY category_name ASC");

    if ($categories) {
        foreach ($categories as $category) {
            if($category->category_id == $what_cat_id) {
                $globals['category_id'] = $category->category_id;
                $globals['category_name'] = $category->category_name;
                $thiscat = ' class="thiscat"';
            } else {
                $thiscat = '';
            }

            echo '<li'.$thiscat.'><a href="'.$globals['base_url'].'?category='.$category->category_id.$query.'">';
            echo _($category->category_name);
            echo "</a></li>\n";
        }
    }

    echo '</ul>';
    echo '<br style="clear: both;" />' . "\n";
    echo '</div><!--html1:do_categories_new-->' . "\n";

}

function do_saved_searches() {
    global $db, $globals, $current_user;

    if (isset($globals['mobile']) && $globals['mobile']) return;

    if ($current_user->user_id == 0) return;

    $busquedas = $db->get_results("SELECT texto FROM busquedas_guardadas WHERE usuario=$current_user->user_id ORDER BY id");

    if (!$busquedas) return;

    echo '<h4>búsquedas guardadas</h4>';
    echo '<div class="column-list-busqueda fondo-caja">' . "\n";
    echo '<ul>' . "\n";

    foreach ($busquedas as $palabra) {

        echo '<li><a href="'.$globals['base_url'].'search.php?q='.$palabra->texto.'">';
            echo $palabra->texto;
            echo "</a></li>\n";
    }

    echo '</ul>';
    echo '<br style="clear: both;" />' . "\n";
    echo '</div><!--html1:do_busquedas_guardadas-->' . "\n";

}
