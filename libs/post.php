<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once(mnminclude.'favorites.php');
class Post {
    var $id = 0;
    var $randkey = 0;
    var $author = 0;
    var $date = false;
    var $votes = 0;
    var $voted = false;
    var $karma = 0;
    var $content = '';
    var $src = 'web';
    var $read = false;
    var $can_answer = false;

    const SQL = " post_id as id, post_user_id as author, post_is_answer as is_answer, user_login as username, user_karma, user_level as user_level, user_level as level, post_randkey as randkey, post_votes as votes, post_karma as karma, post_src as src, inet_ntoa(post_ip_int) as ip, post_type as tipo, user_avatar as avatar, post_content as content, UNIX_TIMESTAMP(posts.post_date) as date, favorite_link_id as favorite, vote_value as voted FROM posts
    LEFT JOIN users on (user_id = post_user_id)
    LEFT JOIN favorites ON (@user_id > 0 and favorite_user_id =  @user_id and favorite_type = 'post' and favorite_link_id = post_id)
    LEFT JOIN votes ON (post_date > @enabled_votes and @user_id > 0 and vote_type='posts' and vote_link_id = post_id and vote_user_id = @user_id)";

    const SQL_BASIC = " post_id as id, post_user_id as author, user_login as username, post_karma as karma, post_type as tipo, user_avatar as avatar, post_content as content, post_src as src, UNIX_TIMESTAMP(posts.post_date) as date FROM posts LEFT JOIN users on (user_id = post_user_id) ";

    static function from_db($id) {
            global $db;

        if (is_numeric($id) && $id > 0) $selector = " post_id = $id ";

        if(($object = $db->get_object("SELECT".Post::SQL." WHERE $selector", 'Post'))) {
            $object->read = true;
            if ($object->src == 'im') $object->src = 'jabber';
            return $object;
        }
        return false;
        }


    static function update_read_conversation() {
        global $db, $globals, $current_user;
        $key = 'p_last_read';

        if (! $current_user->user_id ) return false;
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

        if(!$this->date) $this->date=time();
        $post_author = $this->author;
        $post_src = $this->src;
        $post_karma = $this->karma;
        $post_date = $this->date;
        $post_randkey = $this->randkey;
        if ($this->tipo == 'admin' || $_POST['type'] == 'admin' && $current_user->admin) $tipo = 'admin';
        else if ($this->tipo == 'encuesta') $tipo = 'encuesta';
        else if ($this->tipo == 'normal' || !$_POST['type'] ) $tipo = 'normal';

        $this->answer_from = $this->is_answer();

        if ($this->answer_from > 0 ) {

             if ($this->re_answer()){
             $is_answer = 1;


             } else $is_answer = 0;
        }      else $is_answer = 0;

        $post_content = $db->escape(normalize_smileys(clean_lines(clear_whitespace($this->content))));

        if($this->id===0) {
            $this->ip = $globals['user_ip_int'];
            $db->query("INSERT INTO posts (post_user_id, post_karma, post_ip_int, post_date, post_randkey, post_src, post_content, post_type, post_is_answer, post_last_answer) VALUES ($post_author, $post_karma, $this->ip, FROM_UNIXTIME($post_date), $post_randkey, '$post_src', '$post_content', '$tipo', $is_answer, now())");

            /* Si es una respuesta sube su notita 'padre' al primer puesto */
            if ($is_answer){
                $db->query("UPDATE posts set post_last_answer=now() WHERE post_id=$this->answer_from");
            }

            $this->id = $db->insert_id;

            // Insert post_new event into logs
            log_insert('post_new', $this->id, $post_author);
        } else {
            $db->query("UPDATE posts set post_user_id=$post_author, post_type='$tipo', post_karma=$post_karma, post_date=FROM_UNIXTIME($post_date), post_randkey=$post_randkey, post_content='$post_content', post_is_answer=$is_answer WHERE post_id=$this->id");
            // Insert post_edit event into logs
            log_conditional_insert('post_edit', $this->id, $post_author, 30);
        }
            $this->update_conversation();
            if ($is_answer)  $this->insert_answer();
            else $this->delete_answer();
    }

