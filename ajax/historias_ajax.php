<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jon Arano <arano.jon@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include_once('../config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
include(mnminclude.'comment.php');


$link = new Link;
$link->id = intval($_GET['link_id']);
$link->read('id');

if ( !empty($_GET['server'])) {
    $url_args = preg_split('/\/+/', $_GET['server']);
    array_shift($url_args); // The first element is always a "/"
}

$last_arg = count($url_args)-1;

if ($last_arg > 0) {
    if (preg_match('/^000/', $url_args[$last_arg])) {
        if ($url_args[$last_arg] > 0) {
            header('Location: ' . $link->get_permalink().get_comment_page_suffix($globals['comments_page_size'], (int) $url_args[$last_arg], $link->comments).'#comment-'.(int) $url_args[$last_arg]);
        } else {
            header('Location: ' . $link->get_permalink());
        }
        die;
    }

    if ($url_args[$last_arg] > 0) {
        $requested_page = $current_page =  (int) $url_args[$last_arg];

        array_pop($url_args);
    }
}

if ($globals['comments_page_size'] && $link->comments > $globals['comments_page_size']*$globals['comments_page_threshold']) {
        if (!$current_page) $current_page = ceil($link->comments/$globals['comments_page_size']);
        $offset=($current_page-1)*$globals['comments_page_size'];

        $limit = "LIMIT $offset,".$globals['comments_page_size'];
}

$tab = intval($_GET['tab']);
$what = intval($_GET['what']);

switch($what){

    case 1: print_logs();
        break;

    case 2:
        print_comments();
        break;
    case 3:
        print_best_comments();
        break;

    case 4: print_burradas();
        break;

    case 5: print_trackbacks();
        break;

    case 6: print_votes();
        break;

    case 7: print_favorites();
        break;

    case 8: print_sneaker();
        break;

    default: die;
}

function print_burradas(){
    global $db, $globals, $link, $tab, $current_page, $limit;

    echo '<div class="comments">';

    echo '<div id="ajaxcontainer"><div id="ajaxcomments"></div></div>';

    // Print tabs
    print_story_tabs($tab,$link->id);

    if($tab == 8) do_comment_pages($link->comments, $current_page);

    $comments = $db->get_col("SELECT comment_id, comment_karma FROM comments WHERE comment_link_id=$link->id AND comment_karma < 7 ORDER BY $order_field $limit");
    if ($comments) {
        echo '<ol class="comments-list">';
        require_once(mnminclude.'comment.php');

        foreach($comments as $comment_id) {
            $comment = Comment::from_db($comment_id);
            $comment->print_summary($link, 700, true);
            echo "\n";
        }
        echo "</ol>\n";

    } else echo 'no hay burradas... de momento';

    echo '</div>' . "\n";

}

function print_trackbacks(){
    global $db, $tab, $link, $globals;
    echo '<div class="voters" id="voters">';

    print_story_tabs($tab);

    echo '<h4>'._('lugares que enlazan esta noticia').'</h4><fieldset class="fondo-caja">';
    echo '<ul class="tab-trackback">';

    $trackbacks = $db->get_col("SELECT trackback_id FROM trackbacks WHERE trackback_link_id=$link->id AND trackback_type='in' and trackback_status = 'ok' ORDER BY trackback_date DESC limit 50");

    if ($trackbacks) {
        require_once(mnminclude.'trackback.php');
        $trackback = new Trackback;
        foreach($trackbacks as $trackback_id) {
            $trackback->id=$trackback_id;
            $trackback->read();
            echo '<li class="tab-trackback-entry"><a href="'.$trackback->url.'" rel="nofollow">'.$trackback->title.'</a> ['.preg_replace('/https*:\/\/([^\/]+).*/', "$1", $trackback->url).']</li>' . "\n";
        }
    }

    echo '</ul>';
    echo '</fieldset>';
    echo '<br/>';
    echo '<a href="'.$link->get_trackback().'" title="'._('URI para trackbacks').'" class="tab-trackback-url"><img src="'.$globals['base_url'].'img/estructura/pixel.gif" alt="'._('enlace trackback').'" class="icon trackback img-flotante"/> '._('dirección de trackback').'</a>' . "\n";

    echo '</div>';
}

function print_sneaker(){
    global $tab, $globals, $link;

    echo '<div class="mini-sneaker">';
    print_story_tabs($tab);

    $globals['link'] = $link;
    $globals['link_id'] = $link->id;
    echo '<fieldset class="fondo-caja redondo">';
    include(mnmpath.'/libs/link_sneak.php');
    echo '</fieldset>';
    echo '</div>';

    echo '<script type="text/javascript">$(function(){start_link_sneak()});</script>' . "\n";

}

function print_favorites() {
    global $tab, $link, $globals;

    echo '<div class="voters" id="voters">';

    print_story_tabs($tab);

    $globals['link_id'] = $link->id;

    echo '<div id="voters-container">';
    include(mnmpath.'/backend/get_link_favorites.php');
    echo '</div>';
    echo '</div>';
}

function print_votes(){
    global $globals, $link, $tab;

    echo '<div class="voters" id="voters">';

    print_story_tabs($tab);

    echo '<div id="voters-container">';

    $globals['link_id'] = $link->id;

    if ($link->sent_date < $globals['now'] - 2592000) { // older than 30 days
        echo _('Noticia antigua, datos de votos archivados en nuestra base de datos');
    } else {
        include(mnmpath.'/backend/votos.php');

    }

    echo '</div><br/>';
    echo '</div>';

}

function print_best_comments() {
    global $globals, $link, $current_page, $db, $tab, $current_user, $order_field, $limit;

    echo '<div class="comments">';

    // Print tabs
    print_story_tabs($tab);

    $comments = $db->get_col("SELECT comment_id FROM comments WHERE comment_link_id=$link->id ORDER BY comment_karma DESC, comment_id asc $limit");

    if ($comments) {
        echo '<ol class="comments-list">';
        require_once(mnminclude.'comment.php');

        foreach($comments as $comment_id) {
            $comment = Comment::from_db($comment_id);
            $comment->print_summary($link, 700, true);
            echo "\n";
        }
        echo "</ol>\n";
    }

       if(!$link->comentarios_permitidos) {
        // Comments closed by admin
        if($tab == 1) do_comment_pages($link->comments, $current_page);

        cant_comment('comentarios cerrados temporalmente');
    }

    else if($link->date < $globals['now']-$globals['time_enabled_comments'] || $link->comments >= $globals['max_comments']) {
        // Comments already closed
        if($tab == 1) do_comment_pages($link->comments, $current_page);

        cant_comment('comentarios cerrados');

    } else if ($current_user->authenticated &&
           $link->comentarios_permitidos &&
          ($current_user->user_karma > $globals['min_karma_for_comments'] ||
             $current_user->user_id == $link->author)) {

         // User can comment
        echo '<div id="ajaxcontainer"><div id="ajaxcomments"></div></div>';

                   print_comment_form();

               if($tab == 1) do_comment_pages($link->comments, $current_page);
    } else {
        // Not enough carisma or anonymous user
        if($tab == 1) do_comment_pages($link->comments, $current_page);

        if ($current_user->authenticated) {

            if ($current_user->user_date >= $globals['now'] - $globals['min_time_for_comments']) {
                $remaining = txt_time_diff($globals['now'], $current_user->user_date+$globals['min_time_for_comments']);
                $msg = _('Debes esperar') . " $remaining " . _('para escribir tu primer comentario');
            }

            if ($current_user->user_karma <= $globals['min_karma_for_comments']) {
                $msg = _('Necesitas ').$globals['min_karma_for_comments'] . _(' de carisma para escribir comentarios'). ", pero tu carisma es ".$current_user->user_karma;
            }

            echo '<div class="barra redondo">'."\n";
            echo $msg . "\n";
            echo '</div>'."\n";

    } else if (!$globals['bot']){

        echo '<div class="barra redondo">'."\n";
        echo '<a href="'.$globals['base_url'].'login.php?return='.urlencode($link->get_permalink()).'">'._('Entra con tu cuenta de usuario').'</a> '._('si deseas escribir comentarios').'. '._('O crea tu cuenta haciendo clic'). ' <a href="'.$globals['base_url'].'register.php">aquí</a>'."\n";
        echo '</div>'."\n";

    }
   }

    echo '</div>';

}

function print_comments() {
    global $globals, $link, $current_page, $db, $tab, $current_user, $limit;

    echo '<div class="comments">';

    // Print tabs
    print_story_tabs($tab);

    $comments = $db->get_col("SELECT comment_id FROM comments WHERE comment_link_id=$link->id ORDER BY comment_order asc, comment_id asc ".$limit);

    if ($comments) {
        echo '<ol class="comments-list">';
        require_once(mnminclude.'comment.php');

        foreach($comments as $comment_id) {
            $comment = Comment::from_db($comment_id);
            $comment->print_summary($link, 700, true);
            echo "\n";
        }
        echo "</ol>\n";
    }

       if(!$link->comentarios_permitidos) {
        // Comments closed by admin
        if($tab == 1) do_comment_pages($link->comments, $current_page);

        cant_comment('comentarios cerrados temporalmente');
    }

    else if($link->date < $globals['now']-$globals['time_enabled_comments'] || $link->comments >= $globals['max_comments']) {
        // Comments already closed
        if($tab == 1) do_comment_pages($link->comments, $current_page);

        cant_comment('comentarios cerrados');

    } else if ($current_user->authenticated &&
           $link->comentarios_permitidos &&
          ($current_user->user_karma > $globals['min_karma_for_comments'] ||
             $current_user->user_id == $link->author)) {

         // User can comment
        echo '<div id="ajaxcontainer"><div id="ajaxcomments"></div></div>';

                   print_comment_form();

               if($tab == 1) do_comment_pages($link->comments, $current_page);
    } else {
        // Not enough carisma or anonymous user
        if($tab == 1) do_comment_pages($link->comments, $current_page);

        if ($current_user->authenticated) {

            if ($current_user->user_date >= $globals['now'] - $globals['min_time_for_comments']) {
                $remaining = txt_time_diff($globals['now'], $current_user->user_date+$globals['min_time_for_comments']);
                $msg = _('Debes esperar') . " $remaining " . _('para escribir tu primer comentario');
            }

            if ($current_user->user_karma <= $globals['min_karma_for_comments']) {
                $msg = _('Necesitas ').$globals['min_karma_for_comments'] . _(' de carisma para escribir comentarios'). ", pero tu carisma es ".$current_user->user_karma;
            }

            echo '<div class="barra redondo">'."\n";
            echo $msg . "\n";
            echo '</div>'."\n";

    } else if (!$globals['bot']){

        echo '<div class="barra redondo">'."\n";
        echo '<a href="'.$globals['base_url'].'login.php?return='.urlencode($link->get_permalink()).'">'._('Entra con tu cuenta de usuario').'</a> '._('si deseas escribir comentarios').'. '._('O crea tu cuenta haciendo clic'). ' <a href="'.$globals['base_url'].'register.php">aquí</a>'."\n";
        echo '</div>'."\n";

    }
   }

    echo '</div>';

}

function print_logs() {
    global $db, $globals, $link, $tab, $current_user;

    echo '<div class="voters" id="voters">';

    print_story_tabs($tab);

    echo '<h4>'._('registro (la mafia no manipula)').'</h4><fieldset class="fondo-caja">';

    echo '<div id="voters-container">';

    $logs = $db->get_results("select logs.*, user_id, user_login, user_avatar from logs, users where log_type in ('link_new', 'link_publish', 'link_discard', 'link_edit', 'link_geo_edit', 'link_depublished') and log_ref_id=".$link->id." and user_id= log_user_id order by log_date asc");

    if ($logs) {

    foreach ($logs as $log) {

        echo '<div style="width:100%; display: block; clear: both; border-bottom: 1px solid #adcee9;">';
        echo '<div style="width:30%; float: left;padding: 4px 0 4px 0;">'.$log->log_date.'</div>';

        switch($log->log_type){

            case 'link_new':
            $que= 'enviada';
            break;

            case 'link_publish':
            $que='publicada';
            break;

            case 'link_discard':
            $que='descartada';
            break;

            case 'link_edit':
            $que='editada';
            break;

            case 'link_geo_edit':
            $que = 'geolocalizada';
            break;

            case 'link_depublished':
            $que='retirada de portada';
            break;
        }

        echo '<div style="width:24%; float: left;padding: 4px 0 4px 0;"><strong>'.$que.'</strong></div>';
        echo '<div style="width:45%; float: left;padding: 4px 0 4px 0;">';

        if ($log->log_type == 'link_discard' && $link->author != $log->user_id) { // It was discarded by an admin
            echo '<img src="'.get_admin_avatar(20).'">&nbsp;';
            echo _('la administración de jonéame');
            if ($current_user->admin) {
                echo '&nbsp;('.$log->user_login.')';
            }
        } else {
            echo '<a href="'.get_user_uri($log->user_login).'" title="'.$log->date.'">';
            echo '<img src="'.get_avatar_url($log->log_user_id, $log->user_avatar, 20).'" width="20" height="20" alt="'.$log->user_login.'"/>&nbsp;';
            echo $log->user_login;
            echo '</a>';
        }
        echo '</div>';
        echo '</div>';
    }

    } else {
        echo _('no hay registros o la noticia está pasada de moda');
    }

    echo '</div><br />';
    echo '</fieldset>';
    echo '</div>';

}

function cant_comment($texto){

        echo '<h4 class="redondo">'."\n";
        echo _($texto)."\n";
        echo '</h4>'."\n";
}

function do_comment_pages($total, $current, $reverse = true) {
    global $globals, $link;

    if ( ! $globals['comments_page_size'] || $total <= $globals['comments_page_size']*$globals['comments_page_threshold']) return;

    $index_limit = 10;

    $query = $link->get_relative_permalink();

    $total_pages=ceil($total/$globals['comments_page_size']);
    if (! $current) {
        if ($reverse) $current = $total_pages;
        else $current = 1;
    }
    $start=max($current-intval($index_limit/2), 1);
    $end=$start+$index_limit-1;

    echo '<div class="pages">';

    if($current==1) {
        echo '<span class="barra semi-redondo nextprev">« '._('anterior').'</span>';
    } else {
        $i = $current-1;
        echo '<a class="barra semi-redondo" href="'.get_comment_page_url($i, $total_pages, $query).'">« '._('anterior').'</a>';
    }

    $dots_before = $dots_after = false;
    for ($i=1;$i<=$total_pages;$i++) {
        if($i==$current) {
            echo '<span class="barra semi-redondo current">'.$i.'</span>';
        } else {
            if ($total_pages < 7 || abs($i-$current) < 3 || $i < 3 || abs($i-$total_pages) < 2) {
                echo '<a class="barra semi-redondo" href="'.get_comment_page_url($i, $total_pages, $query).'" title="'._('ir a página')." $i".'">'.$i.'</a>';
            } else {
                if ($i<$current && !$dots_before) {
                    $dots_before = true;
                    echo '<span class="barra semi-redondo nextprev">...</span>';
                } elseif ($i>$current && !$dots_after) {
                    $dots_after = true;
                    echo '<span class="barra semi-redondo nextprev">...</span>';
                }
            }
        }
    }

    if($current<$total_pages) {
        $i = $current+1;
        echo '<a class="barra semi-redondo" href="'.get_comment_page_url($i, $total_pages, $query).'">'._('siguiente').' »</a>';
    } else {
        echo '<span class="barra semi-redondo nextprev">'._('siguiente'). ' »</span>';
    }
    echo "</div>\n";

}

function get_comment_page_url($i, $total, $query) {
    global $globals;
    if ($i == $total) return $query;
    else return $query.'/'.$i;
}

function print_comment_form() {
    global $link, $current_user, $globals;

    if (!$link->author > 0 && !$link->sent) return;

    echo '<div class="commentform">'."\n";
    echo '<form action="" method="post">'."\n";
    echo '<h4>'._('escribe un comentario').'</h4><fieldset class="fondo-caja">'."\n";
    echo '<div style="float: right;">'."\n";
    print_simpleformat_buttons('comment', true);
    echo '<div class="smileylist" id="smileylist">'.smiley_list().'</div><div style="margin-top: 10px;"><textarea name="comment_content" id="comment" cols="75" rows="12"></textarea></div>'."\n";
    echo '<input type="button" class="button" name="submit" id="submit_com" value="'._('enviar comentario').'" onClick="submit_comment();"/>'."\n";

    echo '<img id="spinner" class="blank" src="'.$globals['base_url'].'img/estructura/pixel.gif" width="16" height="16"/>';

    // Allow gods to put "admin" comments which does not allow votes
    if ($current_user->admin )
        echo '<div style="float: right; margin-right: 10px;">';
    else
        echo '<div style="display: none;">';

        echo '<input name="type" type="checkbox" value="admin" id="comentario-admin"/>&nbsp;<label for="comentario-admin">'._('comentario admin').'</strong></label>'."\n";
        echo '&nbsp;&nbsp;<input name="especial" type="checkbox" value="especial" id="comentario-especial"/>&nbsp;<label for="comentario-especial">'._('no mostrar mi nick').'</strong></label>'."\n";
        echo '</div>';

    echo '<br/><span id="error_com"></span>';

    echo '<input type="hidden" id="process" name="process" value="newcomment" />'."\n";
    echo '<input type="hidden" id="randkey" name="randkey" value="'.rand(1000000,100000000).'" />'."\n";
    echo '<input type="hidden" id="link_id" name="link_id" value="'.$link->id.'" />'."\n";
    echo '<input type="hidden" id="user_id" name="user_id" value="'.$current_user->user_id.'" />'."\n";
    echo '</fieldset>'."\n";
    echo '</form>'."\n";
    echo "</div>\n";

}

function print_story_tabs($option,$id = false) {
    global $link, $db, $globals;

    $active = array();

    // Avoid PHP warnings
    for ($n=1; $n <= 9; $n++) $active[$n] = '';

    $active[$option] = ' class="current"';

    if ($id)
        $burradas = $db->get_var("SELECT COUNT(*) comment_id, comment_karma FROM comments WHERE comment_link_id=$id AND comment_karma < 7");

    $permalink = $link->get_relative_permalink();
    echo '<ul class="tabsub">'."\n";
    echo '<li'.$active[1].'><a href="'.$permalink.'" onClick="link_show('.$link->id.', 1, 2); return false;">'._('comentarios'). '</a></li>'."\n";
    echo '<li'.$active[2].'><a href="'.$permalink.'/mejores-comentarios" onClick="link_show('.$link->id.', 2, 3); return false;">'._('+ valorados'). '</a></li>'."\n";
    if ($burradas > 0)
        echo '<li'.$active[8].'><a href="'.$permalink.'/burradas" onClick="link_show('.$link->id.', 8, 4); return false;">'._('burradas'). '</a></li>'."\n";
    // echo '<li'.$active[7].'><a href="'.$permalink.'/trackbacks" onClick="link_show('.$link->id.', 7, 5); return false;"">'._('trackbacks'). '</a></li>'."\n";

    if (!$globals['bot']) { // Don't show "empty" pages to bots, Google can penalize too

        if ($link->sent_date > time() - 2592000) { // newer than 60 days
            echo '<li'.$active[3].'><a href="'.$permalink.'/votos" onClick="link_show('.$link->id.', 3, 6); return false;">'._('votos'). '</a></li>'."\n";
        }

        echo '<li'.$active[6].'><a href="'.$permalink.'/favoritos" onClick="link_show('.$link->id.', 6, 7); return false;">&nbsp;'.FAV_YES.'&nbsp;</a></li>'."\n";

        if ($link->date > time() - 2592000) {
            // echo '<li'.$active[5].'><a href="'.$permalink.'/cotillona" onClick="link_show('.$link->id.', 5, 8); return false;">&micro;&nbsp;'._('cotillona'). '</a></li>'."\n";
            echo '<li'.$active[4].'><a href="'.$permalink.'/eventos#" onClick="link_show('.$link->id.', 4, 1); return false;">'._('eventos'). '</a></li>'."\n";

        }
    }

    /* Spinner */
    echo '&nbsp;&nbsp;&nbsp;<img id="spinner_h" class="blank" src="'.$globals['base_url'].'img/estructura/pixel.gif" width="16" height="16"/>';

    echo '</ul>'."\n";
}

function smiley_list(){

$smileys = array("cool", "lol", "roll", "confused", "blank", "kiss",  "palm", "wow", "shame", "smiley", "awesome", "ffu", "sisi", "gaydude", "nuse", "wink", "cheesy", "grin", "oops",  "cry", "cheesy", "angry", "huh", "sad", "shocked", "tongue", "lipssealed", "undecided",  "clint", "cejas", "sisitres", "music", "roto", "trollface", "yeah", "alone", "troll", "trollface", "longcat", "freising", "yaoface");

foreach ($smileys as $smiley) {

$devolver .= "<a onClick='javascript:appySmiley(\"".$smiley."\");'>".put_smileys("{".$smiley."}")."</a>";

}

return $devolver;



/*.
            put_smileys('{kiss}').
          put_smileys('{cejas}').
        put_smileys('{sisitres}').
        put_smileys('{music}').
        put_smileys('{roto}').
        put_smileys('{trollface}').
        put_smileys('{yeah}').
        put_smileys('{alone}').
        put_smileys('{troll}').
        put_smileys('{longcat}').
        put_smileys('{freising}').
        put_smileys('{yaoface}');*/
}
?>
