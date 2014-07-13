<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

mb_internal_encoding('UTF-8');

// Use proxy detecttion
if ($globals['check_behind_proxy']) {
	require_once(mnminclude.'check_behind_proxy.php');
	$globals['user_ip'] = check_ip_behind_proxy();
} else {
	$globals['user_ip'] = $_SERVER["REMOTE_ADDR"];
}

// Warn, we shoud printf "%u" because PHP on 32 bits systems fails with high unsigned numbers
$globals['user_ip_int'] = sprintf("%u", ip2long($globals['user_ip']));

$globals['now'] = time();

$globals['negative_votes_values'] = Array (-1 => _('repetida'), -2 => _('inadecuada'), -3 => _('voto mafia'));

$globals['extra_js'] = Array();
$globals['extra_css'] = Array();
$globals['post_js'] = Array();
$globals['mobile'] = false;

// For PHP < 5
if ( !function_exists('htmlspecialchars_decode') ) {
	function htmlspecialchars_decode($text) {
		return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
	}
}


// Check the user's referer.
if( !empty($_SERVER['HTTP_REFERER'])) {
	if (preg_match('/http:\/\/'.preg_quote($_SERVER['SERVER_NAME']).'/', $_SERVER['HTTP_REFERER'])) {
		$globals['referer'] = 'local';
	} elseif (preg_match('/q=|search/', $_SERVER['HTTP_REFERER']) ) {
		$globals['referer'] = 'search';
	} else {
		$globals['referer'] = 'remote';
	}
} else {
	$globals['referer'] = 'unknown';
}

// Check bots
if (preg_match('/(bot|slurp|wget|libwww|\Wjava|\Wphp)\W/i', $_SERVER['HTTP_USER_AGENT'])) {
	$globals['bot'] = true;
} else $globals['bot'] = false;

// Check mobile/TV versions
if (preg_match('/SymbianOS|BlackBerry|iPhone|Nintendo|Mobile|Opera Mini|Android|\/MIDP|Portable|webOS/i', $_SERVER['HTTP_USER_AGENT'])) {
        $globals['mobile'] = true;
}  else $globals['mobile'] = false;

