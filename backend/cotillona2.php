<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

//ini_set('display_errors','On');
include('../config.php');
include(mnminclude.'linkcoti.php');
include(mnminclude.'user.php');
include(mnminclude.'sneak.php');
include(mnminclude.'coti2.inc.php');


$foo_link = new Link;

// The client requests version number
if (!empty($_REQUEST['getv'])) {
	echo $sneak_version;
	die;
}
$now = $globals['now'];
if(!($time=check_integer('time')) > 0 || $now-$time > 1200) {
	$time = $now-1200;
}

$dbtime = date("YmdHis", $time);

$last_timestamp = $time;

if(!empty($_REQUEST['items']) && intval($_REQUEST['items']) > 0) {
	$max_items = intval($_REQUEST['items']);
}

if ($max_items < 1 || $max_items > 50) {
	$max_items = 50; // Avoid abuse
}

header('Content-Type: text/html; charset=utf-8');

$client_version = $_REQUEST['v'];
if (empty($client_version) || ($client_version != -1 && $client_version != $sneak_version)) {
	echo "window.location.reload(true);";
	exit();
}


if (empty($_REQUEST['novote']) || empty($_REQUEST['noproblem'])) get_votes($dbtime);


// Get the logs
$logs = $db->get_results("select UNIX_TIMESTAMP(log_date) as time, log_type, log_ref_id, log_user_id from logs where UNIX_TIMESTAMP(log_date) > ".$last_timestamp." order by log_date desc limit $max_items");

if ($logs) {
	foreach ($logs as $log) {
		if ($current_user->user_id > 0) {
			if(!empty($_REQUEST['friends']) && $log->log_user_id != $current_user->user_id) {
				// Check the user is a friend
				if (friend_exists($current_user->user_id, $log->log_user_id) <= 0) continue;
			} elseif (!empty($_REQUEST['admin']) && ($current_user->user_level == 'admin' || $current_user->user_level == 'god')) {
				$user_level = $db->get_var("select user_level from users where user_id=$log->log_user_id");
				if ($user_level != 'admin' && $user_level != 'god') continue;
			}

		}
		switch ($log->log_type) {
			case 'link_new':
				if (empty($_REQUEST['nonew'])) get_story($log->time, 'new', $log->log_ref_id, $log->log_user_id);
				break;
			case 'link_publish':
				if (empty($_REQUEST['nopublished'])) get_story($log->time, 'published', $log->log_ref_id, $log->log_user_id);
				break;
			case 'comment_new':
				if (empty($_REQUEST['nocomment'])) get_comment($log->time, 'comment', $log->log_ref_id, $log->log_user_id);
				break;
			case 'link_depublished':
			case 'link_discard':
				if (empty($_REQUEST['nodiscard'])) get_story($log->time, 'discarded', $log->log_ref_id, $log->log_user_id);
				break;
			case 'link_edit':
				if (empty($_REQUEST['noedit'])) get_story($log->time, 'edited', $log->log_ref_id, $log->log_user_id);
				break;
			case 'link_geo_edit':
				if (empty($_REQUEST['nogeoedit'])) get_story($log->time, 'geo_edited', $log->log_ref_id, $log->log_user_id);
				break;
			case 'comment_edit':
				if (empty($_REQUEST['nocomment'])) get_comment($log->time, 'cedited', $log->log_ref_id, $log->log_user_id);
				break;
			case 'post_new':
				if (empty($_REQUEST['nopost'])) get_post($log->time, 'post', $log->log_ref_id, $log->log_user_id);
				break;
			case 'encuesta_new':
				if (empty($_REQUEST['nopoll'])) get_poll($log->time, 'poll', $log->log_ref_id, $log->log_user_id);
				break;
		}
	}
}

// Only registered users can see the chat messages
if ($current_user->user_id > 0 && empty($_REQUEST['nochat']) && !baneatuta($current_user->user_id)) {
	check_chat();
	get_chat($time);
}
$db->barrier();

if($last_timestamp == 0) $last_timestamp = $now;