    function read() {
        global $db;
        $id = $this->id;
        if(($post = $db->get_row("SELECT posts.*, UNIX_TIMESTAMP(posts.post_date) as date, inet_ntoa(post_ip_int) as ip, users.user_login, users.user_avatar, users.user_level, user_karma FROM posts, users WHERE post_id = $id and user_id = post_user_id"))) {
            $this->author=$post->post_user_id;
            $this->username=$post->user_login;
            $this->user_karma=$post->user_karma;
            $this->randkey=$post->post_randkey;
            $this->votes=$post->post_votes;
            $this->karma=$post->post_karma;
            $this->src=$post->post_src;
            $this->ip=$post->ip;
            $this->tipo=$post->post_type;
            $this->avatar=$post->user_avatar;
            $this->content=$post->post_content;
            $this->date=$post->date;
            $this->level=$post->user_level;
            if ($this->src == 'im') $this->src = 'jabber';
            $this->read = true;
            return true;
        }
        $this->read = false;
        return false;
    }

    function read_basic() {
        global $db;
        $id = $this->id;
        if(($post = $db->get_row("SELECT ".Post::SQL_BASIC." WHERE post_id = $id "))) {

            foreach(get_object_vars($post) as $var => $value) $this->$var = $value;

            if ($this->src == 'im') $this->src = 'jabber';
            $this->read = true;
            return true;
        }
        $this->read = false;
        return false;
    }

    function read_last($user=0) {
        global $db;
        $id = $this->id;
        if ($user > 0) {
            $sql = "select post_id from posts where post_user_id = $user and post_type != 'admin' order by post_date desc limit 1";
        } else {
            $sql = "select post_id from posts WHERE post_type != 'admin' order by post_date desc limit 1";
        }
        $id = $db->get_var($sql);
        if ($id > 0) {
            $this->id = $id;
            return $this->read();
        }
        return false;
    }

