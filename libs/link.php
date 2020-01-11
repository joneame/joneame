<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

require_once(mnminclude.'log.php');
require_once(mnminclude.'favorites.php');

class Link {
    var $id = 0;
    var $author = -1;
    var $blog = 0;
    var $username = false;
    var $randkey = 0;
    var $karma = 0;
    var $valid = false;
    var $date = false;
    var $sent_date = 0;
    var $sent = 0;
    var $modified = 0;
    var $url = false;
    var $url_title = '';
    var $encoding = false;
    var $status = 'discard';
    var $type = '';
    var $category = 0;
    var $votes = 0;
    var $anonymous = 0;
    var $votes_avg = 0;
    var $negatives = 0;
    var $title = '';
    var $tags = '';
    var $uri = '';
    var $content = '';
    var $content_type = '';
    var $ip = '';
    var $html = false;
    var $read = false;
    var $voted = false;
    var $banned = false;
    var $thumb_status = 'unknown';
    var $aleatorios_positivos = 0;
    var $aleatorios_negativos = 0;
    var $clicks = 0;
    var $visits = 0;
    var $broken_link = 0;

    var $votes_sum = 0;
    var $negatives_sum = 0;

    // sql fields to build an object from mysql
    //const SQL = " link_id as id, link_author as author, link_blog as blog, link_status as status, link_votes as votes, link_negatives as negatives, link_anonymous as anonymous, link_votes_avg as votes_avg, link_aleatorios_positivos as aleatorios_positivos, link_aleatorios_negativos as aleatorios_negativos, link_comments as comments, link_karma as karma, link_randkey as randkey, link_category as category, link_url as url, link_uri as uri, link_url_title as title, link_title as title, link_tags as tags, link_content as content, UNIX_TIMESTAMP(link_date) as date,  UNIX_TIMESTAMP(link_sent_date) as sent_date, link_sent as sent, UNIX_TIMESTAMP(link_modified) as modified, link_content_type as content_type, link_ip as ip, link_thumb_status as thumb_status, link_thumb_x as thumb_x, link_thumb_y as thumb_y, link_thumb as thumb, link_comentarios_permitidos as comentarios_permitidos, link_votos_permitidos as votos_permitidos, user_login as username, user_email as email, user_avatar as avatar, user_karma as user_karma, user_level as user_level, user_adcode, cat.category_name as category_name, cat.category_uri as category_uri, meta.category_id as meta_id, meta.category_name as meta_name, favorite_link_id as favorite, clicks.counter as clicks, visits.counter as visits FROM links LEFT JOIN favorites ON (@user_id > 0 and favorite_user_id =  @user_id and favorite_type = 'link' and favorite_link_id = links.link_id) LEFT JOIN categories as cat on (cat.category_id = links.link_category) LEFT JOIN categories as meta on (meta.category_id = cat.category_parent) LEFT JOIN link_clicks as clicks on (clicks.id = links.link_id) LEFT JOIN link_visits as visits on (visits.id = links.link_id), users ";

    // sql fields to build an object from mysql
        const SQL = " link_id as id, link_author as author, link_blog as blog, link_status as status, link_votes as votes, link_negatives as negatives, link_anonymous as anonymous, link_votes_avg as votes_avg, link_aleatorios_positivos as aleatorios_positivos, link_aleatorios_negativos as aleatorios_negativos, link_comments as comments, link_karma as karma, link_randkey as randkey, link_category as category, link_url as url, link_uri as uri, link_url_title as title, link_title as title, link_tags as tags, link_content as content, UNIX_TIMESTAMP(link_date) as date,  UNIX_TIMESTAMP(link_sent_date) as sent_date, link_sent as sent, UNIX_TIMESTAMP(link_modified) as modified, link_content_type as content_type, link_ip as ip, link_thumb_status as thumb_status, link_thumb_x as thumb_x, link_thumb_y as thumb_y, link_thumb as thumb, link_comentarios_permitidos as comentarios_permitidos, link_votos_permitidos as votos_permitidos, link_broken_link as broken_link, user_login as username, user_email as email, user_avatar as avatar, user_karma as user_karma, user_level, cat.category_name as category_name, meta.category_id as meta_id, favorite_link_id as favorite, clicks.counter as clicks, visits.counter as visits, votes.vote_value as voted FROM links
    INNER JOIN users on (user_id = link_author)
    LEFT JOIN (categories as cat, categories as meta) on (cat.category_id = links.link_category)
    LEFT JOIN votes ON (link_date > @enabled_votes and vote_type='links' and vote_link_id = links.link_id and vote_user_id = @user_id and ( @user_id > 0  OR vote_ip_int = @ip_int ) )
    LEFT JOIN favorites ON (@user_id > 0 and favorite_user_id =  @user_id and favorite_type = 'link' and favorite_link_id = links.link_id)
    LEFT JOIN link_clicks as clicks on (clicks.id = links.link_id)
    LEFT JOIN link_visits as visits on (visits.id = links.link_id)";

    static function from_db($id) {
            global $db;

        if (is_numeric($id) && $id > 0) $selector = " link_id = $id ";
        else $selector = " link_uri = '$id' ";

        if(($object = $db->get_object("SELECT".Link::SQL." WHERE $selector LIMIT 1", 'Link'))) {
            $object->votes_sum = $object->votes + $object->aleatorios_positivos;
            $object->negatives_sum = $object->negatives + $object->aleatorios_negativos;
            $object->read = true;
            return $object;
        }
        return false;
        }

