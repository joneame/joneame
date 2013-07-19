<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Comment {
	var $id = 0;
	var $randkey = 0;
	var $author = 0;
	var $link = 0;
	var $date = false;
	var $order = 0;
	var $votes = 0;
	var $voted = false;
	var $karma = 0;
	var $content = '';
	var $read = false;
	var $ip = '';

	const SQL = " SQL_NO_CACHE comment_id as id, comment_type as type, comment_user_id as author, user_login as username, user_email as email, user_karma as user_karma, user_level as user_level, comment_randkey as randkey, comment_link_id as link, comment_order as c_order, comment_votes as votes, comment_karma as karma, comment_ip as ip, user_avatar as avatar, comment_content as content, UNIX_TIMESTAMP(comment_date) as date, favorite_link_id as favorite, vote_value as voted FROM comments
    INNER JOIN users on (user_id = comment_user_id)
    LEFT JOIN favorites ON (@user_id > 0 and favorite_user_id =  @user_id and favorite_type = 'comment' and favorite_link_id = comment_id)
    LEFT JOIN votes ON (comment_date > @enabled_votes and @user_id > 0 and vote_type='comments' and vote_link_id = comment_id and vote_user_id = @user_id)";

	const SQL_BASIC = "  comment_id as id, comment_type as type, comment_votes as votes, comment_user_id as author, user_login as username, comment_karma as karma, user_avatar as avatar, comment_content as content, UNIX_TIMESTAMP(comment_date) as date FROM comments INNER JOIN users on (user_id = comment_user_id)";
  

	static function from_db($id) {
		global $db;
		if(($result = $db->get_object("SELECT".Comment::SQL."WHERE comment_id = $id", 'Comment'))) {
		    $result->order = $result->c_order; // Order is a reserved word in SQL
		    $result->read = true;
		    if($result->order == 0) $result->update_order();
		    return $result;
		}
		return false;
	}

	static function update_read_conversation() {
	       	global $db, $globals, $current_user;

		$key = 'c_last_read';

		if (!$current_user->user_id ) return false;
		$time = $globals['now'];
		$previous = (int) $db->get_var("select pref_value from prefs where pref_user_id = $current_user->user_id and pref_key = '$key'");

		if ($time > $previous) {
	      
		    $db->query("delete from prefs where pref_user_id = $current_user->user_id and pref_key = '$key'");
		    $db->query("insert into prefs set pref_user_id = $current_user->user_id, pref_key = '$key', pref_value = $time");
	 
		}
		return true;

        }

	function store() {
		require_once(mnminclude.'log.php');
		global $db, $current_user, $globals;

		if(!$this->date) $this->date=$globals['now'];
		$comment_author = $this->author;
		$comment_link = $this->link;
		// Si el usuario tiene más de <s>20</s> 22 de carisma, el comentario vale 22 siempre --neiKo
		if ($current_user->user_karma > 22)
			$initial_karma = 22;
		else
			$initial_karma = $current_user->user_karma;
		$comment_karma = $this->karma;
		$comment_date = $this->date;
		$comment_randkey = $this->randkey;
		$comment_content = $db->escape(normalize_smileys(clear_whitespace(clean_lines($this->content))));
		if ($this->type == 'admin') $comment_type = 'admin';
		elseif($this->type=='especial') $comment_type = 'especial';
		else $comment_type = 'normal';
		if($this->id===0) {
			$this->ip = $db->escape($globals['user_ip']);
			$db->query("INSERT INTO comments (comment_user_id, comment_link_id, comment_type, comment_karma, comment_ip, comment_date, comment_randkey, comment_content) VALUES ($comment_author, $comment_link, '$comment_type', $initial_karma, '$this->ip', FROM_UNIXTIME($comment_date), $comment_randkey, '$comment_content')");
			$this->id = $db->insert_id;

			// Insert comment_new event into logs
			log_insert('comment_new', $this->id, $current_user->user_id);
		} else {
			$db->query("UPDATE comments set comment_user_id=$comment_author, comment_link_id=$comment_link, comment_type='$comment_type', comment_karma=$comment_karma, comment_ip = '$this->ip', comment_date=FROM_UNIXTIME($comment_date), comment_randkey=$comment_randkey, comment_content='$comment_content' WHERE comment_id=$this->id");
			// Insert comment_edit event into logs
			log_conditional_insert('comment_edit', $this->id, $current_user->user_id, 60);
		}
		$this->update_order();
                $this->update_conversation();
	}

	function update_order() {
		global $db;

		if ($this->id == 0 || $this->link == 0) return false;
		$order = intval($db->get_var("select count(*) from comments where comment_link_id=$this->link and comment_id < $this->id"))+1;
		if ($order != $this->order) {
			$this->order = $order;
			$db->query("update comments set comment_order=$this->order where comment_id=$this->id");
		}
		return $this->order;
	}

     
	function read() {
        	global $db;

		$id = $this->id;

		if(($result = $db->get_row("SELECT".Comment::SQL."WHERE comment_id = $id"))) {

		    foreach(get_object_vars($result) as $var => $value) $this->$var = $value;

		    $this->order = $this->c_order; // Order is a reserved word in SQL
		    $this->read = true;

		    if($this->order == 0) $this->update_order();
		    return true;

		}

		$this->read = false;
		return false;
        }

	function read_basic() {
        	global $db;

		$id = $this->id;

		if(($result = $db->get_row("SELECT".Comment::SQL_BASIC."WHERE comment_id = $id"))) {

		    foreach(get_object_vars($result) as $var => $value) $this->$var = $value;
		    $this->read = true;

		    return true;

		}

		$this->read = false;
		return false;
        }

	function print_summary($link=false, $length = 0, $single_link=true) {
		echo $this->return_summary($link, $length, $single_link);
	}

	function insert_vote() {
		global $current_user;
		require_once(mnminclude.'votes.php');
		$vote = new Vote;
		$vote->user = $current_user->user_id;
		$vote->type='comments';
		$vote->link=$this->id;
		if ($vote->exists()) {
			return false;
		}
		$vote->value = $current_user->user_karma;
		if($vote->insert()) return true;
		return false;
	}

	function return_text($length = 0, $single_link=true) {
		global $current_user, $globals;

		if (($this->author == $current_user->user_id &&
			$globals['now'] - $this->date < $globals['comment_edit_time'])
			|| $current_user->user_level == 'god') {

				if ($current_user->user_level == 'god')
					$iddqd = ' iddqd';

				$expand = '&nbsp;&nbsp;<a href="javascript:abrirEditar(\'edit_comment.php\',\'edit_comment\',\'ccontainer-'.$this->id.'\',0,'.$this->id.')" title="'._('editar comentario').'"><span class="c-edit'.$iddqd.'">'.calc_remaining_edit_time($this->date, $globals['comment_edit_time']).'</span></a>';

		} elseif ($length > 0 && mb_strlen($this->content) > $length + $length / 2) {
			$this->content = preg_replace('/&\w*$/', '', mb_substr($this->content, 0 , $length));
			$expand = '...&nbsp;&nbsp;' .

				'<a href="javascript:obtener(\'mostrar_comentario.php\',\'comment\',\'cid-'.$this->id.'\',0,'.$this->id.')" title="'._('resto del comentario').'">»&nbsp;'._('ver todo el comentario').'</a>';
		}

		return put_smileys($this->put_comment_tooltips(save_text_to_html($this->content, 'comments'))) . $expand;
	}

	function print_text($length = 0, $single_link=true) {
		echo Comment::return_text($length, $single_link); // hack to workaround something i didn't break
	}

	function username() {
		global $db;

		$this->username = $db->get_var("SELECT user_login FROM users WHERE user_id = $this->author");
		return $this->username;
	}

	// Add calls for tooltip javascript functions
	function put_comment_tooltips($str) {
		return preg_replace('/(^|[\(,;\.\s])#([0-9]+)/', "$1<a class='tt' href=\"".$this->link_permalink."/000$2\" onmouseover=\"return tooltip.c_show(event, 'id', '$2', '".$this->link."');\" onmouseout=\"tooltip.hide(event);\"  onclick=\"tooltip.hide(this);\">#$2</a>", $str);
	}

	function same_text_count($min=30) {
		global $db;
		// WARNING: $db->escape(clean_lines($comment->content)) should be the sama as in libs/comment.php (unify both!)
		return (int) $db->get_var("select count(*) from comments where comment_user_id = $this->author  and comment_date > date_sub(now(), interval $min minute) and comment_content = '".$db->escape(clean_lines($this->content))."'");
	}

	function same_links_count($min=30) {
		global $db;
		$count = 0;
		$localdomain = preg_quote(get_server_name(), '/');
		preg_match_all('/([\(\[:\.\s]|^)(https*:\/\/[^ \t\n\r\]\(\)\&]{5,70}[^ \t\n\r\]\(\)]*[^ .\t,\n\r\(\)\"\'\]\?])/i', $this->content, $matches);
		foreach ($matches[2] as $match) {
			$link=clean_input_url($match);
			$components = parse_url($link);
			if (! preg_match("/.*$localdomain$/", $components['host'])) {
				$link = '//'.$components['host'].$components['path'];
				$link=preg_replace('/(_%)/', "\$1", $link);
				$link=$db->escape($link);
				$count = max($count, (int) $db->get_var("select count(*) from comments where comment_user_id = $this->author and comment_date > date_sub(now(), interval $min minute) and comment_content like '%$link%'"));
			}
		}
		return $count;
	}
    function update_conversation() {
        global $db, $globals;

        $db->query("delete from conversations where conversation_type='comment' and conversation_from=$this->id");
        $orders = array();
        if (preg_match_all('/(^|[\(,;\.\s])#(\d+)/', $this->content, $matches)) {
            foreach ($matches[2] as $order) {
                $orders[$order] += 1;
            }
        }
        foreach ($orders as $order => $val) {
            if ($order == 0) {
                $to = $db->get_row("select 0 as id, link_author as user_id from links where link_id = $this->link");
            } else {
                $to = $db->get_row("select comment_id as id, comment_user_id as user_id from comments where comment_link_id = $this->link and comment_order=$order and comment_type != 'admin'");
            }
            if ($to && $to->user_id != $this->author) {

                if (!$this->date) $this->date = time();
                $db->query("insert into conversations (conversation_user_to, conversation_type, conversation_time, conversation_from, conversation_to) values ($to->user_id, 'comment', from_unixtime($this->date), $this->id, $to->id)");
            }
        }

    }


    function get_relative_individual_permalink() {
        // Permalink of the "comment page"
        global $globals;
        if ($globals['base_comment_url']) {
            return $globals['base_url'] . $globals['base_comment_url'] . $this->id;
        } else {
            return $globals['base_url'] . 'comment.php?id=' . $this->id;
        }
    }

    function save_from_post($link) {
        global $db, $current_user, $globals;

        $error = '';

        require_once(mnminclude.'ban.php');
        if(check_ban_proxy()) return _('dirección IP no permitida');

        // Check if is a POST of a comment

        if( ! ($link->votes > 0 && $link->date > $globals['now']-$globals['time_enabled_comments']*1.01 && 
                $link->comments < $globals['max_comments'] &&
                intval($_POST['link_id']) == $link->id && $current_user->authenticated && 
                intval($_POST['user_id']) == $current_user->user_id &&
                intval($_POST['randkey']) > 0
                )) {
            return _('comentario o usuario incorrecto');
        }

        if ($current_user->user_karma < $globals['min_karma_for_comments'] && $current_user->user_id != $link->author) {
            return _('carisma demasiado bajo');
        }

        $this->link=$link->id;
        $this->ip = $db->escape($globals['user_ip']);
        $this->randkey=intval($_POST['randkey']);
        $this->author=intval($_POST['user_id']);
        $this->karma=round($current_user->user_karma);
        $this->content=clean_text($_POST['comment_content'], 0, false, 10000);
        // Check if is an admin comment
        if ($current_user->user_level == 'god' && $_POST['type'] == 'admin') {
            $this->type = 'admin';
        } 


        if (mb_strlen($this->content) < 5 || ! preg_match('/[a-zA-Z:-]/', $_POST['comment_content'])) { // Check there are at least a valid char
            return _('texto muy breve o caracteres no válidos');
        }


        // Check the comment wasn't already stored
        $already_stored = intval($db->get_var("select count(*) from comments where comment_link_id = $this->link and comment_user_id = $this->author and comment_randkey = $this->randkey"));
        if ($already_stored) {
            return _('comentario duplicado');
        }

        $this->store();
        $this->insert_vote();
        $link->update_comments();
        return $error;
    }

function return_summary($link=false, $length = 0, $single_link=true) {
	global $current_user, $globals, $db;

	if(!$this->read) return;

 	if (! $link && $this->link > 0) {
		include_once(mnminclude.'link.php');
		$link = new Link;
		$link->id = $this->link;
		$link->read();
		$this->link_object = $link;
        }
	
        require_once(mnminclude.'user.php');

        $this->ignored = ($current_user->user_id > 0 && $this->type != 'admin' && friend_exists($current_user->user_id, $this->author) < 0);
        $this->hidden = ($this->karma < -60 ) || ($this->user_level == 'disabled' && $this->type != 'admin' && $this->type != 'especial');
	$this->is_connected = is_connected($this->author);

        if ($this->hidden || $this->ignored)  {
		$comment_meta_class = 'comment-meta-hidden';
		$comment_class = 'comment-body-hidden';
        } else {
		$comment_meta_class = 'comment-meta';
		$comment_class = 'comment-body';
		if ($this->type == 'admin' || $this->type == 'especial') {
			$comment_meta_class .= ' admin';
			$comment_class .= ' admin';
		} elseif ($this->karma > 110 ) {
			$comment_meta_class .= ' high';
			$comment_class .= ' high';
		}
        }

        $this->link_permalink =  $link->get_relative_permalink();
	
	$var = compact('comment_meta_class','comment_class', 'single_link', 'length');
	$var['self'] = $this;
	Haanga::Load("comment_summary.html", $var);


}

}
