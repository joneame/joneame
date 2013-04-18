<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

//clase de comentarios encuestas

class Opinion {

	var $id=0;
	var $por=0;
	var $user_login = 'unknown';
	var $avatar;
	var $carisma = 0;
	var $votos = 0;
	var $date;

	function read(){
		global $db;

		$id = intval($this->id);

		$opinion = $db->get_row("SELECT polls_comments.*, UNIX_TIMESTAMP(fecha) as date, user_login, user_avatar FROM polls_comments, users where id=$id AND autor=user_id LIMIT 1");

		if ($opinion) {

			$this->id = $opinion->id;
			$this->contenido = $opinion->contenido;
			$this->por = $opinion->autor;
			$this->user_login = $opinion->user_login;
			$this->encuesta_id = $opinion->encuesta_id;
			$this->avatar = $opinion->user_avatar;
			$this->carisma = $opinion->carisma;
			$this->votos = $opinion->votos;
			$this->date = $opinion->date;
			$this->ip = $opinion->ip;
			$this->orden = $opinion->orden;
			
			if($this->orden == 0) $this->update_order();
			
			return true;
		}

		return false;
	}

	function store() {
		require_once(mnminclude.'log.php');
		global $db, $current_user, $globals;

		if(!$this->date) $this->date=$globals['now'];
		$poll_author = $current_user->user_id;
		$poll_id = intval($this->encuesta_id);
		
		//$comment_karma = $this->karma;
		$poll_date = $this->date;
		$poll_content = $db->escape(normalize_smileys(clear_whitespace(clean_lines($this->contenido))));
		
		if($this->id===0) {
			$this->ip = $db->escape($globals['user_ip']);
			$db->query("INSERT INTO polls_comments (autor, encuesta_id, ip, fecha, contenido) VALUES ($poll_author, $poll_id, '$this->ip', FROM_UNIXTIME($poll_date), '$poll_content')");
			$this->id = $db->insert_id;
			$db->query("UPDATE encuestas SET comentarios=comentarios+1 where encuesta_id=$this->encuesta_id");
			// Insert comment_new event into logs
			log_insert('opinion_new', $this->id, $current_user->user_id);
		} else {
			$db->query("UPDATE polls_comments set  contenido='$poll_content' WHERE id=$this->id");
			// Insert comment_edit event into logs
			//log_conditional_insert('comment_edit', $this->id, $current_user->user_id, 60);
		}
		$this->update_order();
              
	}


	function print_opinion(){
		global $current_user, $page, $globals;

		$summaryresponse = '';

		$summaryresponse .= '<li id="ccontainer-'.$this->id.'">';
	     
		$comment_meta_class = 'comment-meta';
		$comment_class = 'comment-body';

		if ($this->carisma > 50 ) {
			$comment_meta_class .= ' high';
			$comment_class .= ' high';
		}

		$summaryresponse .= '<div class="'.$comment_class.'">';

		if (is_connected($this->por))
			$conectado = '<a class="conectado" href="'.$globals['base_url'].'cotillona.php" title="'.$this->user_login.' está actualmente en la cotillona"></a>';
		else    $conectado = '';

		$summaryresponse .= '<a href="'.get_user_uri($this->user_login).'"><img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$this->por.');" onmouseout="tooltip.clear(event);" src="'.get_avatar_url($this->por, $this->avatar, 20).'" width="20" height="20" alt="" title="'.$this->user_login.'" class="comment-avatar"/></a>'.$conectado.'';
	

		$summaryresponse .= '<div class="comment-text">';

		$summaryresponse .= '<a href="'.$this->get_relative_individual_permalink().'"><strong>#'.$this->orden.'</strong></a>';
	   
		$summaryresponse .= '<span id="comment-'.$this->orden.'">';

		$summaryresponse .= '&nbsp;&nbsp;<span id="cid-'.$this->id.'">';
		$summaryresponse .= '</span>';
	       
		// contenido
		$summaryresponse .= $this->return_text();

		$summaryresponse .= '</span></div></div>';

		// The comments info bar
		$summaryresponse .= '<div class="'.$comment_meta_class.'">';
	
		// Check that the user can vote

		$summaryresponse .= '<div class="comment-votes-info">';

		if ($current_user->user_id > 0 && $this->por != $current_user->user_id )
			$summaryresponse .= $this->return_shake_icons();

		$summaryresponse .= _('votos').': <span id="vc-'.$this->id.'">'.$this->votos.'</span>, carisma: <span id="vk-'.$this->id.'">'.$this->carisma.'</span>';

		// Print the votes info (left)
		// Add the icon to show votes
		if ($this->votos > 0 && $this->date > $globals['now'] - 30*86400) { // Show votes if newer than 30 days
			$summaryresponse .= '&nbsp;&nbsp;<a class="fancybox" href="'.$globals['base_url'].'backend/mostrar_votos_comentarios_encuestas.php?id='.$this->id.'">';
			$summaryresponse .= '<img class="icon info" src="'.get_cover_pixel().'" style="margin-top: -2px;" alt="+ info" title="'._('¿quién ha votado?').'"/>';
			$summaryresponse .= '</a>';
		}