    function json_votes_info($value=false) {
        $dict = array();
        $dict['id'] = $this->id;
        if ($value)
            $dict['value'] = $value;
        $dict['votes'] = $this->votes;
        $dict['aleatorios_positivos'] = $this->aleatorios_positivos;
        $dict['anonymous'] = $this->anonymous;
        $dict['negatives'] = $this->negatives;
        $dict['karma'] = intval($this->karma);
        $dict['aleatorio_valor'] = 'no';
        return json_encode($dict);
    }

    function json_votes_info_aleatorio($value=false) {
        $voto_aleatorio = $this->aleatorio_info();
        $dict = array();
        $dict['id'] = $this->id;
        if ($value)
            $dict['value'] = $value;
        $dict['votes'] = $this->votes;
        $dict['anonymous'] = $this->anonymous;
        $dict['aleatorios_positivos'] = $this->aleatorios_positivos;
        $dict['aleatorios_negativos'] = $this->aleatorios_negativos;
        $dict['negatives'] = $this->negatives;
        $dict['karma'] = intval($this->karma);
        $dict['aleatorio_valor'] = $voto_aleatorio->valor;
        return json_encode($dict);
    }

    function print_html() {
        echo "Valid: " . $this->valid . "<br>\n";
        echo "Url: " . $this->url . "<br>\n";
        echo "Title: " . $this->url_title . "<br>\n";
        echo "encoding: " . $this->encoding . "<br>\n";
    }

    function check_url($url, $check_local = true, $first_level = false) {
        global $globals, $current_user;
        if(!preg_match('/^http[s]*:/', $url))
            return false;
        $url_components = @parse_url($url);
        if (!$url_components)
            return false;
        if (!preg_match('/[a-z]+/', $url_components['host']))
            return false;
        $quoted_domain = preg_quote(get_server_name());
        if($check_local && preg_match("/^$quoted_domain$/", $url_components['host'])) {
            $this->ban = array();
            $this->ban['comment'] = _('el servidor es local');
            syslog(LOG_NOTICE, "Joneame, server name is local name ($current_user->user_login): $url");
            return false;
        }
        require_once(mnminclude.'ban.php');
        if(($this->ban = check_ban($url, 'hostname', false, $first_level))) {
            syslog(LOG_NOTICE, "Joneame, server name is banned ($current_user->user_login): $url");
            $this->banned = true;
            return false;
        }
        return true;
    }

    function get($url, $maxlen = 150000, $check_local = true) {
        global $globals;
        $url=trim($url);
        $url_components = @parse_url($url);
        if(version_compare(phpversion(), '5.0.0') >= 0) {
            $opts = array(
                'http' => array('user_agent' => 'Bot de noticias de Joneame (https://joneame.net/)', 'max_redirects' => 7, 'timeout' => 10, 'header' => 'Referer: https://'.get_server_name().$globals['base_url']."\r\n" ),
                'https' => array('user_agent' => 'Bot de noticias de Joneame (https://joneame.net/)', 'max_redirects' => 7, 'timeout' => 10, 'header' => 'Referer: https://'.get_server_name().$globals['base_url']."\r\n" ),
            );
            $context = stream_context_create($opts);
            if(($stream = @fopen($url, 'r', false, $context))) {
                $meta_data = stream_get_meta_data($stream);
                foreach($meta_data['wrapper_data'] as $response) {
                    // Check if it has pingbacks
                    if (preg_match('/^X-Pingback: /i', $response)) {
                        $answer = preg_split(' ', $response);
                        if (!empty($answer[1])) {
                            $this->pingback = 'ping:'.trim($answer[1]);
                        }
                    }
                    /* Were we redirected? */
                    if (preg_match('/^location: /i', $response)) {
                        /* update $url with where we were redirected to */
                        $answer = preg_split(' ', $response);
                        $new_url = clean_input_url($answer[1]);
                    }
                    if (preg_match('/^content-type: /i', $response)) {
                        $answer = preg_split(' ', $response);
                        $this->content_type = preg_replace('/\/.*$/', '', $answer[1]);
                    }
                }
                if (!empty($new_url) && $new_url != $url) {
                    syslog(LOG_NOTICE, "Joneame, redirected ($current_user->user_login): $url -> $new_url");
                    /* Check again the url */
                    // Warn: relative path can come in "Location:" headers, manage them
                    if(!preg_match('/^http[s]*:/', $new_url)) {
                        // It's relative
                        $new_url = $url . $new_url;
                    }
                    if (!$this->check_url($new_url, $check_local, true)) {
                        $this->url = $new_url;
                        return false;
                    }
                    // Change the url if we were directed to another host
                    if (strlen($new_url) < 250  && ($new_url_components = @parse_url($new_url))) {
                        if ($url_components['host'] != $new_url_components['host']) {
                            syslog(LOG_NOTICE, "Joneame, changed source URL ($current_user->user_login): $url -> $new_url");
                            $url = $new_url;
                            $url_components = $new_url_components;
                        }
                    }
                }
                $url_ok = $this->html = @stream_get_contents($stream, $maxlen);
                fclose($stream);
            } else {
                syslog(LOG_NOTICE, "Joneame, error getting ($current_user->user_login): $url");
                $url_ok = false;
            }
            //$url_ok = $this->html = @file_get_contents($url, false, $context, 0, 200000);
        } else {
            $url_ok = $this->html = @file_get_contents($url);
        }
        $this->url=$url;
        // Fill content type if empty
        // Right now only check for typical image extensions
        if (empty($this->content_type)) {
            if (preg_match('/(jpg|jpeg|gif|png)(\?|#|$)/i', $this->url)) {
                $this->content_type='image';
            }
        }
        // NO more to do
        if (!$url_ok)
            return true;

        if(preg_match('/charset=([a-zA-Z0-9-_]+)/i', $this->html, $matches)) {
            $this->encoding=trim($matches[1]);
            if(strcasecmp($this->encoding, 'utf-8') != 0) {
                $this->html=iconv($this->encoding, 'UTF-8//IGNORE', $this->html);
            }
        }

        // Check if the author doesn't want to share
        if (preg_match('/<!-- *jnm_no_share *-->/', $this->html)) {
            $this->ban = array();
            $this->ban['comment'] = _('el autor no desea que se envíe el artículo, respeta sus deseos');
            syslog(LOG_NOTICE, "Joneame, noshare ($current_user->user_login): $url");
            return false;
        }

        // Now we analyse the html to find links to banned domains
        // It avoids the trick of using google or technorati
        // Ignore it if the link has a rel="nofollow" to ignore comments in blogs
        if (!preg_match('/content="[^"]*(vBulletin|phpBB)/i', $this->html)) {
            preg_match_all('/(< *meta +http-equiv|< *script|< *iframe|< *frame[^<]*>|< *h[0-9][^<]*>[^<]*<a|window\.|document.\|parent\.|location\.|top\.|self\.)[^>]*(href|url|action|src|location|replace) *[=\(] *[\'"]{0,1}https*:\/\/[^\s "\'>]+[\'"\;\)]{0,1}[^>]*>/i', $this->html, $matches);
        } else {
            preg_match_all('/(< *a|<* meta +http-equiv|<* script|<* iframe|<* frame[^<]*>|window\.|document.\|parent\.|location\.|top\.|self\.)[^>]*(href|url|action|src|location|replace) *[=\(] *[\'"]{0,1}https*:\/\/[^\s "\'>]+[\'"\;\)]{0,1}[^>]*>/i', $this->html, $matches);
        }

        $check_counter = 0;
        $second_level = preg_quote(preg_replace('/^(.+\.)*([^\.]+)\.[^\.]+$/', "$2", $url_components['host']));
        foreach ($matches[0] as $match) {
            if (!preg_match('/<a.+rel=.*nofollow.*>/', $match)) {
                preg_match('/(href|url|action|src|location|replace) *[=\(] *[\'"]{0,1}(https*:\/\/[^\s "\'>]+)[\'"\;\)]{0,1}/i', $match, $url_a);
                $embeded_link  = $url_a[2];
                $new_url_components = @parse_url($embeded_link);
                if (! empty($embeded_link) && $check_counter < 5 && ! $checked_links[$new_url_components['host']]) {
                    if (! preg_match("/$second_level\.[^\.]+$/", $new_url_components['host']) ) {
                        $check_counter++;
                    }
                    $checked_links[$new_url_components['host']] = true;
                    if (!$this->check_url($embeded_link, false) && $this->banned) return false;
                }
            }
        }

        // The URL has been checked
        $this->valid = true;

        if(preg_match('/<title[^<>]*>([^<>]*)<\/title>/si', $this->html, $matches)) {
            $url_title=clean_text($matches[1]);
            if (mb_strlen($url_title) > 3) {
                $this->url_title=$url_title;
            }
        }
        return true;
    }