$ccntu = $db->get_var("select count(*) from sneakers where sneaker_user > 0 and sneaker_id not like 'jabber/%'");
$ccntj = $db->get_var("select count(*) from sneakers where sneaker_user > 0 and sneaker_id like 'jabber/%'");
$ccnta = $db->get_var("select count(*) from sneakers where sneaker_user = 0");
$ccnt = $ccntu+$ccnta+$ccntj; // . " ($ccntu+$ccntj+$ccnta)"
// Banak irakurri
if ($current_user->user_id > 0)
{
if (baneatuta($current_user->user_id)) $baneatuta = 1; 
else $baneatuta = 0;



if (!$baneatuta) $mezutxo = '<input type="submit" value="'._('enviar').'" class="button"/>';
else $mezutxo = '<input type="submit" value="'._('enviar').'" disabled="disabled" class="button"/>';

}

if (($current_user->user_id > 0) && ($current_user->admin))
{
// Erabiltzaileak irakurri

$norentzat = $current_user->user_level;

$eskaera = $db->get_results("SELECT sneaker_user from sneakers order by sneaker_user asc");
$znb = 0;
foreach ($eskaera as $fisusr) {
// sartu arrayan
if (!existitzen_da_arrayan($array, $fisusr->sneaker_user)) $array[$znb] = $fisusr->sneaker_user;
$znb++;
}

$usrs_cnn = "<strong>Usuarios conectados:</strong>";


foreach ($array as $idar => $fisusr) {
			if ($fisusr > 0) {
	$user=new User();
	$user->id = $fisusr;

			// usuaxu topa
		if ($user->read())	$izena = '<a target="_blank" href =" mafioso/'.$user->username.'"> '.$user->username.' </a>'; else $izena = "(??)";
		if (baneatuta($user->id)){
			if (($norentzat == "god" || $norentzat == "admin") && $user->level != 'god' ) $banned = '<a href="cotillona.php?unban='.$user->id.'">unban</a>'; 
		 	else $banned = 'baneado';
		} else  {
			if (($norentzat == "god" || $norentzat == "admin") && $user->level != 'god') $banned = '<a href="cotillona.php?ban='.$user->id.'">ban</a>';
	  		else $banned = 'usuario';
		}
		$usrs_cnn .= " ".$izena." (".$banned.")  ";

		
		}

}


echo "ts=$last_timestamp;ccnt='$ccnt';ttm='$mezutxo';uzo='$usrs_cnn';\n";
} else echo "ts=$last_timestamp;ccnt='$ccnt';ttm='$mezutxo';\n";

if(count($events) < 1) exit;
krsort($events);

$counter=0;
echo "new_data = ([";
foreach ($events as $key => $val) {
	if ($counter>0) 
		echo ",";
	echo $val;
	$counter++;
	if($counter>=$max_items) {
		echo "]);";
		exit();
	}
}
echo "]);";
if(intval($_REQUEST['r']) % 10 == 0) update_sneakers();