	       $summaryresponse .= '&nbsp;&nbsp;<a href="'.$this->get_relative_individual_permalink().'" title="permalink"><img class="icon permalink img-flotante" src="'.get_cover_pixel().'"/></a>';
		
		$summaryresponse .= '</div>';

		// Print comment info (right)
		$summaryresponse .= '<div class="comment-info">';
		
		$summaryresponse .= '<a href="'.get_user_uri($this->user_login).'" title="" id="cauthor-'.$this->orden.'">'.$this->user_login.'</a> ';
	
		
		// Print dates
		if ($globals['now'] - $this->date > 1209600) // 14 days
			$summaryresponse .= _('el').get_date_time($this->date);
		else
			$summaryresponse .= _('hace').' '.txt_time_diff($this->date);

		if ($current_user->user_level == 'god') $summaryresponse .= _(' desde ').$this->ip;

		$summaryresponse .= '</div></div>';
		$summaryresponse .= "</li>\n";

		return $summaryresponse;


	}

	function get_relative_individual_permalink() {
        	global $globals;
        
		if ($globals['base_poll_comment_url']) {
		    return $globals['base_url'] . $globals['base_poll_comment_url'] . $this->id;
		} else {
		    return $globals['base_url'] . 'opinion.php?id=' . $this->id;
		}
        }


	function vote_exists() {
		global $current_user;
		require_once(mnminclude.'votes.php');
		$vote = new Vote;
		$vote->user=$current_user->user_id;
		$vote->type='poll_comment';
		$vote->link=$this->id;
		$this->voted = $vote->exists();
		return $this->voted;
	}

	function insert_vote() {
		global $current_user;
		require_once(mnminclude.'votes.php');
		$vote = new Vote;
		$vote->user = $current_user->user_id;
		$vote->type='poll_comment';
		$vote->link=$this->id;
		if ($vote->exists()) {
			return false;
		}
		$vote->value = $current_user->user_karma;
		if($vote->insert()) return true;
		return false;
	}


	function return_shake_icons() {
		global $globals, $current_user;
		$response = '';
		if ( $current_user->user_karma > $globals['min_karma_for_comment_votes'] && $this->date > $globals['now'] - $globals['time_enabled_votes'] && $this->vote_exists() === false) {  
			$response .= '<span id="c-votes-'.$this->id.'">';
			$response .= '<a href="javascript:poll_comment_vote('."$current_user->user_id,$this->id,-1".')" title="'._('voto negativo').'"><img class="icon vote-down" src="'.get_cover_pixel().'" alt="'._('voto negativo').'"/></a>&nbsp;';
			$response .= '<a href="javascript:poll_comment_vote('."$current_user->user_id,$this->id,1".')" title="'._('voto positivo').'"><img class="icon vote-up" src="'.get_cover_pixel().'" alt="'._('voto positivo').'"/></a>';
			$response .= '</span>&nbsp;&nbsp;';
		} else {
			if ($this->voted > 0) {
				$response .= '<img class="icon voted-up" src="'.get_cover_pixel().'" alt="'._('votado positivo').'" title="'._('votado positivo').'"/>&nbsp;&nbsp;';
			} elseif ($this->voted < 0) {
				$response .= '<img class="icon voted-down" src="'.get_cover_pixel().'" alt="'._('votado negativo').'" title="'._('votado negativo').'"/>&nbsp;&nbsp;';
			}
		}
		return $response;
	}


	function return_text() {
		global $current_user, $globals;

		if (($this->por == $current_user->user_id &&
			$globals['now'] - $this->date < $globals['comment_edit_time'])
			|| $current_user->user_level == 'god') {

				if ($current_user->user_level == 'god')
					$iddqd = ' iddqd';

				$expand = '&nbsp;&nbsp;<a href="javascript:abrirEditar(\'edit_poll_comment.php\',\'edit_comment\',\'ccontainer-'.$this->id.'\',0,'.$this->id.')" title="'._('editar comentario').'"><span class="c-edit'.$iddqd.'">'.calc_remaining_edit_time($this->date, $globals['comment_edit_time']).'</span></a>';

		}

		return put_smileys($this->put_comment_tooltips(save_text_to_html($this->contenido))) . $expand;
	}

	// Add calls for tooltip javascript functions
	function put_comment_tooltips($str) {
		return preg_replace('/(^|[\(,;\.\s])#([0-9]+)/', "$1<a class='tt' href=\"".$this->encuesta_id."\" onmouseover=\"return tooltip.poll_c_show(event, 'id', '$2', '".$this->encuesta_id."');\" onmouseout=\"tooltip.hide(event);\"  onclick=\"tooltip.hide(this);\">#$2</a>", $str);
	}

	function update_order() {
		global $db;

		if ($this->id == 0 || $this->encuesta_id == 0) return false;

		$order = intval($db->get_var("select count(*) from polls_comments where encuesta_id=$this->encuesta_id and id < $this->id"))+1;
		
		if ($order != $this->orden) {
			$this->orden = $order;
			$db->query("update polls_comments set orden=$this->orden where id=$this->id");
		}
		return $this->orden;
	}

}