    function trackback() {
        // Now detect trackbacks
        if (preg_match('/trackback:ping="([^"]+)"/i', $this->html, $matches) ||
            preg_match('/trackback:ping +rdf:resource="([^>]+)"/i', $this->html, $matches) ||
            preg_match('/<trackback:ping>([^<>]+)/i', $this->html, $matches)) {
            $trackback=trim($matches[1]);
        } elseif (preg_match('/<a[^>]+rel="trackback"[^>]*>/i', $this->html, $matches)) {
            if (preg_match('/href="([^"]+)"/i', $matches[0], $matches2)) {
                $trackback=trim($matches2[1]);
            }
        } elseif (preg_match('/<a[^>]+href=[^>#]+>[^>]*trackback[^>]*<\/a>/i', $this->html, $matches)) {
            if (preg_match('/href="([^"]+)"/i', $matches[0], $matches2)) {
                $trackback=trim($matches2[1]);
            }
        } elseif (preg_match('/(http:\/\/[^\s#]+\/trackback\/*)/i', $this->html, $matches)) {
            $trackback=trim($matches[0]);
        }

        if (!empty($trackback)) {
            $this->trackback = clean_input_url($trackback);
            return true;
        }
        return false;
    }

    function pingback() {
        $url_components = @parse_url($this->url);

        // Now we use previous pingback or detect it
        if ((!empty($url_components['query']) || preg_match('|^/.*[\.-/]+|', $url_components['path']))) {
            if (!empty($this->pingback)) {
                $trackback = $this->pingback;
            } elseif (preg_match('/<link[^>]+rel="pingback"[^>]*>/i', $this->html, $matches)) {
                if (preg_match('/href="([^"]+)"/i', $matches[0], $matches2)) {
                    $trackback='ping:'.trim($matches2[1]);
                }
            }
        }

        if (!empty($trackback)) {
            $this->trackback = clean_input_url($trackback);
            return true;
        }

        return false;
    }

    function has_rss() {
        return preg_match('/<link[^>]+(text\/xml|application\/rss\+xml|application\/atom\+xml)[^>]+>/i', $this->html);
    }

    function create_blog_entry() {
        require_once(mnminclude.'blog.php');
        $blog = new Blog();
        $blog->analyze_html($this->url, $this->html);
        if(!$blog->read('key')) {
            $blog->store();
        }
        $this->blog=$blog->id;
        $this->type=$blog->type;
    }