function check_chat() {
	global $db, $current_user, $now, $globals, $events;
	
	//update_sneakers();
	
	if(empty($_POST['chat'])) return;
	$comment = trim(preg_replace("/[\r\n\t]/", ' ', $_REQUEST['chat']));
	$comment = clear_whitespace($comment);
	if ($current_user->user_id > 0) {
		if ((strlen($comment) > 2 && !$current_user->admin) || $current_user->admin) {
			// Sends a message back if the user has a very low carisma
			if ($globals['min_karma_for_sneaker'] > 0 && $current_user->user_karma < $globals['min_karma_for_sneaker']) {
				$comment = _('no tienes suficiente carisma para comentar en la cotillona').' ('.$current_user->user_karma.' < '.$globals['min_karma_for_sneaker'].')';
				send_chat_warn($comment);
				return;
			}

			$period = $now - 1;
			$counter = intval($db->get_var("select count(*) from chats where chat_time > $period and chat_uid = $current_user->user_id"));
			if ($counter > 0 && !$current_user->admin) {
				$comment = _('tranquilo charlatán, no se pudo enviar: ').'\''.$comment.'\'';
				send_chat_warn($comment);
				return;
			}

			/*if (baneatuta($current_user->user_id)){
				$comment = _('Has sido baneado de la cotillona de jon&aacute;ame, si crees que hubo alg&uacute;n error comun&ieacute;quenoslo a trav&eeacute; del email');
				send_chat_warn($comment);
				syslog(LOG_NOTICE, "Jonéame, usuario baneado intentando chatear en la cotillona: $current_user->user_login ");
				return;
					}*/
			

			if (preg_match('/^!/', $comment)) {
				require_once('sneaker-stats.php');
				if(!($comment = check_stats($comment))) {
					send_chat_warn(_('comando no reconocido'));
				} else {
					send_string($comment);
				}
				return;
			} else {
				$comment = htmlspecialchars($comment);
				$comment = preg_replace('/(^|[\s\.,¿#@])\/me([\s\.,\?]|$)/', "$1<i>$current_user->user_login</i>$2", $comment);
			}

			$from = $now - 1200;
			$db->query("delete from chats where chat_time < $from");
			$comment = $db->escape(trim($comment));
			if ((!empty($_REQUEST['admin']) || preg_match('/^#/', $comment)) && ($current_user->admin)) {
				$room = 'admin';
				$comment = preg_replace('/^# */', '', $comment);
			} elseif (!empty($_REQUEST['friends']) || preg_match('/^@/', $comment)) {
				$room = 'friends';
				$comment = preg_replace('/^@ */', '', $comment);
			}  elseif ((($_REQUEST['devel']) || preg_match('/^%/', $comment)) && ($current_user->admin || $current_user->devel)){
				$room = 'devel';
				$comment = preg_replace('/^% */', '', $comment);
			} else {
				$room = 'all';
			}
			if ((!empty($_REQUEST['admins']) || preg_match('/^€/', $comment)) && ($current_user->admin)) {
				$room = 'admin';
				$comment = preg_replace('/^€ */', '', $comment);
			}
			if (strlen($comment)>0) {
				$db->query("insert into chats (chat_time, chat_uid, chat_room, chat_user, chat_text) values ($now, $current_user->user_id, '$room', '$current_user->user_login', '$comment')");
			}
		} else {
			send_chat_warn(_('mensaje demasiado corto, no se pudo enviar: ').'\''.$comment.'\'');
		}
	}
}

function send_string($mess) {
	global $current_user, $now, $globals, $events;

	$key = $now . ':chat:'.$id;
	$json['who'] = addslashes($current_user->user_login);
	$json['uid'] = $current_user->user_id;
	$json['ts'] = $now;
	$json['status'] =  _('chat');
	$json['type'] =  'chat';
	$json['votes'] = 0;
	$json['com'] = 0;
	$json['title'] = addslashes(text_to_html($mess));
	$events[$key] = json_encode_single($json);
}

function send_chat_warn($mess) {
	$mess = '<strong>'._('Aviso').'</strong>: '.$mess;
	send_string($mess);
}

function get_chat($time) {
	global $db, $events, $globals, $last_timestamp, $max_items, $current_user;

	if (!empty($_REQUEST['admin']) || !empty($_REQUEST['friends'])) $chat_items = $max_items * 2;
	else $chat_items = $max_items;
	$res = $db->get_results("select * from chats where chat_time > $time order by chat_time desc limit $chat_items");
	if (!$res) return;
	foreach ($res as $event) {
		$json['uid'] = $uid = $event->chat_uid;
		$json['status'] = _('chat');
		if ($uid != $current_user->user_id) {

			// CHECK ADMIN MODE
			// If the message is for admins check this user is also admin
			if ($event->chat_room == 'admin') {
				if ($current_user->user_level != 'admin' && $current_user->user_level != 'god') continue;
				$json['status'] = 'il capo'; //aqui para editar el estado <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
			}
			if ($event->chat_room == 'devel') {
				if (!$current_user->admin && !$current_user->devel) continue;
				$json['status'] = 'devel'; //aqui para editar el estado <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
			}

			// If this user is in "admin" mode, check the sender is also admin
			if (!empty($_REQUEST['admin']) && ($current_user->admin)) {
				$user_level = $db->get_var("select user_level from users where user_id=$uid");
				if ($user_level != 'admin' && $user_level != 'god') continue;
			} elseif (!empty($_REQUEST['devel']) && ($current_user->admin || $current_user->devel)) {
				$user_level = $db->get_var("select user_level from users where user_id=$uid");
				if ($user_level != 'admin' && $user_level != 'god' && $user_level != 'devel') continue;
			}else  {
				// CHECK FRIENDSHIP
				$friendship = friend_exists($current_user->user_id, $uid);
				// Ignore
				if ($friendship < 0 && !($current_user->admin)) continue;
				// This user is ignored by the writer
				if (friend_exists($uid, $current_user->user_id) < 0){
					if ($current_user->admin) $aktibia = 1; 
					else { $aktibia = 0; 
						continue;
					}
				}
				if ($event->chat_room == 'friends') {
					// Check the user is a friend of the sender
					if (friend_exists($uid, $current_user->user_id) <= 0) {
						continue;
					}
					$json['status'] = _('cosa nostra'); 
				}
				// Check the sender is a friend of the receiver
				if (!empty($_REQUEST['friends']) && $friendship <= 0) {
						continue;
				}
			}
		} else {
			if ($event->chat_room == 'friends') {
				$json['status'] = _('cosa nostra'); 
			} elseif ($event->chat_room == 'admin') {
				$json['status'] = 'il capo';
			}
		elseif ($event->chat_room == 'devel') {
				$json['status'] = 'devel';
			}
		}
		$json['who'] = addslashes($event->chat_user);
		$json['ts'] = $event->chat_time;
		$json['type'] = 'chat';
		$json['votes'] = 0;
		$json['com'] = 0;
		$json['link'] = 0;
		$json['title'] = addslashes(clear_whitespace(text_to_html(preg_replace("/[\r\n]+/", ' ¬ ', preg_replace('/&&user&&/', $current_user->user_login, $event->chat_text)))));
		if ($aktibia) { $json['title'] = '<span style=\"color: #99CC00\">IG: '.$json['title'].'</span>'; $aktibia = 0;}
		if ($uid >0) $json['icon'] = get_avatar_url($uid, -1, 20);
		$key = $event->chat_time . ':chat:'.$uid;

		$events[$key] = json_encode_single($json);
		if($event->chat_time > $last_timestamp) $last_timestamp = $event->chat_time;
	}
}