    function print_summary($length = 0) {
        global $current_user, $globals;

        if(!$this->read) $this->read();

        require_once(mnminclude.'user.php');

        echo '<li id="pcontainer-'.$this->id.'">';

        $this->hidden = $this->karma < -50 || $this->level == 'disabled';
        /* $this->ignored = $current_user->user_id > 0 && friend_exists($current_user->user_id, $this->author) < 0; */
        $this->ignored = false;

        $post_class = 'notita-body fondo-caja';
        $post_meta_class = $post_meta_class_link = '';

        if ($this->karma > 90) {  //resaltado si carisma alto
            $post_class .= ' high';
            $post_meta_class .= ' high';
            $post_meta_class_link = ' high';
        }

        if ($this->tipo == 'admin') {  //resaltado si admin
            $post_class .= ' admin';
            $post_meta_class .= ' admin';
            $post_meta_class_link = ' admin';
        }

        $post_meta_class = 'barra';

        if (($this->hidden || $this->ignored || $this->level == 'disabled') && $this->tipo != 'admin') {
            $post_class .= ' blanqueado';
            $post_meta_class .= ' blanqueado';
            $post_meta_class_link .= ' blanqueado';
        }

        // The comments info bar
        echo '<div class="'.$post_meta_class.'">';

        // Print comment info (right)
        echo '<div class="notitas-info">';

        // !!HACKHACK -- Para no tener que cambiar la BD actual se compara con '' --neiKo
        if ($this->tipo == 'normal' || $this->tipo == '' || $this->tipo == 'encuesta') {
            if ($this->level == 'disabled')
                echo '<a href="'.post_get_base_url($this->username).'" class="uname'.$post_meta_class_link.'"><s>' . $this->username.'</s></a> ';
            else
                echo '<a href="'.post_get_base_url($this->username).'" class="uname'.$post_meta_class_link.'">' . $this->username.'</a> ';
        } else {
            if ($current_user->admin)
                echo '<a href="'.post_get_base_url($this->username).'" class="uname'.$post_meta_class_link.'">' . 'admin ('.$this->username.')'.'</a> ';
            else
                echo '<strong>'. 'admin '.'</strong>';
        }

        // Print dates
        if (time() - $this->date > 604800) { // 7 días
            echo _('el').get_date_time($this->date);
        } else {
            echo _('hace').' '.txt_time_diff($this->date);
        }

        if ($current_user->user_level=='god' && $this->src == 'web') echo " ($this->ip) ";

        echo '&nbsp;&nbsp;';

        if ($this->tipo == 'admin') $this->username = 'admin';

        if ($this->tipo != 'encuesta')
        echo '<a href="'.post_get_base_url($this->username).'/'.$this->id.'" title="permalink" class="'.$post_meta_class_link.'"><img class="icon permalink img-flotante" src="'.get_cover_pixel().'"/></a>&nbsp;';

        if ($current_user->user_id > 0 && $this->tipo != 'encuesta')
            echo '&nbsp;&nbsp;<a id="fav-'.$this->id.'" href="javascript:obtener(\'notita_favorito.php\',\''.$current_user->user_id.'\',\'fav-'.$this->id.'\',0,\''.$this->id.'\')">'.favorite_icon($this->favorite, 'post').'</a>';

        if ($this->tipo == 'admin')
            $usuario = 'admin';
        else
            $usuario = $this->username;

        if ($this->tipo == 'encuesta')
        echo '<a href="'.$globals['base_url'].'encuestas.php"><img src="'.get_cover_pixel().'" class="icon poll-new" /></a>'."";

        if ($this->is_answer()) $reference_id =$this->is_answer(); else $reference_id = $this->id;
        $referencia = '@'.$usuario.','.$reference_id.' ';
        // Reply button
        if ($current_user->user_id > 0 && $this->tipo != 'encuesta' && $this->can_answer == true)
            echo '&nbsp;<a href="javascript:respuesta('.$reference_id.',\''.$referencia.'\')" title="'._('responder').'"><img class="icon post-reply img-flotante" src="'.get_cover_pixel().'" style="margin-left: 5px;"/></a>';

        echo '</div>';

        // Print the votes info
        echo '<div class="notitas-votes-info">';
        // Check that the user can vote
        if ($current_user->user_id > 0 && $this->author != $current_user->user_id && $this->tipo != 'admin')
                    $this->print_shake_icons();

        if ($this->tipo != 'admin') echo _('votos').': <span id="vc-'.$this->id.'" >'.$this->votes.'</span>, carisma: <span id="vk-'.$this->id.'">'.$this->karma.'</span>';

        // Add the icon to show votes
        if ($this->votes > 0 && $this->date > $globals['now'] - 30*86400 && $this->tipo != 'admin') { // Show votes if newer than 30 days
            echo '&nbsp;&nbsp;<a class="fancybox" href="'.$globals['base_url'].'backend/mostrar_notitas_votos.php?id='.$this->id.'">';
            echo '<img class="icon info img-flotante" src="'.get_cover_pixel().'" style="margin-top: -2px;" alt="+ info" title="'._('¿quién ha votado?').'"/>';
            echo '</a>';
        }

        echo '</div></div>';

        echo '<div class="'.$post_class.'" id="pid-'.$this->id.'">';

        if ($this->ignored
            || ($this->hidden && ($current_user->user_comment_pref & 1) == 0)
            && $this->tipo != 'admin' ) { // no grisees notitas de admin!!!
            $this->print_user_avatar();
            echo '<div class="notita-text">';
            if ($this->ignored) echo _('notita <b>ignorada</b>');
            elseif ($this->hidden) echo _('notita <b>sensurada</b>').', '.$this->karma.' '._('de carisma');
            echo '<br/><a href="javascript:obtener(\'mostrar_notita.php\',\'post\',\'pid-'.$this->id.'\',0,'.$this->id.')">';
            echo '» clic aquí para verla</a>';
            echo '</div>';
        } else {
            if ($this->tipo != 'admin')
                $this->print_user_avatar();
            else
                echo '<div class="user-avatar"><img src="'.get_admin_avatar(40).'"/></div>';

            echo '<div class="notita-text">';
            $this->print_text($length);
            echo '</div>';
        }


        if ($current_user->user_id > 0 && $globals['reports_notitas'])
            echo '<a href="'.$globals['base_url'].'report.php?p='.$this->id.'"><img class="reportar" src="'.$globals['base_url'].'img/iconos/report.gif" title="Reportar notita"/></a>';


        echo '</div>';
        echo "</li>\n";
    }