    function type() {
        if (empty($this->type)) {
            if ($this->blog > 0) {
                require_once(mnminclude.'blog.php');
                $blog = new Blog();
                $blog->id = $this->blog;
                if($blog->read()) {
                    $this->type=$blog->type;
                    return $this->type;
                }
            }
            return 'normal';
        }
        return $this->type;
    }

    function store() {
        global $db, $current_user;

        $link_url = $db->escape($this->url);
        $link_uri = $db->escape($this->uri);
        $link_url_title = $db->escape($this->url_title);
        $link_title = $db->escape($this->title);
        $link_tags = $db->escape($this->tags);
        $link_content = $db->escape(normalize_smileys($this->content));

        $link_thumb = $db->escape($this->thumb);
        $link_thumb_x = intval($this->thumb_x);
        $link_thumb_y = intval($this->thumb_y);
        $link_thumb_status = $db->escape($this->thumb_status);
        $db->query("LOCK TABLES links WRITE");
        $this->store_basic();
        $db->query("UPDATE links set link_url='$link_url', link_uri='$link_uri', link_url_title='$link_url_title', link_title='$link_title', link_content='$link_content', link_tags='$link_tags', link_thumb='$link_thumb', link_thumb_x=$link_thumb_x, link_thumb_y=$link_thumb_y, link_thumb_status='$link_thumb_status' WHERE link_id=$this->id");
        $db->query("UNLOCK TABLES");
    }

    function store_basic() {
        global $db, $current_user, $globals;

        if(!$this->date)
            $this->date=$globals['now'];
        $link_author = $this->author;
        $link_blog = $this->blog;
        $link_status = $db->escape($this->status);
        $link_votes = $this->votes;
        $link_negatives = $this->negatives;
        $link_anonymous = $this->anonymous;
        $link_aleatorios_positivos = $this->aleatorios_positivos;
        $link_aleatorios_negativos = $this->aleatorios_negativos;
        $link_comments = $this->comments;
        $link_karma = $this->karma;
        $link_votes_avg = $this->votes_avg;
        $link_randkey = $this->randkey;
        $link_category = $this->category;
        $link_votos_permitidos = intval($this->votos_permitidos);
        $link_comentarios_permitidos = intval($this->comentarios_permitidos);
        $link_date = $this->date;
        $link_sent_date = $this->sent_date;
        $link_sent = $this->sent;
        $link_content_type = $db->escape($this->content_type);
        $link_ip = $db->escape($this->ip);
        $broken_link = $db->escape($this->broken_link);

        if($this->id===0) {
            $db->query("INSERT INTO links (link_author, link_blog, link_status, link_randkey, link_category, link_date, link_sent_date, link_sent, link_votes, link_negatives, link_karma, link_anonymous, link_votes_avg, link_content_type, link_ip, link_broken_link) VALUES ($link_author, $link_blog, '$link_status', $link_randkey, $link_category, FROM_UNIXTIME($link_date), FROM_UNIXTIME($link_sent_date), $link_sent, $link_votes, $link_negatives, $link_karma, $link_anonymous, $link_votes_avg, '$link_content_type', '$link_ip', '0')");
            $this->id = $db->insert_id;
        } else {
            // update
            $db->query("UPDATE links set link_author=$link_author, link_blog=$link_blog, link_status='$link_status', link_randkey=$link_randkey, link_category=$link_category, link_date=FROM_UNIXTIME($link_date), link_sent_date=FROM_UNIXTIME($link_sent_date), link_votes=$link_votes,link_aleatorios_positivos=$link_aleatorios_positivos,link_aleatorios_negativos=$link_aleatorios_negativos, link_votos_permitidos=$link_votos_permitidos, link_comentarios_permitidos=$link_comentarios_permitidos, link_sent=$link_sent, link_negatives=$link_negatives, link_comments=$link_comments, link_karma=$link_karma, link_anonymous=$link_anonymous, link_votes_avg=$link_votes_avg, link_content_type='$link_content_type', link_ip='$link_ip', link_broken_link='$broken_link' WHERE link_id=$this->id");

        }

        if ($this->votes == 1 && $this->negatives == 0 && $this->status == 'queued') {
            // This is a new link, add it to the events, it an additional control
            // just in case the user dind't do the last submit phase and voted later
            log_conditional_insert('link_new', $this->id, $this->author);
        }
    }

    function read_basic($key='id') {
        global $db;

        switch ($key) {
            case 'id':
                $cond = "link_id = $this->id";
                break;
            case 'uri':
                $cond = "link_uri = '$this->uri'";
                break;
            case 'url':
                $cond = "link_url = '$this->url'";
                break;
            default:
                $cond = "link_id = $this->id";
        }

        if(($link = $db->get_row("SELECT link_id, link_karma as karma, link_author, link_status, link_votes, link_negatives, link_anonymous, link_randkey, link_votos_permitidos, link_aleatorios_positivos, link_sent as sent,  UNIX_TIMESTAMP(link_date) as link_ts, link_title FROM links WHERE $cond"))) {

            $this->id=$link->link_id;
            $this->author=$link->link_author;
            $this->status=$link->link_status;
            $this->votes=$link->link_votes;
            $this->negatives=$link->link_negatives;
            $this->anonymous=$link->link_anonymous;
            $this->randkey=$link->link_randkey;
            $this->votos_permitidos=$link->link_votos_permitidos;
            $this->sent = $link->sent;
            $this->date=$link->link_ts;
            $this->title=$link->link_title;
            $this->karma=$link->karma;
            $this->aleatorios_positivos=$link->link_aleatorios_positivos;
            $this->votes_sum = $link->votes + $link->aleatorios_positivos;
            $this->negatives_sum = $link->negatives + $link->aleatorios_negativos;

            $this->read = true;

            return true;
        }
        return false;
    }