// Check last votes
function get_votes($dbtime) {
	global $db, $events, $last_timestamp, $foo_link, $max_items, $current_user;

	$res = $db->get_results("select vote_id, unix_timestamp(vote_date) as timestamp, vote_value, vote_aleatorio,INET_NTOA(vote_ip_int) as vote_ip, vote_user_id, link_id, link_title, link_uri, link_status, link_date, link_votes, link_anonymous, link_comments from votes, links where vote_type='links' and UNIX_TIMESTAMP(vote_date) > ".$last_timestamp." and link_id = vote_link_id and vote_user_id != link_author order by vote_date desc limit $max_items");
	if (!$res) return;
	foreach ($res as $event) {
		if ($current_user->user_id > 0) {
			if (!empty($_REQUEST['friends']) && $event->vote_user_id != $current_user->user_id) {
				// Check the user is a friend
				if (friend_exists($current_user->user_id, $event->vote_user_id) <= 0) {
					continue;
				} elseif ($event->vote_value < 0) {
					// If the vote is negative, verify also the other user has selected as friend to the current one
					if (friend_exists($event->vote_user_id, $current_user->user_id) <= 0) {
						continue;
					}
				}
			} elseif (!empty($_REQUEST['admin']) && ($current_user->user_level == 'admin' || $current_user->user_level == 'god')) {
				$user_level = $db->get_var("select user_level from users where user_id=$event->vote_user_id");
				if ($user_level != 'admin' && $user_level != 'god') continue;
			}
				elseif (!empty($_REQUEST['devel']) && ($current_user->admin || $current_user->user_level == 'devel')) {
				$user_level = $db->get_var("select user_level from users where user_id=$event->vote_user_id");
				if ($user_level != 'admin' && $user_level != 'devel' && $user_level != 'god') continue;
			}
		}
		if ($event->vote_value >= 0) {
			if ($_REQUEST['novote']) continue;
			if ($event->link_status == 'published' && $_REQUEST['nopubvotes']) continue;
		} else {
			if ($_REQUEST['noproblem']) continue;
		}
		$foo_link->id=$event->link_id;
		$foo_link->uri=$event->link_uri;
		$foo_link->get_relative_permalink();
		$uid = $event->vote_user_id;
		if($event->vote_user_id > 0) {
			$res = $db->get_row("select user_login from users where user_id = $event->vote_user_id");
			$user = $res->user_login;
		} else {
			if ($current_user->user_level == 'god') $user = $event->vote_ip;
				else $user= preg_replace('/\.[0-9]+$/', '', $event->vote_ip);
		}
		if ($event->vote_value >= 0 ) {
			$type = 'vote';
			$who = $user;
		} else { 
			$type = 'problem';
			$who = get_negative_vote($event->vote_value);
			// Show user_login if she voted more than N negatives in one minute
			if($current_user->user_id > 0 && ($current_user->admin)) {
				$negatives_last_minute = $db->get_var("select count(*) from votes where vote_type='links' and vote_user_id=$event->vote_user_id and vote_date > date_sub(now(), interval 30 second) and vote_value < 0");
				if($negatives_last_minute > 2 ) {
					$who .= "<br>($user)";
				}
			}
		}
		$json['status'] = get_status($event->link_status);
		$json['type'] = $type;
		$json['ts'] = $event->timestamp;
		$json['votes'] = $event->link_votes+$event->link_anonymous;
		$json['com'] = $event->link_comments;
		$json['link'] = $foo_link->get_relative_permalink();
		$json['title'] = addslashes($event->link_title);
		$json['who'] = addslashes($who);
		$json['uid'] = $event->vote_user_id;
		$json['id'] = $event->link_id;
		 if ($event->vote_user_id >0) $json['icon'] = get_avatar_url($event->vote_user_id, -1, 20);
		 elseif ($event->vote_user_id == 0 && $event->vote_aleatorio == "aleatorio"  ) $json['icon'] = 'img/v2/no-avatar-20.png';
		$key = $event->timestamp . ':votes:'.$event->vote_id;;
		$events[$key] = json_encode_single($json);
		if($event->timestamp > $last_timestamp) $last_timestamp = $event->timestamp;
	}
}


