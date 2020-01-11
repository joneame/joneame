<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

require_once(mnminclude.'link.php');

class LinkMobile extends Link{
    function print_summary($type='full', $karma_best_comment = 0, $show_tags = true) {
        global $current_user, $current_user, $globals, $db;

        if(!$this->read) return;
        if($this->is_votable()) {
            $this->voted = $this->vote_exists($current_user->user_id);
        }

        echo '<div class="news-summary">';
        echo '<div class="news-body">';
        if ($globals['link']) $this->print_warn();

/*
        if (! $globals['link']) {
            $url = $this->get_permalink();
            $nofollow = '';
*/

        if ($this->status != 'published') $nofollow = ' rel="nofollow"';
        else $nofollow = '';
        $url = htmlspecialchars($this->url);

        if ($type != 'preview' && !empty($this->title) && !empty($this->content)) {
            $this->print_shake_box();
        }


        echo '<h1>';
        echo '<a href="'.$url.'"'.$nofollow.'>'. $this->title. '</a>';
        echo '</h1>';

        if ($globals['link']) {
            echo '<div class="news-submitted">';
            echo _('por').' <a href="'.get_user_uri($this->username, 'history').'">'.$this->username.'</a> ';
            // Print dates
            if ($globals['now'] - $this->date > 604800) { // 7 days
                if($this->status == 'published')
                    echo _('publicado el').get_date_time($this->date);
                else
                    echo _('el').get_date_time($this->sent_date);
            } else {
                if($this->status == 'published')
                    echo _('publicado hace').txt_time_diff($this->date);
                else
                    echo _('hace').txt_time_diff($this->sent_date);
            }
            echo "</div>\n";
        }

        $text = text_to_html($this->content);

        // Change links to mydomain.net to m.mydomain.net (used in "related")
        $my_domain = get_server_name();
        $parent_domain = preg_replace('/movil\./', '', $my_domain);
        if ($parent_domain != $my_domain && preg_match('#[^\.]'.preg_quote($parent_domain).'/#', $text)) {
            $text = preg_replace('#([^\.])'.preg_quote($parent_domain).'/#', "$1$my_domain/", $text);
        }
        echo $text;


        echo '<div class="news-details">';
        if($this->comments > 0) {
            $comments_mess = $this->comments . ' ' . _('comentarios');
        } else  {
            $comments_mess = _('sin chorradas');
        }
        echo '<span class="comments"><a href="'.$this->get_relative_permalink().'">'.$comments_mess. '</a></span>';

        if ($globals['link']) {
            // Print meta and category
            echo ' <span class="tool">'._('en').': ';
            echo $this->meta_name.', ';
            echo $this->category_name;
            echo '</span>';
            echo ' <span class="tool">carisma: <span id="a-karma-'.$this->id.'">'.intval($this->karma).'</span></span>';
        }

            echo '</div>';
            // End news details

        if ($globals['link']) {
            echo '<div class="news-details">';
            echo '<strong>'._('sensuras').'</strong>: '.$this->negatives.'&nbsp;&nbsp;';
            echo '<strong>'._('mafiosos').'</strong>: '.$this->votes.'&nbsp;&nbsp;';
            echo '<strong>'._('anónimos').'</strong>: '.$this->anonymous.'&nbsp;&nbsp;';
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';

    }

    function print_shake_box() {
        global $current_user, $anonnymous_vote, $site_key, $globals;

        switch ($this->status) {
            case 'queued': // another color box for not-published
                $box_class = 'mnm-queued';
                break;
            case 'abuse': // another color box for discarded
            case 'autodiscard': // another color box for discarded
            case 'discard': // another color box for discarded
                $box_class = 'mnm-discarded';
                break;
            case 'published': // default for published
            default:
                $box_class = 'mnm-published';
                break;
        }
        echo '<div class="news-shakeit">';
        echo '<div class="'.$box_class.'">';
        echo '<a id="a-votes-'.$this->id.'" href="'.$this->get_relative_permalink().'">'.($this->votes+$this->anonymous).'</a></div>';

        echo '<div class="menealo" id="a-va-'.$this->id.'">';

        if ($this->votes_enabled == false) {
            echo '<span>'._('chapado').'</span>';
        } elseif( !$this->voted) {
            echo '<a href="javascript:menealo('."$current_user->user_id,$this->id".')" id="a-shake-'.$this->id.'">'._('jonéalo').'</a>';
        } else {
            if ($this->voted > 0) $mess = _('¡bibaaa!');
            else $mess = ':-(';
            echo '<span id="a-shake-'.$this->id.'">'.$mess.'</span>';
        }
        echo '</div>';
        echo '</div>';
    }



}