        function read($key='id') {
          global $db;
          switch ($key)  {
            case 'id':
                $cond = "link_id = $this->id";
                break;
            case 'uri':
                $cond = "link_uri = '$this->uri'";
                break;
            case 'url':
                $cond = "link_url = '$this->url'";
                break;
            default:
                $cond = "link_id = $this->id";
        }
        if(($result = $db->get_row("SELECT".Link::SQL."WHERE $cond AND user_id=link_author"))) {
            foreach(get_object_vars($result) as $var => $value) $this->$var = $value;
            $this->votes_sum = $this->votes + $this->aleatorios_positivos;
            $this->negatives_sum = $this->negatives + $this->aleatorios_negativos;
            $this->read = true;
            return true;
        }
        $this->read = false;
        return false;
        }

    function duplicates($url) {
        global $db;
        $trimmed = $db->escape(preg_replace('/\/$/', '', $url));
        $list = "'$trimmed', '$trimmed/'";
        if (preg_match('/^http:\/\/www\./', $trimmed)) {
            $link_alternative = preg_replace('/^http:\/\/www\./', 'http://', $trimmed);
        } else {
            $link_alternative = preg_replace('/^http:\/\//', 'http://www.', $trimmed);
        }
        $list .= ", '$link_alternative', '$link_alternative/'";
        $found = $db->get_var("SELECT link_id FROM links WHERE link_url in ($list) AND (link_status not in ('discard', 'abuse') OR link_votes>0) limit 1");
        return $found;
    }

    function print_summary($type='full', $karma_best_comment = 0, $show_tags = true) {
        global $current_user, $globals, $db;

        if(!$this->read)
            return;

        if($this->is_votable()) {
            if ($this->voted === null) $this->md5 = md5($current_user->user_id.$this->id.$this->randkey.$globals['user_ip']);
        }

        if ($globals['base_story_url']) {
            $this->joneame_link = 'https://'.get_server_name().$globals['base_url'].$globals['base_story_url'].'0'.$this->id;
        }

        if ($type != 'preview') {
            if ($type == 'short')
                $this->print_vote_box = true;
            else
                $this->print_vote_box = true;
        } else $this->print_vote_box = true;

        if ($current_user->user_id > 0 && $voto_aleatorio = $this->aleatorio_info()){
            $this->aleatorio_valor = $voto_aleatorio->valor;
            $this->aleatorio_tipo = $voto_aleatorio->aleatorio;
        }

        $this->is_nsfw();

        $this->permalink = $this->get_permalink();
        $this->get_shake_box_class();
        $this->discarded = $this->is_discarded();
        $this->has_thumb = $this->has_thumb();
        $this->short_permalink = $this->get_short_permalink();
        $this->relative_permalink = $this->get_relative_permalink();
        $this->show_tags = $show_tags;
        $this->can_negative_vote = $this->negatives_allowed();
        $this->warned = false;
        $this->tiene_negativos = false;

        if(!$this->sent) $this->box_class = 'mnm-queued';

        /* Edit */
        $this->is_editable = $this->is_editable();
        $this->edit_remaining_time = round($globals['edicion_historias_usuario'] / 60);

        /* URL and Content */
        $this->url_str =  htmlentities(preg_replace('/^https*:\/\//', '', txt_shorter($this->url)));
        $this->url_domain = txt_shorter(parse_url($this->url, PHP_URL_HOST));
        if (strpos($this->url_domain, 'www.') === 0) {
            $this->url_domain = substr($this->url_domain, 4);
        }
        $this->content_str = text_to_html(put_smileys($this->content, 'links'));

        /* neiko: chapado */

        /*Votos totales positivos*/
        /* $votos_totales = $db->get_var("SELECT count(*) FROM votes WHERE vote_link_id=$this->id and vote_type='links' and vote_value >= 0"); */

        // votos negativos no-aleatorios
        /* $votos_negativos  = $db->get_var("SELECT count(*) FROM votes WHERE vote_link_id=$this->id and vote_type='links' and vote_aleatorio='normal' and vote_value < 0"); */

        if ($this->votes_enabled && !$this->discarded && $votos_negativos > 1 && $votos_negativos > $votos_totales/6)
            $this->tiene_negativos = true;

        if ($this->status == 'abuse') {
            $this->warned = true;
        } else if ($this->status == 'duplicated') {
            require_once(mnminclude.'dupe.class.php');
            $dupe = new Dupe;
            $dupe->id = $this->id;
            $dupe->get();

            $this->duplicate_link = $dupe->duplicate;
            $this->warned = true;
        }

        if ($this->status == 'published' && $this->tiene_negativos && !$this->nsfw ) {
            $this->warned = true;
        }

        // historias NSFW
        if ($this->nsfw && $this->status != 'duplicated') {
            $this->warned = true;
        }

        if ($this->votes_enabled && $this->discarded && $this->tiene_negativos && !$this->is_nsfw && $this->status != 'published') {

            $negatives = $db->get_row("select vote_value, count(vote_value) as count from votes where vote_type='links' and vote_link_id=$this->id and vote_value < 0 and vote_aleatorio='normal' group by vote_value order by count desc limit 1");

            $this->negative_value = $negatives->vote_value;
            $this->warned = true;
        }

        if ($karma_best_comment > 0 && ($best_comment = $db->get_row("select comment_id, comment_order, comment_content from comments where comment_link_id = $this->id and comment_karma > $karma_best_comment order by comment_karma desc limit 1"))) {
                $this->best_comment = $db->get_row("select SQL_CACHE comment_id, comment_order, substr(comment_content, 1, 225) as content from comments where comment_link_id = $this->id and comment_karma > $karma_best_comment and comment_votes > 0 order by comment_karma desc limit 1");
        } else {
            $this->best_comment  = FALSE;
        }

        $this->total_votes = $this->votes+$this->anonymous+$this->aleatorios_positivos;

        $this->comentarios = '';
        $this->comments_mess = $this->comments > 1 ? ' ' . _('comentarios') : ' ' . _('comentario');

        if ($this->comments == 0)
            $this->comments_mess = _('no hay comentarios');
        else    $this->comentarios = $this->comments;

        $var = compact('type');
        $var['self'] = $this;

        if ($type != 'preview') {
            $shakebox = Haanga::Load("link_shake_box.html", $var, true);
            $var['shakebox'] = $shakebox;
        }

        $var['warning'] = Haanga::Load("link_warn.html", $var, true);

        Haanga::Load("link_summary.html", $var);

    }