function get_story($time, $type, $linkid, $userid) {
    global $db, $events, $last_timestamp, $foo_link, $max_items;
    $event = $db->get_row("select SQL_CACHE user_login, user_level, link_title, link_uri, link_status, link_votes, link_anonymous, link_comments, link_author from links, users where link_id = $linkid and user_id=$userid limit ".$max_items);
    if (!$event) return;

    $foo_link->id=$linkid;
    $foo_link->uri=$event->link_uri;
    $json['link'] = $foo_link->get_relative_permalink();
    $json['id'] = $linkid;
    $json['status'] = get_status($event->link_status);
    $json['ts'] = $time;
    $json['type'] = $type;
    $json['votes'] = $event->link_votes+$event->link_anonymous;
    $json['com'] = $event->link_comments;
    $json['title'] = addslashes($event->link_title);

    if ($type == 'discarded' && $event->link_status == 'abuse' && $event->link_auhtor != $userid 
        && ($event->user_level == 'admin' || $event->user_level == 'god')) {
        // Discarded by abuse, don't show the author
        $json['uid'] = 0;
        $json['who'] = 'admin';
    } else {
        $json['who'] = addslashes($event->user_login);
        $json['uid'] = $userid;
        if ($userid >0) $json['icon'] = get_avatar_url($userid, -1, 20);
    }

    $key = $time . ':'.$type.':'.$linkid;
    $events[$key] = json_encode_single($json);
    if($time > $last_timestamp) $last_timestamp = $time;
}

function get_comment($time, $type, $commentid, $userid) {
	global $db, $events, $last_timestamp, $foo_link, $max_items, $globals;
	$event = $db->get_row("select user_login, comment_user_id, comment_type, comment_order, link_id, link_title, link_uri, link_status, link_date, link_votes, link_anonymous, link_comments from comments, links, users where comment_id = $commentid and link_id = comment_link_id and user_id=$userid limit ".$max_items);
	if (!$event) return;
	$foo_link->id=$event->link_id;
	$foo_link->uri=$event->link_uri;
	$json['link'] = $foo_link->get_relative_permalink().get_comment_page_suffix($globals['comments_page_size'], $event->comment_order, $event->link_comments)."#comment-$event->comment_order";
	$json['id'] = $commentid;
	$json['status'] = get_status($event->link_status);
	$json['ts'] = $time;
	$json['type'] = $type;
	$json['votes'] = $event->link_votes+$event->link_anonymous;
	$json['com'] = $event->link_comments;
	$json['title'] = addslashes($event->link_title);
	if ( $event->comment_type == 'admin') {
		$json['who'] = 'admin';
		$userid = 0;
	} else {
		$json['who'] = addslashes($event->user_login);
	}
	$json['uid'] = $userid;
	if ($userid >0) $json['icon'] = get_avatar_url($userid, -1, 20);
	
	$key = $time . ':'.$type.':'.$commentid;
	$events[$key] = json_encode_single($json);
	if($time > $last_timestamp) $last_timestamp = $time;
}