function htmlentities2unicodeentities ($input) {
	$htmlEntities = array_values (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
	$entitiesDecoded = array_keys  (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
	$num = count ($entitiesDecoded);
	for ($u = 0; $u < $num; $u++) {
		$utf8Entities[$u] = '&#'.ord($entitiesDecoded[$u]).';';
	}
	return str_replace ($htmlEntities, $utf8Entities, $input);
}

function clean_input_url($string) {
    $string = preg_replace('/ /', '+', trim(stripslashes(mb_substr($string, 0, 512))));
    $string = preg_replace('/[<>\r\n\t]/', '', $string);
    $string = preg_replace('/utm_\w+?=[^&]*/', '', $string); // Delete common variables  for Analitycs
    $string = preg_replace('/&{2,}/', '&', $string); // Delete duplicates &
    $string = preg_replace('/&+$/', '', $string); // Delete useless & at the end
    $string = preg_replace('/\?&+/', '?', $string); // Delete useless & after ?
    $string = preg_replace('/\?&*$/', '', $string); // Delete empty queries
    return $string;
}

function clean_input_string($string) {
	return preg_replace('/[ <>\'\"\r\n\t\(\)]/', '', stripslashes($string));
}

function get_hex_color($color, $prefix = '') {
	return $prefix . substr(preg_replace('/[^a-f\d]/i', '', $color), 0, 6);
}

function get_negative_vote($value) {
	global $globals;
	return empty($globals['negative_votes_values'][$value]) ? "voto aleatorio" : $globals['negative_votes_values'][$value];
}

function user_exists($username) {
	global $db;
	$username = $db->escape($username);
	$res=$db->get_var("SELECT count(*) FROM users WHERE user_login='$username'");
	if ($res>0) return true;
	return false;
}

function email_exists($email) {
	global $db;

	$parts = explode('@', $email);
	$domain = $parts[1];
	$subparts = explode('+', $parts[0]); // Because we allow user+extension@gmail.com
	$user = $subparts[0];
	$user = $db->escape($user);
	$domain = $db->escape($domain);
	$res=$db->get_var("SELECT count(*) FROM users WHERE user_email = '$user@$domain' or user_email LIKE '$user+%@$domain'");
	if ($res>0) return $res;
	return false;
}

function check_email($email) {
	global $globals;
	require_once(mnminclude.'ban.php');
	if (! preg_match('/^[a-z0-9_\-\.]+(\+[a-z0-9_\-\.]+)*@[a-z0-9_\-\.]+\.[a-z]{2,4}$/i', $email)) return false;

	$username = preg_replace('/@.+$/', '', $email);
	if ( substr_count($username, '.') > 2 || preg_match('/\.{2,}/', $username) ) return false; // Doesn't allow "..+" or more than 2 dots

	if(check_ban(preg_replace('/^.*@/', '', $email), 'email')) return false;
	return true;
}

function url_clean($url) {
	$array = explode('#', $url, 1);
	return $array[0];
}

function check_username($name) {
	return (preg_match('/^[a-zçÇñÑ][a-z0-9_\-\.çÇñÑ·|]+$/i', $name) && mb_strlen($name) <= 24 &&
				! preg_match('/^admin/i', $name) ); // Does not allow nicks begining with "admin"
}

function check_password($password) {
	 return preg_match("/^(?=.{6,})(?=(.*[a-z].*))(?=(.*[A-Z0-9].*)).*$/", $password);
}

function txt_time_diff($from, $now=0){
	global $globals;


        $txt = '';

        if ($now == 0) $now = $globals['now'];

	if ($from > $now) $from = $now;

        $diff=$now-$from;
        $days=intval($diff/86400);

        $diff=$diff%86400;
        $hours=intval($diff/3600);

        $diff=$diff%3600;
        $minutes=intval($diff/60);

        $secs=$diff%60;

        if($days>1) $txt  .= " $days "._('d');
        else if ($days==1) $txt  .= " $days "._('d');

        if($hours>1) $txt .= " $hours "._('h');
        else if ($hours==1) $txt  .= " $hours "._('h');

        if($minutes>1) $txt .= " $minutes "._('m');
        else if ($minutes==1) $txt  .= " $minutes "._('m');

        if ($txt=='') $txt = " $secs ". _('segs');

        return $txt;

}

function txt_shorter($string, $len=70) {
	if (strlen($string) > $len)
		$string = substr($string, 0, $len-3) . "...";
	return $string;
}

// Used to get the text content for stories and comments
function clean_text($string, $wrap=0, $replace_nl=true, $maxlength=0) {
	$string = stripslashes(trim($string));
	$string = clear_whitespace($string);
	$string = html_entity_decode($string, ENT_COMPAT, 'UTF-8');
	// Replace two "-" by a single longer one, to avoid problems with xhtml comments
	//$string = preg_replace('/--/', '–', $string);
	if ($wrap>0) $string = wordwrap($string, $wrap, " ", 1);
	if ($replace_nl) $string = preg_replace('/[\n\t\r]+/s', ' ', $string);
	if ($maxlength > 0) $string = mb_substr($string, 0, $maxlength);
	return @htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
}

function clean_lines($string) {
	return preg_replace('/[\n\r]{6,}/', "\n\n", $string);
}

function save_text_to_html($string, $hashtype = false) {
    //$string = strip_tags(trim($string));
    //$string= htmlspecialchars(trim($string));
    $str= text_to_html($string, $hashtype);
    $str = preg_replace("/\r\n|\r|\n/", "\n<br />\n", $str);
    return $str;
}

function text_sub_text($str, $length=70) {
    $len = mb_strlen($str);
    $string = preg_replace("/[\r\n\t]+/", ' ', $str);
    $string = mb_substr($string,  0, $length);
    if (mb_strlen($string) < $len) {
        $string = preg_replace('/ *[\w&;]*$/', '', $string);
        $string = preg_replace('/\. [^\.]{1,50}$/', '.', $string);
        $string .= '...';
    }
    return $string;
}

function text_to_summary($string, $length=50) {
    return text_to_html(text_sub_text($string, $length), false, false);
}

function text_to_html($str, $hashtype = false, $do_links = true) {
    global $globals;

    if ($do_links) {
        $str = preg_replace('/(\b)(https*:\/\/)(www\.){0,1}([^ \t\n\r\]\&]{5,70})([^ \t\n\r\]]*)([^ :.\t,\n\r\(\)\"\'\]\?])/u', '$1<a href="$2$3$4$5$6" title="$2$3$4$5$6" rel="nofollow">$4$6</a>', $str);
    }
    if ($hashtype) {
        // Add links to hashtags
        $str = preg_replace('/(^|\s)\#([^\d][^\s\.\,\:\;\¡\!\)\-]{1,42})/u', '$1<a href="'.$globals['base_url'].'search.php?w='.$hashtype.'&amp;q=%23$2&amp;o=date">#$2</a>', $str);
    }
    $str = preg_replace('/\b_([^\s<>_]+)_\b/', "<em>$1</em>", $str);
    $str = preg_replace('/(^|[\(¡;,:¿\s])\*([^\s<>]+)\*/', "$1<strong>$2</strong>", $str);
    $str = preg_replace('/(^| )\-([^\s<>]+)\-/', "$1<del>$2</del>", $str);
    return $str;
}

// Clean all special chars and html/utf entities
function text_sanitize($string) {
	$string = preg_replace('/&[^ ;]{1,8};/', ' ', $string);
	$string = preg_replace('/(^|[\(¡;,:\s])[_\*]([^\s<>]+)[_\*]/', ' $2 ', $string);
	return $string;
}

function check_integer($which) {
	if (isset($_REQUEST[$which]) && is_numeric($_REQUEST[$which])) {
		return intval($_REQUEST[$which]);
	} else {
		return false;
	}
}

function get_comment_page_suffix($page_size, $order, $total=0) {
	if ($page_size > 0) {
		if ($total && $total < $page_size) return '';
		return '/'.ceil($order/$page_size);
	}
	return '';
}

function get_current_page() {
	if(($var=check_integer('page'))) {
		return $var;
	} else {
		return 1;
	}
    // return $_GET['page']>0 ? $_GET['page'] : 1;
}

function get_date($epoch) {
    return date("Y-m-d", $epoch);
}

function get_date_time($epoch) {
	    return date(" d-m-Y H:i", $epoch);
}

function get_server_name() {
	global $server_name;
	if($_SERVER['SERVER_NAME']) return $_SERVER['SERVER_NAME'];
	else {
		if ($server_name) return $server_name;
		else return 'joneame.net'; // Warn: did you put the right server name?
	}
}

function get_user_uri($user, $view='') {
	global $globals;

	if (!empty($globals['base_user_url'])) {
		$uri= $globals['base_url'] . $globals['base_user_url'] . htmlspecialchars($user);
		if (!empty($view)) $uri .= "/$view";
	} else {
		$uri = $globals['base_url'].'mafioso.php?login='.htmlspecialchars($user);
		if (!empty($view)) $uri .= "&amp;view=$view";
	}
	return $uri;
}

function get_corto_uri($corto) {
	global $globals;

	if (!empty($globals['base_corto_url'])) {
		$uri= $globals['base_url'] . $globals['base_corto_url'] . htmlspecialchars($corto);
	} else {
		$uri = $globals['base_url'].'corto.php?id='.htmlspecialchars($corto);
	}
	return $uri;
}

function get_mensajes_uri($mensaje) {
	global $globals;

	if (!empty($globals['base_mensaje_url'])) {
		$uri= $globals['base_url'] . $globals['base_mensaje_url'] . htmlspecialchars($mensaje);
	} else {
		$uri = $globals['base_url'].'postbox.php?id='.htmlspecialchars($mensaje);
	}
	return $uri;
}
function get_story_uri($story) {
	global $globals;

	if (!empty($globals['base_story_url'])) {
		$uri= $globals['base_url'] . $globals['base_story_url'] . htmlspecialchars($story);
	} else {
		$uri = $globals['base_url'].'story.php?id='.htmlspecialchars($story);
	}
	return $uri;
}

function get_encuesta_uri($encuesta) {
	global $globals;

	if (!empty($globals['base_encuesta_url'])) {
		$uri= $globals['base_url'] . $globals['base_encuesta_url'] . htmlspecialchars($encuesta);
	} else {
		$uri = $globals['base_url'].'encuesta.php?id='.htmlspecialchars($encuesta);
	}
	return $uri;
}

function get_user_uri_by_uid($user, $view='') {
    global $globals;

    $uid = guess_user_id($user);
    if ($uid == 0) $uid = -1; // User does not exist, ensure it will give error later
    $uri = get_user_uri($user, $view);
    if (!empty($globals['base_user_url'])) {
        $uri .= "/$uid";
    } else {
        $uri .= "&uid=$uid";
    }
    return $uri;
}


function post_get_base_url($option='') {
	global $globals;
	if (empty($globals['base_sneakme_url']) || isset($globals['localhost']) && $globals['localhost']) {
		if (empty($option)) {
			return $globals['base_url'].'sneakme/';
		} else {
			return $globals['base_url'].'sneakme/?id='.$option;
		}
	} else {
		return $globals['base_url'].$globals['base_sneakme_url'].$option;
	}
}

function get_avatar_url($user, $avatar, $size) {
	global $globals, $db;

	// If it does not get avatar status, check the database
	if ($user > 0 && $avatar < 0) {
		$avatar = (int) $db->get_var("select user_avatar from users where user_id = $user");
	}

	if ($avatar > 0 && $globals['cache_dir']) {
		$file = $globals['cache_dir'] . '/avatars/'. intval($user/$globals['avatars_files_per_dir']) . '/' . $user . "-$size.jpg";
		// Don't check every time, but 1/10, decrease VM pressure
		// Disabled for the moment, it fails just too much for size 40
		// if (rand(0, 10) < 10) return $globals['base_url'] . $file;
		$file_path = mnmpath.'/'.$file;
		if (@filemtime($file_path) >= $avatar) {
			return $globals['base_url'] . $file;
		} else {
			return $globals['base_url'] . "backend/get_avatar.php?id=$user&amp;size=$size&amp;time=$avatar";
		}
	}
	return get_no_avatar_url($size);
}

function get_no_avatar_url($size) {
	global $globals;
	return $globals['base_url'].'img/v2/no-avatar-'.$size.'.png';
}

function get_joneame_avatar($size) {
	global $globals;
	return $globals['base_url'].'cache/avatars/0/73-'.$size.'.jpg';
}

function get_admin_avatar($size) {
	global $globals;
	return $globals['base_url'].'img/v2/admin-avatar-'.$size.'.png';
}

function is_connected($id) {
	global $db, $current_user;

    if (!$current_user->user_id) {
        return false;
    }

	$existe = $db->get_var("SELECT sneaker_user FROM sneakers WHERE sneaker_user=$id");
    return $existe ? true : false;
}

function utf8_substr($str,$start)
{
	preg_match_all("/./su", $str, $ar);

	if(func_num_args() >= 3) {
		$end = func_get_arg(2);
		return join("",array_slice($ar[0],$start,$end));
	} else {
		return join("",array_slice($ar[0],$start));
	}
}

function not_found($mess = '') {
	header("HTTP/1.0 404 Not Found");
	header("Status: 404 Not Found");
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";
    echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$dblang.'" lang="'.$dblang.'">' . "\n";
    echo '<head>' . "\n";
    echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . "\n";
    echo "<title>". _('error') . "</title>\n";
    echo '<meta name="generator" content="meneame" />' . "\n";
    echo '<link rel="icon" href="'.$globals['base_url'].'img/favicons/favicon4.ico" type="image/x-icon" />' . "\n";
    echo '</head>' . "\n";
    echo "<body>\n";
	if (empty($mess)) {
		echo '<h1>' . _('Error.') . ' [número pi]</h1><p>' . _('no encontrado.') . '</p><br><br><br><br><br><br><br><br><br><br><br><br>';
		echo '<li><a href="javascript:history.go(-1)">'._('Retroceder').'</a></li>'."\n";
	} else {
		echo $mess;
	}
	echo "</body></html>\n";
	exit;
}

function get_uppercase_ratio($str) {
	$str = trim(htmlspecialchars_decode($str));
	$len = mb_strlen($str);
	$uppers = preg_match_all('/[A-Z]/', $str, $matches);
	if ($uppers > 0 && $len > 0) {
		return $uppers/$len;
	}
	return 0;
}

function do_modified_headers($time, $tag) {
	header('Last-Modified: ' . date('r', $time));
	header('ETag: "'.$tag.'"');
	header('Cache-Control: max-age=5');
}

function get_if_modified() {
	// Get client headers - Apache only
	if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	// Split the If-Modified-Since (Netscape < v6 gets this wrong)
		$modifiedSince = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
		return strtotime($modifiedSince[0]);
	} else {
		return 0;
	}
}

function guess_user_id ($str) {
	global $db;

	if (preg_match('/^[0-9]+$/', $str)) {
		// It's a number, return it as id
		return (int) $str;
	} else {
		$str = $db->escape($str);
		$id = (int) $db->get_var("select user_id from users where user_login = '$str'");
		return $id;
	}
}

function print_simpleformat_buttons($textarea_id, $smileys=false) {

	echo '<div class="barra redondo simpleformat">';

	if ($smileys)
		echo '<img onclick="smileys_list()" alt="smileys" src="/img/smileys/smiley.gif"/>';
	echo '<img onclick="applyTag(\''.$textarea_id.'\', \'-\');" src="'.get_cover_pixel().'" alt="strikethrough" class="icon strike rich-edit-key" />';
	echo '<img onclick="applyTag(\''.$textarea_id.'\', \'_\');" src="'.get_cover_pixel().'" alt="italic" class="icon italic rich-edit-key" />';
	echo '<img onclick="applyTag(\''.$textarea_id.'\', \'*\');" src="'.get_cover_pixel().'" alt="bold" class="icon bold rich-edit-key" />';

	echo '</div>';
}

function put_smileys($str) {
    global $globals;

    if ($globals['bot']) return $str;

    $str = preg_replace_callback('/\{([a-z]{3,10})\}/', 'put_smileys_callback', $str);
    return $str;
}

function put_smileys_callback(&$matches) {
	global $globals;
	static $translations = false;

	if (!$translations) {
        $translations = array(
			'awesome' => ' <img src="'.$globals['base_url'].'img/smileys/awesome.png" alt=":awesome:" title=":awesome:" width="19" height="19" /> ',
			'clint' => ' <img src="'.$globals['base_url'].'img/smileys/clint.png" alt=":clint:" title=":clint:" width="25" height="25" /> ',
			'ffu' => ' <img src="'.$globals['base_url'].'img/smileys/fu.gif" alt=":ffu:" title=":ffu:" width="19" height="15" /> ',
			'palm' => ' <img src="'.$globals['base_url'].'img/smileys/palm.gif" alt=":palm:" title=":palm:" width="15" height="15" /> ',
			'goatse' =>  '<img src="'.$globals['base_url'].'img/smileys/goat.gif" alt="goat" title=":goat: :goatse:" width="15" height="15" /> ',
			'wow' => ' <img src="'.$globals['base_url'].'img/smileys/wow.gif" alt="o_o" title="o_o :wow:" width="15" height="15" /> ',
			'shame' => ' <img src="'.$globals['base_url'].'img/smileys/shame.gif" alt="¬¬" title="¬¬ :shame:" width="15" height="15" /> ',
			'sisi' => ' <img src="'.$globals['base_url'].'img/smileys/sisi1.gif" alt=":sisi1:" title=":sisi1:" width="15" height="15" /> ',
			'gaydude' =>  ' <img src="'.$globals['base_url'].'img/smileys/gaydude.gif" alt=":gaydude:" title=":gaydude:" width="15" height="21" /> ',
			'nuse' => ' <img src="'.$globals['base_url'].'img/smileys/nusenuse.gif" alt=":nusenuse:" title=":nusenuse:" width="37" height="19" /> ',
			'smiley' => ' <img src="'.$globals['base_url'].'img/smileys/smiley.gif" alt=":-)" title=":-)" width="15" height="15" />',
			'wink' => ' <img src="'.$globals['base_url'].'img/smileys/wink.gif" alt=";)" title=";)"  width="15" height="15" /> ',
			'cheesy' => ' <img src="'.$globals['base_url'].'img/smileys/cheesy.gif" alt=":-&gt;" title=":-&gt;"  width="15" height="15" />',
			'grin' => ' <img src="'.$globals['base_url'].'img/smileys/grin.gif" alt=":-D" title=":-D" width="15" height="15" />',
			'oops' =>  ' <img src="'.$globals['base_url'].'img/smileys/embarassed.gif" alt="&lt;&#58;(" title="&#58;oops&#58; &lt;&#58;("  width="15" height="15" />',
			'cool' => ' <img src="'.$globals['base_url'].'img/smileys/cool.gif" alt="8-D" title=":cool: 8-D" width="15" height="15"/> ',
			'roll' => ' <img src="'.$globals['base_url'].'img/smileys/rolleyes.gif" alt=":roll:" title=":roll:"  width="15" height="15"/> ',
			'cry' => ' <img src="'.$globals['base_url'].'img/smileys/cry.gif" alt=":\'(" title=":cry: :\'("  width="15" height="15"/> ',
			'lol' => ' <img src="'.$globals['base_url'].'img/smileys/laugh.gif" alt="xD" title=":lol: xD"  width="15" height="15"/> ',
			'cheesy' => ' <img src="'.$globals['base_url'].'img/smileys/cheesy.gif" alt=":-&gt;" title=":-&gt;"  width="15" height="15" /> ',
			'angry' => ' <img src="'.$globals['base_url'].'img/smileys/angry.gif" alt="&gt;&#58;-(" title="&gt;&#58;-("  width="15" height="15" /> ',
			'huh' => ' <img src="'.$globals['base_url'].'img/smileys/huh.gif" alt="?(" title="?("  width="15" height="22" /> ',
			'sad' => ' <img src="'.$globals['base_url'].'img/smileys/sad.gif" alt=":-(" title=":-("  width="15" height="15" /> ',
			'shocked' => ' <img src="'.$globals['base_url'].'img/smileys/shocked.gif" alt=":-O" title=":-O"  width="15" height="15" />',
			'tongue' => ' <img src="'.$globals['base_url'].'img/smileys/tongue.gif" alt=":-P" title=":-P"  width="15" height="15" /> ',
			'lipssealed' => ' <img src="'.$globals['base_url'].'img/smileys/lipsrsealed.gif" alt=":-x" title=":-x"  width="15" height="15"/> ',
			'undecided' => ' <img src="'.$globals['base_url'].'img/smileys/undecided.gif" alt=":-/" title=":-/ :/"  width="15" height="15"/> ',
			'confused' => ' <img src="'.$globals['base_url'].'img/smileys/confused.gif" alt=":-S" title=":-S :S" width="15" height="15"/> ',
			'blank' => ' <img src="'.$globals['base_url'].'img/smileys/blank.gif" alt=":-|" title=":-| :|" width="15" height="15"/> ',
			'kiss' => ' <img src="'.$globals['base_url'].'img/smileys/kiss.gif" alt=":-*" title=":-* :*" width="15" height="15" /> ',
			'music' => ' <img src="'.$globals['base_url'].'img/smileys/music_note.png" alt=":8:" title=":8: (8)"  width="19" height="19" /> ',
			'roto' =>  ' <img src="'.$globals['base_url'].'img/smileys/roto2.gif" alt=":roto2:" title=":roto2:"  width="16" height="16" /> ',
			'trollface' =>  ' <img src="'.$globals['base_url'].'img/smileys/trollface.png" alt=":trollface:" title=":trollface:"  width="25" height="25" /> ',
			'yeah' => ' <img src="'.$globals['base_url'].'img/smileys/yeah.png" alt=":fuckyeah:" title=":fuckyeah:"  width="29" height="25" /> ',
			'alone' => ' <img src="'.$globals['base_url'].'img/smileys/forever.png" alt=":foreveralone:" title=":foreveralone:"  width="25" height="25" /> ',
			'troll' =>  ' <img src="'.$globals['base_url'].'img/smileys/troll.png" alt=":troll:" title=":troll:"  width="25" height="25" /> ',
			'longcat' =>  ' <img src="'.$globals['base_url'].'img/smileys/lolcat.png" alt=":longcat:" title=":longcat:"  width="25" height="25" /> ',
			'freising' =>  ' <img src="'.$globals['base_url'].'img/smileys/freising.jpg" alt=":freising:" title=":freising:"  width="40" height="40" /> ',
			'yaoface' =>  ' <img src="'.$globals['base_url'].'img/smileys/yaoface.jpg" alt=":yaoface:" title=":yaoface:"  width="30" height="30" /> ',
			'cejas' =>  ' <img src="'.$globals['base_url'].'img/smileys/cejas.gif" alt=":cejas:" title=":cejas:"  width="15" height="15" /> ',
			'sisitres' =>  ' <img src="'.$globals['base_url'].'img/smileys/sisi3.gif" alt=":sisi3:" title=":sisi3:"  width="15" height="15" /> '
        );
    }

    return isset($translations[$matches[1]]) ? $translations[$matches[1]] : $matches[0];

}

function normalize_smileys($str) {
    global $globals;

    $str=preg_replace('/(\s|^):ffu:/i', '$1{ffu}', $str);
    $str=preg_replace('/(\s|^):clint:/i', '$1{clint}', $str);
    $str=preg_replace('/(\s|^):nusenuse:/i', '$1{nuse}', $str);
    $str=preg_replace('/(\s|^):gaydude:/i', '$1{gaydude}', $str);
    $str=preg_replace('/(\s|^):yeah:/i', '$1{yeah}', $str);
    $str=preg_replace('/(\s|^):fuckyeah:/i', '$1{yeah}', $str);
    $str=preg_replace('/(\s|^):alone:/i', '$1{alone}', $str);
    $str=preg_replace('/(\s|^):8:/i', '$1{music}', $str);
    $str=preg_replace('/(\s|^):awesome:/i', '$1{awesome}', $str);
    $str=preg_replace('/(\s|^):sisi1:/i', '$1{sisi}', $str);
    $str=preg_replace('/(\s|^):roto2:/i', '$1{roto}', $str);
    $str=preg_replace('/(\s|^):trollface:/i', '$1{trollface}', $str);
    $str=preg_replace('/(\s|^):troll:/i', '$1{troll}', $str);
    $str=preg_replace('/(\s|^):palm:/i', '$1{palm}', $str);
    $str=preg_replace('/(\s|^):goatse:/i', '$1{goatse}', $str);
    $str=preg_replace('/(\s|^)o_o|:wow:/i', '$1{wow}', $str);
    $str=preg_replace('/(\s|^)¬¬|:shame:/i', '$1{shame}', $str);
    $str=preg_replace('/(\s|^):-{0,1}\)(\s|$)/i', '$1{smiley}$2', $str);
    $str=preg_replace('/(\s|^);-{0,1}\)(\s|$)/i', '$1{wink}$2', $str);
    $str=preg_replace('/(\s|^):-{0,1}&gt;/i', '$1{cheesy}', $str);
    $str=preg_replace('/(\s|^)(:-{0,1}D|:grin:)/i', '$1{grin}', $str);
    $str=preg_replace('/(\s|^)(:oops:|&lt;:\()/i', '$1{oops}', $str);
    $str=preg_replace('/(\s|^)&gt;:-{0,1}\((\s|$)/i', '$1{angry}$2', $str);
    $str=preg_replace('/(\s|^)\?(:-){0,1}\((\s|$)/i', '$1{huh}$2', $str);
    $str=preg_replace('/(\s|^):-{0,1}\((\s|$)/i', '$1{sad}$2', $str);
    $str=preg_replace('/(\s|^):-{0,1}O/', '$1{shocked}', $str);
    $str=preg_replace('/(\s|^)(8-{0,1}[D\)]|:cool:)/', '$1{cool}', $str);
    $str=preg_replace('/(\s|^):roll:/i', '$1{roll}', $str);
    $str=preg_replace('/(\s|^):-{0,1}P(|$)/i', '$1{tongue}$2', $str);
    $str=preg_replace('/(\s|^):-{0,1}x/i', '$1{lipssealed}', $str);
    $str=preg_replace('/(\s|^):-{0,1}\//i', '$1{undecided}', $str);
    $str=preg_replace('/(\s|^)(:\'\(|:cry:)/i', '$1{cry}', $str);
    $str=preg_replace('/(\s|^)(x-{0,1}D+|:lol:)/i', '$1{lol}', $str);
    $str=preg_replace('/(\s|^):-{0,1}S(\s|$)/i', '$1{confused}$2', $str);
    $str=preg_replace('/(\s|^):-{0,1}\|/i', '$1{blank}', $str);
    $str=preg_replace('/(\s|^):-{0,1}\*/i', '$1{kiss}', $str);
    $str=preg_replace('/(\s|^):longcat:/i', '$1{longcat}', $str);
    $str=preg_replace('/(\s|^):freising:/i', '$1{freising}', $str);
    $str=preg_replace('/(\s|^):yaoface:/i', '$1{yaoface}', $str);
    $str=preg_replace('/(\s|^):cejas:/i', '$1{cejas}', $str);
    $str=preg_replace('/(\s|^):sisi3:/i', '$1{sisitres}', $str);

    return $str;
}

// yawn :( php does not support the inline keyword. this is not precompiled, after all...
function get_cover_pixel() {
	global $globals;

	return $globals['base_url'].'img/estructura/pixel.gif';
}

// returns a nifty 'you have ** mins/secs remaining'
// $created_time: unixtime when the comment/post/whatever was added
// $edit_time: time, in seconds, that the user has to edit it (usually a $global but we dont have to take
// 	care of that here)
// WARNING: it supposes that gods can edit *everything*
function calc_remaining_edit_time($created_time, $edit_time, $geo=false) {
	global $globals, $current_user;

	$remaining_secs = $edit_time - ($globals['now'] - $created_time);
	$remaining_mins = round($remaining_secs / 60);

	if ($current_user->user_level == 'god')
		return 'god';

	if ($current_user->especial && $geo && $globals['now'] - $created_time < 14400) return 'especial';

	if ($remaining_secs < 90 && $remaining_secs > 60)
		return '1½ min';
	elseif ($remaining_secs < 60)
		return ($remaining_secs == 1) ? '1 seg, ¡corre!' : $remaining_secs.' segs';
	else
		return $remaining_mins.' mins';
}

function meta_get_current() {
	global $globals, $db;

	$globals['meta_current'] = 0;
	if (isset($_REQUEST['meta']) && $_REQUEST['meta'])
	$globals['meta']  = clean_input_string($_REQUEST['meta']);


	if (isset($_REQUEST['category']) && $_REQUEST['category']) {
		$_REQUEST['category'] = $cat = (int) $_REQUEST['category'];
		if ($globals['meta'][0] == '_') {
			$globals['meta_current'] = $globals['meta'];
		} else {
			$globals['meta_current'] = (int) $db->get_var("select category_parent from categories where category_id = $cat and category_parent > 0");
			$globals['meta'] = '';
		}
	}
	if (isset($globals['meta_current']) && $globals['meta_current'] > 0) {
		$globals['meta_categories'] = meta_get_categories_list($globals['meta_current']);
		if (!$globals['meta_categories']) {
			$globals['meta_current'] = 0;
		}
	}
	return $globals['meta_current'];
}

function meta_get_categories_list($id) {
	global $db;
	$categories = $db->get_col("SELECT category_id FROM categories WHERE category_parent = $id order by category_id");
	if (!$categories) return false;
	return implode(',', $categories);
}

function insert_clon($last, $previous, $ip='') {
    global $db;
    $db->query("REPLACE INTO clones (clon_from, clon_to, clon_ip) VALUES ($last, $previous, '$ip')");
    $db->query("INSERT IGNORE INTO clones (clon_to, clon_from, clon_ip) VALUES ($last, $previous, '$ip')");
}

function fork($uri) {
	global $globals;

	$sock = @fsockopen(get_server_name(), $_SERVER['SERVER_PORT'], $errno, $errstr, 0.01 );

	if ($sock) {
		@fputs($sock, "GET {$globals['base_url']}$uri HTTP/1.0\r\n" . "Host: {$_SERVER['HTTP_HOST']}\r\n\r\n");
		return true;
	}
	return false;
}

function stats_increment($type, $all=false) {
	global $globals, $db;

	if ($globals['save_pageloads']) {
		if(!$globals['bot'] || $all) {
			$db->query("insert into pageloads (date, type, counter) values (now(), '$type', 1) on duplicate key update counter=counter+1");
		} else {
			$db->query("insert into pageloads (date, type, counter) values (now(), 'bot', 1) on duplicate key update counter=counter+1");
		}
	}
}

// Json basic functions

function json_encode_single($dict) {
	$item = '{';
	$passed = 0;
	foreach ($dict as $key => $val) {
		if ($passed) { $item .= ','; } // como el primer passed es cero, no mete la coma
		$item .= $key . ':"' . $val . '"'; // agrega a $item
		$passed = 1; // ahora le dice que meta la coma
	}
	 return $item . '}';
}

// Generic function to get content from an url
function get_url($url, $referer = false, $max=200000) {
    global $globals;
    static $session = false;
    static $previous_host = false;
    $url = html_entity_decode($url);
    $parsed = parse_url($url);
    if (!$parsed) return false;
    if ($session && $previous_host != $parsed['host']) {
        curl_close($session);
        $session = false;
    }
    if (!$session) {
        $session = curl_init();
        $previous_host =  $parsed['host'];
    }
    $url = preg_replace('/ /', '%20', $url);
    curl_setopt($session, CURLOPT_URL, $url);
    curl_setopt($session, CURLOPT_USERAGENT, $globals['user_agent']);
    if ($referer) curl_setopt($session, CURLOPT_REFERER, $referer);
    curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($session, CURLOPT_HEADER , true);
    curl_setopt($session, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($session, CURLOPT_MAXREDIRS, 20);
    curl_setopt($session, CURLOPT_TIMEOUT, 20);
    curl_setopt($session, CURLOPT_FAILONERROR, true);
    // curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($session, CURLOPT_COOKIESESSION, true);
    // curl_setopt($session,CURLOPT_RANGE,"0-$max"); // It gives error with some servers
    $response = @curl_exec($session);
    if (!$response) {
            echo "<!-- CURL error " . curl_getinfo($session,CURLINFO_EFFECTIVE_URL) . ": " .curl_error($session) . " -->\n";
            return false;
    }
    $header_size = curl_getinfo($session,CURLINFO_HEADER_SIZE);
    $result['header'] = substr($response, 0, $header_size);
    $result['content'] = substr($response, $header_size, $max);

    if (preg_match('/Content-Encoding: *gzip/i', $result['header'])) {
            $result['content'] = gzBody($result['content']);
            echo "<!-- get_url gzinflating -->\n";
    }
    $result['http_code'] = curl_getinfo($session,CURLINFO_HTTP_CODE);
    $result['content_type'] = curl_getinfo($session, CURLINFO_CONTENT_TYPE);
    $result['redirect_count'] = curl_getinfo($session, CURLINFO_REDIRECT_COUNT);
    $result['location'] = curl_getinfo($session, CURLINFO_EFFECTIVE_URL);
    return $result;
}


// From http://es2.php.net/manual/en/function.gzinflate.php#77336
function gzBody($gzData){
    if(substr($gzData,0,3)=="\x1f\x8b\x08"){
        $i=10;
        $flg=ord(substr($gzData,3,1));
        if($flg>0){
            if($flg&4){
                list($xlen)=unpack('v',substr($gzData,$i,2));
                $i=$i+2+$xlen;
            }
            if($flg&8) $i=strpos($gzData,"\0",$i)+1;
            if($flg&16) $i=strpos($gzData,"\0",$i)+1;
            if($flg&2) $i=$i+2;
        }
        return gzinflate(substr($gzData,$i,-8));
    }
    else return false;
}

function clear_invisible_unicode($input){
    $invisible = array(
    "\0",
    "\xc2\xad", // 'SOFT HYPHEN' (U+00AD)
    "\xcc\xb7", // 'COMBINING SHORT SOLIDUS OVERLAY' (U+0337)
    "\xcc\xb8", // 'COMBINING LONG SOLIDUS OVERLAY' (U+0338)
    "\xcd\x8f", // 'COMBINING GRAPHEME JOINER' (U+034F)
    "\xe1\x85\x9f", // 'HANGUL CHOSEONG FILLER' (U+115F)
    "\xe1\x85\xa0", // 'HANGUL JUNGSEONG FILLER' (U+1160)

    "\xe2\x80\x8b", // 'ZERO WIDTH SPACE' (U+200B)
    "\xe2\x80\x8c", // 'ZERO WIDTH NON-JOINER' (U+200C)
    "\xe2\x80\x8d", // 'ZERO WIDTH JOINER' (U+200D)
    "\xe2\x80\x8e", // 'LEFT-TO-RIGHT MARK' (U+200E)
    "\xe2\x80\x8f", // 'RIGHT-TO-LEFT MARK' (U+200F)
    "\xe2\x80\xaa", // 'LEFT-TO-RIGHT EMBEDDING' (U+202A)
    "\xe2\x80\xab", // 'RIGHT-TO-LEFT EMBEDDING' (U+202B)
    "\xe2\x80\xac", // 'POP DIRECTIONAL FORMATTING' (U+202C)
    "\xe2\x80\xad", // 'LEFT-TO-RIGHT OVERRIDE' (U+202D)
    "\xe2\x80\xae", // 'RIGHT-TO-LEFT OVERRIDE' (U+202E)
    "\xe3\x85\xa4", // 'HANGUL FILLER' (U+3164)
    "\xef\xbb\xbf", // 'ZERO WIDTH NO-BREAK SPACE' (U+FEFF)
    "\xef\xbe\xa0", // 'HALFWIDTH HANGUL FILLER' (U+FFA0)
    "\xef\xbf\xb9", // 'INTERLINEAR ANNOTATION ANCHOR' (U+FFF9)
    "\xef\xbf\xba", // 'INTERLINEAR ANNOTATION SEPARATOR' (U+FFFA)
    "\xef\xbf\xbb", // 'INTERLINEAR ANNOTATION TERMINATOR' (U+FFFB)
    );

    return str_replace($invisible, '', $input);

}

function clear_unicode_spaces($input){
    $spaces = array(
    "\x9", // 'CHARACTER TABULATION' (U+0009)
    //  "\xa", // 'LINE FEED (LF)' (U+000A)
    "\xb", // 'LINE TABULATION' (U+000B)
    "\xc", // 'FORM FEED (FF)' (U+000C)
    //  "\xd", // 'CARRIAGE RETURN (CR)' (U+000D)
    "\x20", // 'SPACE' (U+0020)
    "\xc2\xa0", // 'NO-BREAK SPACE' (U+00A0)
    "\xe1\x9a\x80", // 'OGHAM SPACE MARK' (U+1680)
    "\xe1\xa0\x8e", // 'MONGOLIAN VOWEL SEPARATOR' (U+180E)
    "\xe2\x80\x80", // 'EN QUAD' (U+2000)
    "\xe2\x80\x81", // 'EM QUAD' (U+2001)
    "\xe2\x80\x82", // 'EN SPACE' (U+2002)
    "\xe2\x80\x83", // 'EM SPACE' (U+2003)
    "\xe2\x80\x84", // 'THREE-PER-EM SPACE' (U+2004)
    "\xe2\x80\x85", // 'FOUR-PER-EM SPACE' (U+2005)
    "\xe2\x80\x86", // 'SIX-PER-EM SPACE' (U+2006)
    "\xe2\x80\x87", // 'FIGURE SPACE' (U+2007)
    "\xe2\x80\x88", // 'PUNCTUATION SPACE' (U+2008)
    "\xe2\x80\x89", // 'THIN SPACE' (U+2009)
    "\xe2\x80\x8a", // 'HAIR SPACE' (U+200A)
    "\xe2\x80\xa8", // 'LINE SEPARATOR' (U+2028)
    "\xe2\x80\xa9", // 'PARAGRAPH SEPARATOR' (U+2029)
    "\xe2\x80\xaf", // 'NARROW NO-BREAK SPACE' (U+202F)
    "\xe2\x81\x9f", // 'MEDIUM MATHEMATICAL SPACE' (U+205F)

    "\xe3\x80\x80", // 'IDEOGRAPHIC SPACE' (U+3000)
    );

    return str_replace($spaces, ' ', $input);
}

function clear_whitespace($input){
    $input = clear_unicode_spaces(clear_invisible_unicode($input));
    return ereg_replace('/  +/', ' ', $input);
}

// From http://php.net/manual/en/function.get-browser.php
// There was no license, so I'll consider it in the Public Domain :P --neiKo

function browser_info($agent=null) {
  // Declare known browsers to look for
  $known = array('msie', 'firefox', 'safari', 'webkit', 'chrome', 'opera', 'netscape',
    'konqueror', 'gecko', 'shiretoko', 'iceweasel');

  // Clean up agent and build regex that matches phrases for known browsers
  // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
  // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
  $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
  $pattern = '#(?P<browser>' . join('|', $known) .
    ')[/ ]+(?P<version>[0-9]+(?:\.[0-9]+)?)#';

  // Find all phrases (or return empty array if none found)
  if (!preg_match_all($pattern, $agent, $matches)) return array();

  // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
  // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
  // in the UA).  That's usually the most correct.

  $i = count($matches['browser'])-1;
  if ($matches['browser'][$i - 1] == "chrome") $i--; // Para que no detecte Chrome como Safari
  return array($matches['browser'][$i] => $matches['version'][$i]);

}

function do_post_video($textNotita) {
	global $globals;

	//if (!$globals['do_video']) return $textNotita;
	$regExpUrls = array();

	// webs, sus expresiones regulares y códigos de inserción
	// youtube // formato http://www.youtube.com/embed/c7c_OXivqSk?rel=0 embed/watch?v= [11 caracteres alfanuméricos + guión]
	$regExpUrls = "/(http\:\/\/)(www.)?(youtube)\.(com|es)\/watch\?v\=([a-zA-Z0-9\-\_]{11})([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i";

	// ahora utilizo la expresión regular para comprobar que la url enviada está bien formada
   	if (preg_match($regExpUrls, $textNotita, $cachos) == 1) // la comparación es correcta
	  {
	    // $urlVideo = convertUrl($textNotita, $j, $cachos);
        // get_embed_code($textNotita, $j);
        $embedCode = get_embed_code("http://www.youtube.com/embed/{$cachos[5]}&rel=0"); // le paso a la función la url, el contador y los cachos que ha dado el preg

        // preg_replace(expresión regular, string de reemplazo, string a reemplazar)
        $textNotita = preg_replace($regExpUrls[$j], $embedCode, $textNotita);

	}
	return $textNotita;
}

function do_jonevision_convert($textNotita) {
	global $globals;


	$regExpUrls = array();

	// webs, sus expresiones regulares y códigos de inserción
	// youtube // formato url/watch?v= [11 caracteres alfanuméricos + guión]
	$regExpUrls[0] = "/(http\:\/\/)(www.)?(youtube)\.(com|es)\/watch\?v\=([a-zA-Z0-9\-\_]{11})([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i";

	// google video // formato url/videoplay?docid= [18 números]
	$regExpUrls[1] = "/(http\:\/\/)(www.)?(video\.google)\.(com|es)\/videoplay\?docid\=([0-9]{18})([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.\#]*)/i";

	// zappinternet // formato url/video/ [10 caracteres alfabéticos] / [cualquier texto]
	$regExpUrls[2] = "/(http\:\/\/)(www.)?(zappinternet)\.com\/video\/([a-zA-Z]{10})\/(.*?)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i";

	// daily motion // formato url/ [cualquier texto] /video/ [6 caracteres alfanuméricos en minúsculas] / [cualquier texto]
	$regExpUrls[3] = "/(http\:\/\/)(www.)?(dailymotion)\.com\/?(.*?)\/video\/([a-z0-9]{6})([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i";

	// goear // http://www.goear.com/listen/de6a21f/
	$regExpUrls[4] = "/(http\:\/\/)(www.)?(goear)\.com\/listen\/([a-z0-9]{7})([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i";

	// chorrada instantrimshot
	$regExpUrls[5] = "/(\:instantrimshot\:)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i";

	// chorrada instantgrillos
	$regExpUrls[6] = "/(\:grillos\:)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i";

	// chorrada instant desierto
	$regExpUrls[7] = "/(\:desierto\:)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i";

	// ahora utilizo la expresión regular para comprobar que la url enviada está bien formada
	for ($j=0; $j<sizeOf($regExpUrls); $j++)
	{
      	if (preg_match($regExpUrls[$j], $textNotita, $cachos) == 1) // la comparación es correcta
	  {
	    // $urlVideo = convertUrl($textNotita, $j, $cachos);
        // get_embed_code($textNotita, $j);
        $embedCode = get_embed_code(convertUrl($textNotita, $j, $cachos), $j); // le paso a la función la url, el contador y los cachos que ha dado el preg

        // preg_replace(expresión regular, string de reemplazo, string a reemplazar)
        $textNotita = preg_replace($regExpUrls[$j], $embedCode, $textNotita);
	  }
	}
	return $textNotita;
}


// funcion para convertir la url de video normal a la url del embedded
function convertUrl($urlIntro, $webCounter, $cachos)
{
	switch ($webCounter)
	{
	 /* youtube */
	 case 0: return "http://www.youtube.com/v/{$cachos[5]}&hl=es&fs=1&"; break;
	 /* google video */
	 case 1: return "http://video.google.es/googleplayer.swf?docid={$cachos[5]}&hl=es&fs=true"; break;
	 /* zappinternet */
	 case 2: return "http://zappinternet.com/v/{$cachos[4]}"; break;
	 /* dailymotion */
	 case 3: return "http://www.dailymotion.com/swf/{$cachos[5]}"; break;
	/* goear */
	case 4: return "http://www.goear.com/files/external.swf?file={$cachos[4]}"; break;

	default: return "";
	}
}


// funcion para obtener el código de embebido del vídeo
function get_embed_code($urlVideo, $webCounter)
{
	// variables de alto y ancho para todos los vídeos
	$width = "205";
	$height= "178";
	// variable para contener los distintos códigos de embebido
	$insertionCode = "";
	if (($webCounter >= 0) && ($webCounter <= 3)) // Vídeos
	    {  $insertionCode = "<div align=\"center\"><div class=\"embeddedVideo\">"; }

	elseif ($webCounter >= 4) // Audio y otros
	    {  $insertionCode = "<div align=\"center\">"; }

        switch ($webCounter)
        {
            // youtube
            case 0: $insertionCode .= "<object width=\"{$width}\" height=\"{$height}\"><param name=\"wmode\" value=\"transparent\"/><param name=\"movie\"   value=\"{$urlVideo}\"&hl=es&fs=1&rel=0&color1=0x2b405b&color2=0x6b8ab6\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"{$urlVideo}\"&hl=es&fs=1&rel=0&color1=0x2b405b&color2=0x6b8ab6\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"{$width}\" height=\"{$height}\" wmode=\"transparent\"></embed></object>";
                    break;

            // google video
            case 1: $insertionCode .= "<embed id=VideoPlayback src=\"{$urlVideo}\"&hl=es&fs=true style=width:{$width}px;height:{$height}px allowFullScreen=true allowScriptAccess=always type=application/x-shockwave-flash wmode=\"transparent\"></embed>";
                    break;

            // zappinternet
            case 2: $insertionCode .= "<object type=\"application/x-shockwave-flash\" data=\"{$urlVideo}\" height=\"{$height}\" width=\"{$width}\"><param name=\"movie\" value=\"{$urlVideo}\" /><param name=\"allowFullScreen\" value=\"true\" /><param name=\"wmode\" value=\"transparent\"/></object>";
                    break;

            // dailymotion
            case 3: $insertionCode .= "<object width=\"{$width}\" height=\"{$height}\"><param name=\"wmode\" value=\"transparent\"/><param name=\"movie\" value=\"{$urlVideo}\" /><param name=\"allowFullScreen\" value=\"true\" /><param name=\"allowScriptAccess\" value=\"always\" /><embed src=\"{$urlVideo}\" type=\"application/x-shockwave-flash\" width=\"{$width}\" height=\"{$height}\" allowFullScreen=\"true\" allowScriptAccess=\"always\" wmode=\"transparent\"></embed></object>";
                    break;

            // goear
	    case 4: $insertionCode .= "<object width=\"353\" height=\"132\"><embed src=\"{$urlVideo}\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" quality=\"high\" width=\"353\" height=\"132\"></embed></object>"; break;

	// instantrimshot
	case 5: $insertionCode .= "<object width=\"75\" height=\"75\"><param name=\"movie\" value=\"/swf/rimshot.swf\" /><param name=\"quality\" value=\"high\" /><embed src=\"/swf/rimshot.swf\" width=\"75\" height=\"75\" align=\"middle\" quality=\"high\" wmode=\"transparent\"></embed></object>"; break;

	// instant grillos
	case 6: $insertionCode .= "<object width=\"40\" height=\"40\"><param name=\"movie\" value=\"/swf/crickets.swf\" /><param name=\"quality\" value=\"high\" /><embed src=\"/swf/crickets.swf\" width=\"40\" height=\"40\" align=\"middle\" quality=\"high\" wmode=\"transparent\"></embed></object>"; break;

	// instant silencio
	case 7: $insertionCode .= "<object width=\"75\" height=\"75\"><param name=\"movie\" value=\"/swf/estropajo.swf\" /><param name=\"quality\" value=\"high\" /><embed src=\"/swf/estropajo.swf\" width=\"75\" height=\"75\" align=\"middle\" quality=\"high\" wmode=\"transparent\"></embed></object>"; break;

        }
        $insertionCode .= "</div>";
	if ($webCounter < 4) $insertionCode .= "</div>";

	return $insertionCode;
}

/* TODO Altamente mejorable */
function get_polls_unvoted(){
	global $db, $current_user;

	$db->cache_queries = true;
	//$pools = $db->get_col("SELECT encuesta_id from encuestas WHERE encuesta_start > date_sub(now(), interval 7*24 hour) ");
	$pools = $db->get_col("SELECT encuesta_id from encuestas WHERE encuesta_start > date_sub(now(), interval 30*24 hour) ");
	$sum = 0;

	if (!$pools){
	return 0;
	}

	foreach ($pools as $pool){

	$voted = $db->get_var("SELECT count(*) FROM encuestas_votes WHERE uid = '".intval($current_user->user_id)."' AND pollid = '".$pool."'");
		if ($voted > 0) {
	 		continue;

		}
		$sum ++;
	}

	$db->cache_queries = false;

	return $sum;

}

function get_comment_unread_conversations() {
        global $db, $current_user;

        $key = 'c_last_read';

        if ($current_user->user_id > 0) $user = $current_user->user_id;
        $last_read = intval($db->get_var("select pref_value from prefs where pref_user_id = $user and pref_key = '$key'"));
        $n = (int) $db->get_var("select count(*) from conversations where conversation_user_to = $user and conversation_type = 'comment' and conversation_time > FROM_UNIXTIME($last_read)");
        return $n;

}

function get_post_unread_conversations() {
        global $db, $current_user;
        $key = 'p_last_read';

        if ($current_user->user_id > 0) $user = $current_user->user_id;

        $last_read = intval($db->get_var("select pref_value from prefs where pref_user_id = $user and pref_key = '$key'"));
        $n = (int) $db->get_var("select count(*) from conversations where conversation_user_to = $user and conversation_type = 'post' and conversation_time > FROM_UNIXTIME($last_read)");
        return $n;
    }

function print_oauth_icons($return = false) {
    global $globals, $current_user;

    $globals['uri'] = preg_replace('/[<>\r\n]/', '', urldecode($_SERVER['REQUEST_URI'])); // clean  it for future use
    if ($globals['oauth']['twitter']['consumer_key']) {
        $title = false;
        if (! $return) $return = $globals['uri'];
        if ($current_user->user_id) {
            // Check the user is not already associated to Twitter
            if (! $current_user->GetOAuthIds('twitter')) {
                $title = _('asociar la cuenta a Twitter, podrás autentificarte también con tu cuenta en Twitter');
                $text = _('asociar a Twitter');
            }
        } else {
            $title = _('crea una cuenta o autentifícate desde Twitter');
            $text = _('login con Twitter');
        }
        if ($title) {
            echo '<a href="'.$globals['base_url'].'login/signin.php?service=twitter&amp;op=init&amp;return='.$return.'" title="'.$title.'">';
            echo '<img style="vertical-align:middle;" src="'.$globals['base_url'].'img/v2/signin-twitter2.png" width="89" height="21" alt=""/></a>&nbsp;&nbsp;'."\n";
        }
    }
    if ($globals['facebook_key']) {
        $title = false;
        if (! $return) $return = $globals['uri'];
        if ($current_user->user_id) {
            // Check the user is not already associated to Twitter
            if (! $current_user->GetOAuthIds('facebook')) {
                $title = _('asociar la cuenta a Facebook, podrás autentificarte también con tu cuenta en Facebook');
                $text = _('asociar a Facebook');
            }
        } else {
            $title = _('crea una cuenta o autentifícate desde Facebook');
            $text = _('login con Facebook');
        }
        if ($title) {
            echo '<a href="'.$globals['base_url'].'login/fbconnect.php?return='.$return.'" title="'.$title.'">';
            echo '<img style="vertical-align:middle" src="'.$globals['base_url'].'img/v2/signin-fb.gif" width="89" height="21" alt=""/></a>&nbsp;&nbsp;'."\n";
        }
    }
}

function check_queue($user_id){
     global $db, $globals;

     $envios_consecutivos = 0;
     $previous_user = 0;
     $joneos = $db->get_results("select link_author from links where link_status='queued' and link_date > date_sub(now(), interval 12 hour) ORDER BY link_date DESC");

     foreach ($joneos as $link){

	$previous_user = $link->link_author;

     	if ($link->link_author == $user_id && $previous_user == $user_id){
		$envios_consecutivos = $envios_consecutivos + 1;

     	} else $envios_consecutivos = 0; // reiniciar contador
     }

     if ($envios_consecutivos >= $globals['max_successive_links_in_queue']){
	return true;
     }

    return false;
}

function backend_call_string($program,$type,$page,$id) {
     // It replaces the get_votes function
     // it generates the string to link to a backend program given its arguments
     global $globals;

     return $globals['base_url']."backend/$program?id=$id&amp;p=$page&amp;type=$type";

}