    function print_shake_box() {
        global $current_user, $anonnymous_vote, $site_key, $globals;

        switch ($this->status) {
            case 'queued': // another color box for not-published
                $box_class = 'mnm-queued';
                break;
            case 'abuse': // another color box for discarded
            case 'autodiscard': // another color box for discarded
            case 'discard': // another color box for discarded
                $box_class = 'mnm-discarded';
                break;
            case 'published': // default for published
            default:
                $box_class = 'mnm-published';
                break;
        }
        echo '<div class="news-shakeit">';
        echo '<div class="'.$box_class.'">';
        echo '<a id="a-votes-'.$this->id.'" href="'.$this->get_relative_permalink().'">'.($this->votes+$this->anonymous).'</a></div>';

        if (! $globals['bot']) {
            echo '<div class="menealo" id="a-va-'.$this->id.'">';

            if ($this->votes_enabled == false) {
                echo '<span>'._('chapado').'</span>';
            } elseif( !$this->voted) {
echo '<a href="javascript:menealo('."$current_user->user_id,$this->id".')" id="a-shake-'.$this->id.'">'._('jonéala').'</a>';

            } else {
                if ($this->voted > 0) $mess = _('¡¡Biba!!');
                else $mess = ':-(';
                echo '<span id="a-shake-'.$this->id.'">'.$mess.'</span>';
            }
            echo '</div>';

        echo '</div>';
    }

    function print_warn() {
        global $db, $globals;

//si el estado es abuso muestra un aviso
                     if ($this->status == 'abuse') {
                    echo '<div class="warn"><strong>'._('Aviso').'</strong>: ';
                    echo _('historia descartada por violar las').' <a href="'.$globals['legal'].'#tos">'._('normas de uso').'</a>';
                    echo "</div>\n";
                }
             else {
                $this->warned = false;
                  }
 if ($this->status == 'abuse' && stripos($this -> title , '[NSFW]') && stripos($this -> title , '[+18]')  != FALSE) {
                    echo '<div class="warn"><strong>'._('Aviso').'</strong>: ';
                    echo _('historia descartada por violar las').' <a href="'.$globals['legal'].'#tos">'._('normas de uso').'</a>' ;
                    echo "</div>\n";
                }
             else {
                $this->warned = false;
                  }
//fin del aviso
//si detecta NSFW o +18 en el titulo muestra un aviso
            if ((stripos($this -> title , '[NSFW]') || stripos($this -> title , '[+18]')  != FALSE )  && $this->status != 'abuse') {
                echo '<div class="porn"><strong>'._('Oiga!!').'</strong> ';
                echo _('el enlace de esta historia podría contener material solo apto para adultos.');
                echo "</div>\n";
//fin del aviso
            } elseif ( $this->votes_enabled  && !$this->is_discarded() &&  $this->negatives > 1 && $this->negatives > $this->votes/6 ) {
            $this->warned = true;
            echo '<div class="warn"><strong>'._('Aviso automático de la mafia').'</strong>: ';
            if ($this->status == 'published') {
                echo _('historia controvertida, por favor lee las chorradas, a saber qué han liado estos.');
            } elseif ($this->author == $current_user->user_id && $this->is_editable()) {
                    echo _('Esta noticia tiene varios votos sensuradores. Si la descartas manualmente tu carisma no será afectado');
            } else {
                // Only says "what" if most votes are "wrong" or "duplicated"
                $negatives = $db->get_row("select vote_value, count(vote_value) as count from votes where vote_type='links' and vote_link_id=$this->id and vote_value < 0 group by vote_value order by count desc limit 1");
                if ($negatives->count > 2 && $negatives->count >= $this->negatives/2 && ($negatives->vote_value == -1 || $negatives->vote_value == -3)) {
                    echo _('Esta noticia podría ser <strong>'). get_negative_vote($negatives->vote_value) . '</strong>. ';
                } else {
                    echo _('Esta noticia tiene varios votos sensuradores.');
                }
                if(!$this->voted ) {
                    echo ' <a href="'.$this->get_relative_permalink().'/votos">' ._('Asegúrate').'</a> ' . _('antes de jonear') . '.';

                }

            }
            echo "</div>\n";
        } else {
            $this->warned = false;
        }
    }

    function print_problem_form() {
        global $current_user, $db, $anon_karma, $anonnymous_vote, $globals, $site_key;

        echo '<form  class="tool" action="" id="problem-'.$this->id.'">';
        echo '<select '.$status.' name="ratings"  onchange="';
        echo 'report_problem(this.form,'."$current_user->user_id, $this->id, "."'".$this->md5."'".')';
        echo '">';
        echo '<option value="0" selected="selected">'._('sensura').'</option>';
        foreach (array_keys($globals['negative_votes_values']) as $pvalue) {
            echo '<option value="'.$pvalue.'">'.$globals['negative_votes_values'][$pvalue].'</option>';
        }
        echo '</select>';
        echo '</form>';
    }

}