function get_post($time, $type, $postid, $userid) {
	global $db, $current_user, $events, $last_timestamp, $foo_link, $max_items;
	$event = $db->get_row("select user_login, post_user_id, post_type, post_content from posts, users where post_id = $postid and user_id=$userid limit ".$max_items);
	if (!$event) return;
	// Dont show her notes if the user ignored
	if ($type == 'post' && $event->post_type != 'admin' && friend_exists($current_user->user_id, $userid) < 0) return;
	if ( $event->post_type != 'admin') 
		$json['link'] = post_get_base_url($event->user_login) . "/$postid";
	else    $json['link'] = post_get_base_url(get_server_name());
	$json['ts'] = $time;
	$json['type'] = $type;
	if ( $event->post_type == 'admin') {
		$json['who'] = 'admin';
		$userid = 0;
	} else {
		$json['who'] = addslashes($event->user_login);
	}
	$json['status'] = _('notita');
	$json['title'] = addslashes(text_to_summary(preg_replace('/(@[\S.-]+)(,\d+)/','$1',$event->post_content),130));
	$json['votes'] = 0;
	$json['com'] = 0;
	$json['uid'] = $userid;
	$json['id'] = $postid;
	if ($userid >0) $json['icon'] = get_avatar_url($userid, -1, 20);
	$key = $time . ':'.$type.':'.$postid;
	$events[$key] = json_encode_single($json);
	if($time > $last_timestamp) $last_timestamp = $time;
}

//obtiene ultimas encuestas
function get_poll($time, $type, $encuestaid, $userid) {
	global $db, $current_user, $events, $last_timestamp, $foo_link, $max_items;
	$event = $db->get_row("select user_login, encuesta_user_id, encuesta_title from encuestas, users where encuesta_id = $encuestaid and user_id=$userid limit ".$max_items);
	if (!$event) return;

	//if ($type == 'encuesta' && friend_exists($current_user->user_id, $userid) < 0) return;
	$json['link'] = $globals['base_url'].'encuestas.php?id='. "$encuestaid";
	$json['ts'] = $time;
	$json['type'] = $type;
	$json['who'] = addslashes($event->user_login);
	$json['status'] = _('encuesta');
	$json['title'] = addslashes(text_to_summary(preg_replace('/(@[\S.-]+)(,\d+)/','$1',$event->encuesta_title),130));
	$json['votes'] = 0;
	$json['com'] = 0;
	$json['uid'] = $userid;
	$json['id'] = $encuestaid;
	if ($userid >0) $json['icon'] = get_avatar_url($userid, -1, 20);
	$key = $time . ':'.$type.':'.$postid;
	$events[$key] = json_encode_single($json);
	if($time > $last_timestamp) $last_timestamp = $time;
}

function get_status($status) {
	switch ($status) {
		case 'published':
			$status = _('en portada');
			break;
		case 'queued':
			$status = _('pendiente');
			break;
		case 'duplicated':
		case 'abuse':
		case 'autodiscard':
		case 'discard':
			$status = _('descartada');
			break;
	}
	return $status;
}


function error($mess) {
	header('Content-Type: text/plain; charset=UTF-8');
	echo "ERROR: $mess";
	die;
}

function update_sneakers() {
    global $db, $globals, $current_user;
    $key = $globals['user_ip'] . '-' . intval($_REQUEST['k']);
	
	if ($db->get_var("select sneaker_id from sneakers where sneaker_user = ".$current_user->user_id." and sneaker_id = '".$key."'"))
	    $db->query("replace into sneakers (sneaker_id, sneaker_time, sneaker_user) values ('".$key."', unix_timestamp(now()), ".$current_user->user_id.")");
//	else // no existe
		$db->query("INSERT INTO sneakers VALUES('".$key."', unix_timestamp(now()), ".$current_user->user_id.")");
		
    if($_REQUEST['r'] % 37 == 0) {
        $from = time()-120;
        $db->query("delete from sneakers where sneaker_time < ".$from);
    }
}
?>