    function get_shake_box_class() {

        switch ($this->status) {
            case 'queued':
                $this->box_class = 'mnm-queued';
                break;
            case 'abuse':
                $this->box_class = 'jnm-abuse';
                break;
            case 'autodiscard':
            case 'discard':
            case 'duplicated':
                $this->box_class = 'mnm-discarded';
                break;
            case 'published':
            default:
                $this->box_class = 'mnm-published';
                break;
        }
    }

    // nos dice si el voto es aleatorio o normal
    function aleatorio_info() {
        global $db, $current_user;

        if ($current_user->user_id == 0) return false;

        return $db->get_row("SELECT vote_aleatorio as aleatorio, vote_value as valor FROM votes WHERE vote_type = 'links' AND vote_link_id = $this->id AND vote_user_id = $current_user->user_id LIMIT 1");
    }

    function vote_exists($user) {

        require_once(mnminclude.'votes.php');
        $vote = new Vote;
        $vote->user=$user;
        $vote->link=$this->id;
        $vote->type = 'links';
        return $vote->exists();     //devuelve el valor del voto si existe, y false si no existe
    }

    function votes($user) {
        require_once(mnminclude.'votes.php');

        $vote = new Vote;
        $vote->user=$user;
        $vote->link=$this->id;
        return $vote->count();
    }

    function aleatorios_count(){

        return $this->aleatorios_positivos + $this->aleatorios_negativos;

    }

    function insert_vote($value) {
        global $db, $current_user;
        require_once(mnminclude.'votes.php');

        $vote = new Vote;
        $vote->user=$current_user->user_id;
        $vote->link=$this->id;

        if ($vote->exists()) return false;

        if (!$this->insert_aleatorio){
            // For karma calculation
            if ($value < 0)
                $karma_value = $value;
            else if ($this->status != 'published')
                $karma_value = $value;
            else
                $karma_value = 0;

            $vote->aleatorio = false;

            if ($karma_value < 0) $user_karma = round($current_user->user_karma);
        } else {

            $karma_value = $vote->get_aleatorio_value();
            $vote->aleatorio = true;
        }

        $vote->value=$karma_value;


        if($vote->insert()) {
            /* Aumentar contador aleatorios*/
            if ($this->insert_aleatorio && $vote->value >= 0) $db->query("update links set link_aleatorios_positivos=link_aleatorios_positivos+1 where link_id = $this->id");
            else if ($this->insert_aleatorio && $vote->value < 0) $db->query("update links set link_aleatorios_negativos=link_aleatorios_negativos+1 where link_id = $this->id");

            if ($vote->value < 0) {
                if (!$this->insert_aleatorio) /* Aumenta contador y carisma negativos */
                $db->query("update links set link_negatives=link_negatives+1, link_karma=link_karma-$user_karma $link_aleatorios where link_id = $this->id"); /* Reduce karma negativo */
                else $db->query("update links set link_karma=link_karma-$user_karma where link_id = $this->id");
            }  else { /* Aumenta contador y carisma positivos */
                if ($current_user->user_id > 0 && !$this->insert_aleatorio)  $db->query("update links set link_votes = link_votes+1, link_karma=link_karma+$karma_value where link_id = $this->id");  /*Carisma +*/
                else if ($current_user->user_id > 0 && $this->insert_aleatorio)  $db->query("update links set link_karma=link_karma+$karma_value where link_id = $this->id"); /* Anonimos */
                else if ($current_user->user_id == 0) $db->query("update links set link_anonymous = link_anonymous+1, link_karma=link_karma+$karma_value where link_id = $this->id");
            }
            $new = $db->get_row("select link_votes, link_anonymous, link_negatives, link_karma, link_aleatorios_positivos, link_aleatorios_negativos from links where link_id = $this->id");
            $this->votes = $new->link_votes;
            $this->anonymous = $new->link_anonymous;
            $this->negatives = $new->link_negatives;
            $this->karma = $new->link_karma;
            $this->aleatorios_positivos = $new->link_aleatorios_positivos;
            $this->aleatorios_negativos = $new->link_aleatorios_negativos;
            return true;
        }
        return false;
    }

    function publish() {
        global $globals;
        if(!$this->read) $this->read_basic();
        $this->published_date = $globals['now'];
        $this->date = $globals['now'];
        $this->status = 'published';
        $this->store_basic();
    }