    function print_user_avatar() {
        global $globals;

        if (is_connected($this->author))
            $conectado = '<a class="conectado" href="'.$globals['base_url'].'cotillona.php" title="'.$this->username.' está actualmente en la cotillona"></a>';
        else $conectado = '';

        echo '<a class="user-avatar" href="'.get_user_uri($this->username).'"><span><img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$this->author.');" onmouseout="tooltip.clear(event);" class="avatar" src="'.get_avatar_url($this->author, $this->avatar, 40).'" width="40" height="40" alt="'.$this->username.'"/></span></a>'.$conectado;
    }

    function print_text($length = 0) {
        global $current_user, $globals;

        $notitas_edit_time = 3600;
        $expand = null;

        if (($this->author == $current_user->user_id &&
            time() - $this->date < $notitas_edit_time ) ||
            $current_user->user_level == 'god') {

            if ($current_user->user_level == 'god')
                $iddqd = ' iddqd';

            $expand = '&nbsp;&nbsp;<a href="javascript:editar('.$this->id.')" title="'._('editar la notita').'"><span class="c-edit'.$iddqd.'">'.calc_remaining_edit_time($this->date, $notitas_edit_time).'</span></a>';
        }

        echo put_smileys($this->put_tooltips(save_text_to_html($this->content, 'posts'))) . $expand;
        echo "\n";
    }

   function put_tooltips ($str) {
        return preg_replace_callback('/(^|\s)@([\S\.\-]+\w)/u', array($this, 'replace_post_link'), $str);
    }

   function replace_post_link($matches) {
            global $globals;

            $pre = $matches[1];
            $a = explode(',', $matches[2]);
            if (count($a) > 1) {
                $user = $a[0];
                $id = ','.$a[1];
            } else {
                $user = $matches[2];
                $id = '';
            }
            return "$pre<a class='tt' href='".$globals['base_url']."backend/obtener_notita.php?id=$user$id-".$this->date."' onmouseover=\"return tooltip.ajax_delayed(event, 'get_post_tooltip.php', '$user".$id.'-'.$this->date."');\" onmouseout=\"tooltip.hide(event);\">@$user</a>";
    }

   function print_edit_form() {
        global $globals, $current_user;
        echo '<div class="commentform" id="edit-form">'."\n";
        echo '<fieldset class="fondo-caja redondo clearleft"><legend class="sign mini barra redondo">';

        if ($this->id == 0) {
         $randform = $this->randkey = rand(1000000,100000000); // para evitar errores de JS si 2 form se llaman igual
        } else  $randform = $this->id;

    if ($this->id == 0 && $this->content) {
            echo _('responder a');
        } else if ($this->id == 0) {
            echo _('nueva notita');

        } else  echo _('editar notita');

        echo '</legend>';
        echo '<form action="'.$globals['base_url'].'backend/post_edit.php?user='.$current_user->user_id.'" method="post" id="thisform'.$randform.'" name="thisform'.$randform.'">'."\n";
        echo '<input type="hidden" name="key" value="'.$this->randkey.'" />'."\n";
        echo '<input type="hidden" name="post_id" value="'.$this->id.'" />'."\n";
        echo '<input type="hidden" name="user_id" value="'.$this->author.'" />'."\n";
        echo '<textarea name="post" rows="6" cols="40" id="post" onKeyDown="textCounter(document.thisform'.$randform.'.post,document.thisform'.$randform.'.postcounter,'.$globals['longitud_notitas'].')" onKeyUp="textCounter(document.thisform'.$this->id.'.post,document.thisform'.$randform.'.postcounter,'.$globals['longitud_notitas'].')">'.$this->content.'</textarea>'."\n";
        $body_left = $globals['longitud_notitas'] - mb_strlen(html_entity_decode($this->content, ENT_COMPAT, 'UTF-8'), 'UTF-8');
        echo '<input readonly type="text" name="postcounter" size="3" maxlength="3" value="'. $body_left . '" /> <span class="note">' . _('caracteres libres') . '</span>';

    print_simpleformat_buttons('post');
        echo '&nbsp;&nbsp;&nbsp;';

        echo '<input class="button" type="submit" value="'._('enviar').'" />'."\n";

    if ($this->tipo =='admin') $checked= 'checked="checked"';
    if ($current_user->admin ) echo '&nbsp;&nbsp;&nbsp;&nbsp;<input name="type" type="checkbox"'.$checked.'value="admin" id="notita-admin"/>&nbsp;<label for="type">'._('notita admin').'</strong></label>'."\n";

        echo '</form>'."\n";
        echo '</fieldset>'."\n";
        echo '</div><br/>'."\n";

        echo'<script type="text/javascript">'."\n";

        // prepare Options Object
        if ($this->id == 0) {

        echo 'var longitud=document.thisform'.$randform.'.post.value.length;';
        echo 'if(longitud>0){'; // es una respuesta
        echo 'var element = document.getElementById("edit-form").parentNode.id;';
        echo 'var options = {success:  function(response) {    if (/^ERROR:/.test(response)) alert(response);else { $("#"+element).html(response);}} };  ';

        echo '} else {var options = {success:  function(response) {if (/^ERROR:/.test(response)) alert(response); else { $("#newpost").html(response); $("#addpost").hide("fast"); } } }; }'; // nueva notita

        }  else {
            echo 'var options = {success:  function(response) {if (/^ERROR:/.test(response)) alert(response); else { $("#pcontainer-'.$this->id.'").html(response); } } }; ';
        }

        // wait for the DOM to be loaded
        echo'$(\'#thisform'.$randform.'\').ajaxForm(options);' ."\n";

        echo '</script>'."\n";
    }


   function print_post_teaser($rss_option) {
        global $globals, $current_user;


        echo '<div id="addpost">';
        // Print "new note" is the user is authenticated
    echo '<ul class="barra redondo herramientas">';

        if ($current_user->user_id > 0) {
            if ((!$this->read_last($current_user->user_id) || time() - $this->date > $globals['tiempo_entre_notitas']) || $current_user->admin) {
        echo '<li><a href="javascript:nueva()" class="icon post-new">escribir nueva notita</a></li>';
            } else {
        echo '<li><a href="javascript:;" class="icon hglass">espera un poco...</a></li>';
            }
        }
    echo '<li><a href="'.$globals['base_url'].'sneakme_rss2.php'.$rss_option.'" class="icon rss">notitas en RSS</a></li>';
    echo '<li><a href="'.$globals['base_url'].'ayuda.php?id=faq#jabber" class="icon jabber">jabber/gtalk para las notitas</a></li>';
    echo '</ul><br/><br/><br/>';
        echo '</div>'."\n";
        if ($current_user->user_id > 0) {
            echo '<ol class="notitas-list" id="newpost"></ol>'."\n";
        }
    }

    function clean_content() {
        // Clean other post references
        return preg_replace('/(@[\S.-]+)(,\d+)/','$1',$this->content);
    }

    function vote_exists() {
        global $current_user;
        require_once(mnminclude.'votes.php');

        $vote = new Vote;
        $vote->user=$current_user->user_id;
        $vote->type='posts';
        $vote->link=$this->id;
        return $vote->exists();     //devuelve el valor del voto si existe, y false si no existe
    }


    function insert_vote() {
        global $current_user;
        require_once(mnminclude.'votes.php');
        $vote = new Vote;
        $vote->user = $current_user->user_id;
        $vote->type='posts';
        $vote->link=$this->id;
        if ($vote->exists()) {
            return false;
        }
        $vote->value = $current_user->user_karma;
        if($vote->insert()) return true;
        return false;
    }

    function print_shake_icons() {
        global $globals, $current_user;

        if ( $current_user->user_karma > $globals['min_karma_for_comment_votes'] && $this->date > time() - $globals['time_enabled_note_votes'] && $this->voted === null) {
            echo '<span id="c-votes-'.$this->id.'">';
            echo '<a href="javascript:votar_notita('."$current_user->user_id,$this->id,-1".')" title="'._('voto negativo').'"><img class="icon vote-down" src="'.get_cover_pixel().'" alt="'._('voto negativo').'"/></a>&nbsp;';
            echo '<a href="javascript:votar_notita('."$current_user->user_id,$this->id,1".')" title="'._('voto positivo').'"><img class="icon vote-up" src="'.get_cover_pixel().'" alt="'._('voto positivo').'"/></a>';
            echo '</span>&nbsp;&nbsp;';
        } else {

            if ($this->voted > 0 && $this->date > time() - $globals['time_enabled_note_votes'] ) {
                echo '<img class="icon voted-up" src="'.get_cover_pixel().'" alt="'._('votado positivo').'" title="'._('votado positivo').'"/>&nbsp;&nbsp;';
            } else if ($this->voted < 0 && $this->date > time() - $globals['time_enabled_note_votes']  ) {
                echo '<img class="icon voted-down" src="'.get_cover_pixel().'" alt="'._('votado negativo').'" title="'._('votado negativo').'"/>&nbsp;&nbsp;';
            }
        }
    }


    function same_text_count($min=30) {
        global $db;
        // WARNING: $db->escape(clean_lines($comment->content)) should be the sama as in libs/comment.php (unify both!)
        return (int) $db->get_var("select count(*) from posts where post_user_id = $this->author and post_date > date_sub(now(), interval $min minute) and post_content = '".$db->escape(clean_lines($this->content))."'");
    }

    function same_links_count($min=30) {
        global $db;
        $count = 0;
        $localdomain = preg_quote(get_server_name(), '/');
        preg_match_all('/([\(\[:\.\s]|^)(https*:\/\/[^ \t\n\r\]\(\)\&]{5,70}[^ \t\n\r\]\(\)]*[^ .\t,\n\r\(\)\"\'\]\?])/i', $this->content, $matches);
        foreach ($matches[2] as $match) {
            $link=clean_input_url($match);
            $components = parse_url($link);
            if (! preg_match("/.*$localdomain$/", $components[host])) {
                $link = "//$components[host]$components[path]";
                $link=preg_replace('/(_%)/', "\$1", $link);
                $link=$db->escape($link);
                $count = max($count, (int) $db->get_var("select count(*) from posts where post_user_id = $this->author and post_date > date_sub(now(), interval $min minute) and post_content like '%$link%'"));
            }
        }
        return $count;
    }
    function update_conversation() {
        global $db, $globals;

        $db->query("delete from conversations where conversation_type='post' and conversation_from=$this->id");
        $references = array();
        if (preg_match_all('/(^|\s)@([\S\.\-]+[\w])/u', $this->content, $matches)) {
            foreach ($matches[2] as $reference) {
                if (!$this->date) $this->date = time();
                $user = $db->escape(preg_replace('/,\d+$/', '', $reference));
                $to = $db->get_var("select user_id from users where user_login = '$user'");
                $id = intval(preg_replace('/[^\s]+,(\d+)$/', '$1', $reference));
                if (! $id > 0) {
                    $id = (int) $db->get_var("select post_id from posts where post_user_id = $to and post_date < FROM_UNIXTIME($this->date) order by post_date desc limit 1");
                }
                $db->query("insert into conversations (conversation_user_to, conversation_type, conversation_time, conversation_from, conversation_to) values ($to, 'post', from_unixtime($this->date), $this->id, $id)");
                $references[$db->escape($user)] += 1;
            }
        }
    }

    function is_answer() {
        global $db;
        /* Regexpresion by @gallir */
        if (preg_match('/^\s*@([^\s<>;:,\?\)]+(?:,\d+){0,1})/', $this->content, $array)) {
            $id = explode(',', $array[1]);
            if (isset($id[1]) && $id[1] > 0) {
                return (int)$id[1];
            }
        }
    }

    function insert_answer(){
    global $db;

    if ($this->id > 0 && $this->answer_from > 0)
    $db->query("INSERT INTO answers (answer_post_id, answer_from) VALUES ($this->id, ".intval($this->answer_from).")");

    }

    /* Delete answer if the user edits the post */
    function delete_answer(){
    global $db;

    $db->query("DELETE FROM answers WHERE answer_post_id=".intval($this->id));

    }

    function re_answer(){
    global $db;

    /*if ($db->get_var("SELECT post_is_answer FROM posts where post_id=$this->answer_from")) return true;

    return false;*/

    $re_answer = $db->get_var("SELECT answer_from FROM answers where answer_post_id=$this->answer_from");

    if ($re_answer > 0) {
         $this->answer_from = $re_answer;
    }

    return $this->answer_from;

}

}