    function update_comments() {
        global $db;
        $this->comments = $db->get_var("SELECT count(*) FROM comments WHERE comment_link_id = $this->id");
        $db->query("update links set link_comments = $this->comments where link_id = $this->id");
    }

    function is_discarded() {
        return $this->status == 'discard' || $this->status == 'abuse' ||  $this->status == 'autodiscard' || $this->sent == 0 || $this->status == 'duplicated' ;
    }

    function is_editable() {
        global $current_user, $globals;

        if($current_user->user_id) {
            // es el usuario, dispone de los primeros 15 minutos
            if(($this->author == $current_user->user_id
                && $this->status != 'published'
                && $this->status != 'abuse'
                && $this->status != 'autodiscard'
                && $globals['now'] - $this->sent_date < $globals['edicion_historias_usuario'])
            // es "special" , dispone a partir de los 15 minutos, hasta pasados 10
            || ($this->author != $current_user->user_id
                && $current_user->especial
                && $this->status != 'abuse'
                && $this->status != 'autodiscard'
                // && $this->status != 'published'
                && $globals['now'] - $this->sent_date > $globals['edicion_historias_usuario'] + 300
                && $globals['now'] - $this->sent_date < $globals['edicion_historias_usuario'] + 600)
            // es administrador
            || $current_user->admin) {
                return true;
            }
        }
        return false;
    }

    function get_editable_teaser() {
        global $current_user, $globals;

        if ($current_user->admin)
            $iddqd = ' iddqd';

        $editable_teaser = '<span class="n-edit'.$iddqd.'">';
        if ($current_user->admin)
            $editable_teaser .= 'admin';
        elseif ($this->author == $current_user->user_id)
            $editable_teaser .= calc_remaining_edit_time($this->sent_date, $globals['edicion_historias_usuario']);
        elseif ($this->author != $current_user->user_id && $current_user->especial)
            $editable_teaser .= 'special';
        $editable_teaser .= '</span>';

        return $editable_teaser;
    }

    function is_votable() {
        global $globals;

        if(isset($globals['bot']) && $globals['bot'] || $this->status == 'abuse' || $this->status == 'duplicated' || $this->status == 'autodiscard' ||
            ($globals['time_enabled_votes'] > 0 && $this->date < $globals['now'] - $globals['time_enabled_votes']) || !$this->votos_permitidos)  {
            $this->votes_enabled = false;
        } else {
            $this->votes_enabled = true;
        }
        return $this->votes_enabled;
    }

    function negatives_allowed() {
        global $globals, $current_user;

        return $current_user->user_id > 0
            && $this->author != $current_user->user_id
            && $this->status != 'abuse'
            && $this->status != 'autodiscard'
            && $current_user->user_karma >= $globals['min_karma_for_negatives']
            && ($this->status != 'published'
                || $this->status == 'published'
                && ($this->date > $globals['now'] - 7200
                    || $this->warned)
            );
    }

    function get_uri() {
        global $db, $globals;
        $seq = 0;
        require_once(mnminclude.'uri.php');
        $new_uri = $base_uri = get_uri($this->title);
        while ($db->get_var("select count(*) from links where link_uri='$new_uri' and link_id != $this->id") && $seq < 20) {
            $seq++;
            $new_uri = $base_uri . "-$seq";
        }
        // In case we tried 20 times, we just add the id of the article
        if ($seq >= 20) {
            $new_uri = $base_uri . "-$this->id";
        }
        $this->uri = $new_uri;
    }

    function get_short_permalink() {
        global $globals;
        if ($globals['base_story_url']) {
            return $globals['base_url'].$globals['base_story_url'].'0'.$this->id;
        } else {
            return $this->get_relative_permalink();
        }
    }

    function get_relative_permalink() {
        global $globals;
        if (!empty($this->uri) && !empty($globals['base_story_url']) ) {
            return $globals['base_url'] . $globals['base_story_url'] . $this->uri;
        } else {
            return $globals['base_url'] . 'historia.php?id=' . $this->id;
        }
    }

    function get_permalink() {
        return $this->get_relative_permalink();
    }

    function get_trackback() {
        global $globals;
        return "https://".get_server_name().$globals['base_url'].'trackback.php?id='.$this->id;
    }

    function get_status_text($status = false) {
        global $current_user, $linkres;
        if (!$status) $status = $this->status;
        switch ($status) {
            case ('abuse'):
                return _('abuso');
            case ('discard'):
                return _('descartada');
            case ('queued'):
                return _('pendiente');
            case ('published'):
                return _('publicada');
            case ('duplicated'):
                return _('duplicada');
            case ('autodiscard'):

            if ($current_user->user_id == $linkres->author)
                return _('autodescartada');

            if ((($current_user->admin)) && ($current_user->user_id != $linkres->author))
                return _('descartada');
        }
        return $status;
    }

    function print_content_type_buttons($link_title = false) {
        global $globals;
        // Is it an image or video?
        switch ($this->content_type) {
            case 'image':
            case 'video':
            case 'text':
                $type[$this->content_type] = 'checked="checked"';
                break;
            default:
                $type['text'] = 'checked="checked"';
        }

        // Not Safe For Work (NSFW) y Sólo para adultos (+18)

        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        if (stripos($link_title , '[NSFW]')) {
            echo '<input type="checkbox" checked="checked" name="sec[0]" value="text" id="nsfw"/>';
        } else {
            echo '<input type="checkbox" name="sec[0]" value="text" id="nsfw"/>';
        }

        echo '&nbsp;<label for="nsfw">'._('NSFW').'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        if (stripos($link_title , '[+18]')) {
            echo '<input type="checkbox" checked="checked" name="sec[1]" value="text" id="mas18"/>';
        } else {
            echo '<input type="checkbox" name="sec[1]" value="text" id="mas18"/>';
        }

        echo '&nbsp;<label for="mas18">'._('+18').'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '<input type="radio" '.$type['text'].' name="type" value="text" id="text"/>';
        echo '&nbsp;<label for="text">'._('texto').'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

        echo '<input type="radio" '.$type['image'].' name="type" value="image" id="image"/>';
        echo '&nbsp;<label for="image"><img src="'.get_cover_pixel().'" class="icon image media-icon" alt="'._('¿es una imagen?').'" title="'._('¿es una imagen?').'" /></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

        echo '<input type="radio" '.$type['video'].' name="type" value="video" id="video"/>';
        echo '&nbsp;<label for="video"><img src="'.get_cover_pixel().'" class="icon video media-icon" alt="'._('¿es un vídeo?').'" title="'._('¿es un vídeo?').'" /></label>';
    }

    function read_content_type_buttons($type) {
        switch ($type) {
            case 'image':
                $this->content_type = 'image';
                break;
            case 'video':
                $this->content_type = 'video';
                break;
            case 'text':
            default:
                $this->content_type = 'text';
        }
    }

    // Thumbnails management
    function get_thumb($debug = false) {
        global $globals;
        require_once(mnminclude.'webimages.php');
        require_once(mnminclude.'blog.php');
        $site = false;

        if (empty($this->url))
            if (!$this->read()) return false;

        $blog = new Blog();
        $blog->id = $this->blog;

        if ($blog->read())
            $site = $blog->url;

        $this->image_parser = new HtmlImages($this->url, $site);
        $this->image_parser->debug = $debug;
        $this->image_parser->referer = $this->get_permalink();
        $img = $this->image_parser->get();
        $this->thumb_status = 'checked';
        $this->thumb = '';

        if ($img) {
            $filepath = $globals['thumbs_dir'];
            mkdir($filepath);
            $l1 = intval($this->id / 100000);
            $l2 = intval(($this->id % 100000) / 1000);
            $filepath .= "/$l1";
            mkdir($filepath);
            chmod($filepath, 0777);
            $filepath .= "/$l2";
            mkdir($filepath);
            chmod($filepath, 0777);
            $filepath .= "/$this->id.jpg";
            if ($img->type == 'local') {
                $scaled_img = $img->scale($globals['thumb_size']);
                if ($scaled_img) {
                    $img->image = $scaled_img->image;
                }
                if ($img->save($filepath)) {
                    chmod($filepath, 0777);

                    $this->thumb = $globals['thumbs_url'];
                    $this->thumb .= "/$l1/$l2/$this->id.jpg";
                    $this->thumb_x = $img->x;
                    $this->thumb_y = $img->y;
                    $this->thumb_status='local';
                    syslog(LOG_NOTICE, "Joneame, new thumbnail $img->url to " . $this->get_permalink());

                    if ($debug)
                        echo "<!-- Joneame, new thumbnail $img->url -->\n";
                } else {
                    $this->thumb_status = 'error';
                    if ($debug)
                        echo "<!-- Joneame, error saving thumbnail ".$this->get_permalink()." -->\n";

                    syslog(LOG_NOTICE, "Joneame, error saving thumbnail $img->url for " . $this->get_permalink());
                }
            }
            if ($img->video)
                $this->content_type = 'video';
        }
        $this->store_thumb();
        return $this->has_thumb();
    }

    function store_thumb() {
        global $db;
        $this->thumb = $db->escape($this->thumb);
        $db->query("update links set link_content_type = '$this->content_type', link_thumb = '$this->thumb', link_thumb_x = $this->thumb_x, link_thumb_y = $this->thumb_y, link_thumb_status = '$this->thumb_status' where link_id = $this->id");
    }

    function has_thumb() {
        return $this->thumb && $this->thumb_x > 0 && $this->thumb_y > 0;
    }

    function is_nsfw () {

        if (stripos($this->title,'[NSFW]') == TRUE || stripos($this->title,'[+18]') == TRUE) $this->nsfw = true;
        else $this->nsfw = false;

        return $this->nsfw;
    }

    function insert_user_click(){
        global $db;
        $db->query("INSERT LOW_PRIORITY INTO link_clicks (id, counter) VALUES ($this->id,1) ON DUPLICATE KEY UPDATE counter=counter+1");
        setcookie('v', implode('x', $visited));

    }

    function id_visited() {

        if (! isset($_COOKIE['l_v']) || ! ($visited = preg_split('/x/', $_COOKIE['l_v'], 0, PREG_SPLIT_NO_EMPTY)) ) {
        $visited = array();
        $found = false;

        } else {
        $found = array_search($this->id, $visited);
        if (count($visited) > 10) {
            array_shift($visited);
        }
        if ($found !== false) {
            unset($visited[$found]);
        }

        }

        $visited[] = $this->id;
        $valor = implode('x', $visited);

        setcookie('l_v', $valor);
        return $found !== false;

    }

    function update_visitors(){
        global $db, $globals;

              if ( isset($globals['bot']) && !$globals['bot']
                 // && $globals['click_counter']
                  && $this->ip != $globals['user_ip']
                  && $this->id_visited() === false) {
                    $db->query("INSERT LOW_PRIORITY INTO link_visits (id, counter) VALUES ($this->id,1) ON DUPLICATE KEY UPDATE counter=counter+1");
            $this->visits = $db->get_var("SELECT counter FROM link_visits WHERE id=$this->id");
                }

        }

}
