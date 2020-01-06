<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

class User {
    const SQL = "user_id as id, user_login as username, user_sex, user_login_register as username_register, user_level as level, UNIX_TIMESTAMP(user_date) as date, user_ip as ip, UNIX_TIMESTAMP(user_modification) as modification, user_pass as pass, user_prev_carisma as previous_carisma, user_email as email, user_email_register as email_register, user_names as names, user_lang as lang, user_karma as karma, user_estado as estado, user_avatar as avatar, user_public_info as public_info, user_url as url, user_thumb as thumb, user_birth as birth";

    function User($id=0) {
        if ($id>0) {
        $this->id = $id;
        $this->read();
        }
    }

    function disabled() {
        return $this->level == 'disabled' ;
    }

    function disable() {
        global $db;

        require_once(mnminclude.'avatars.php');
        require_once(mnminclude.'geo.php');
        avatars_db_remove($this->id);
        avatars_remove_user_files($this->id);
        geo_delete('user', $this->id);

        // Delete relationships
        $db->query("DELETE FROM friends WHERE friend_type='manual' and (friend_from = $this->id or friend_to = $this->id)");
        // Delete preferences
        $db->query("DELETE FROM prefs WHERE pref_user_id = $this->id");
        // Delete posts
        //$db->query("delete from posts where post_user_id = $this->id");

        $this->username = '--'.$this->id.'--';
        $this->email = "$this->id@disabled";
        $this->url = '';
        $this->level = 'disabled';
        $this->sex = 'A ti que te importa';
        $this->names = 'disabled';
        $this->public_info = '';
        $this->adcode = '';
        $this->adchannel = '';
        $this->phone = '';
        $this->avatar = 0;
        $this->karma = 7;
        $this->estado = '';
        $this->thumb= 1;
        return $this->store();
    }

    function store($full_save = true) {
        global $db, $globals;

        if(!$this->date)
            $this->date=$globals['now'];

        $user_login = $db->escape($this->username);
        $user_level = $this->level;
        $user_comment_pref = $this->comment_pref;
        $user_karma = $this->karma;
        $user_avatar = $this->avatar;
        $user_date = $this->date;
        $user_ip = $this->ip;
        $user_pass = $db->escape($this->pass);
        $user_lang = $this->lang;
        $user_email = $db->escape($this->email);
        $user_names = $db->escape($this->names);
        $user_estado = $db->escape($this->estado);
        $user_sex = $db->escape($this->sex);
        $user_public_info = $db->escape(htmlentities($this->public_info));
        $user_url = $db->escape(htmlspecialchars($this->url));
        $birth = $db->escape($this->birth);
        $user_thumb = $db->escape($this->thumb);
        $user_prev_carisma = $this->previous_carisma;

        if(!$this->id) {
            $db->query("INSERT INTO users (user_login, user_level, user_karma, user_date, user_ip, user_pass, user_lang, user_email, user_names, user_public_info, user_url, user_phone, user_thumb) VALUES ('$user_login', '$user_level', $user_karma, FROM_UNIXTIME($user_date), '$user_ip', '$user_pass', $user_lang, '$user_email', '$user_names',  '$user_url', '$user_phone', $user_thumb");
            $this->id = $db->insert_id;
        } else {
            if ($full_save) $modification = ', user_modification = now() ' ;
            $db->query("UPDATE users set user_login='$user_login', user_level='$user_level', user_sex='$user_sex', user_karma=$user_karma, user_estado='$user_estado', user_avatar=$user_avatar, user_date=FROM_UNIXTIME($user_date), user_ip='$user_ip', user_pass='$user_pass', user_lang=$user_lang, user_birth='$birth' , user_email='$user_email', user_names='$user_names', user_public_info='$user_public_info', user_url='$user_url',user_prev_carisma=$user_prev_carisma, user_thumb=$user_thumb $modification  WHERE user_id=$this->id");
        }
    }

    function read() {
        global $db;

        if (isset($this->id))
        $id = $this->id;

        if (isset($id))
            $where = "user_id = $id";
        elseif (!empty($this->username))
            $where = "user_login='".$db->escape(mb_substr($this->username,0,64))."'";
        elseif (!empty($this->email))
            $where = "user_email='".$db->escape(mb_substr($this->email,0,64))."' and user_level != 'disabled'";

        if(!empty($where) && ($result = $db->get_row("SELECT ".User::SQL." FROM users WHERE $where LIMIT 1"))) {
            foreach(get_object_vars($result) as $var => $value) $this->$var = $value;

            if ($this->level == 'admin' || $this->level == 'god')
                $this->admin = true;
            else
                $this->admin = false;

            if ($this->admin || $this->level == 'devel')
                $this->devel = true;
            else
                $this->devel = false;

            $this->read = true;
            return true;
        }

        $this->read = false;
        return false;
    }



        function all_stats() {
               global $db, $globals;

        include_once mnminclude.'annotation.php';

        if(!$this->read) $this->read();

        $do_cache = ($this->date < $globals['now'] - 86400); // Don't cache for new users
        $stats = new Annotation("user_stats-$this->id");

        if ($do_cache && $stats->read()
            && ($stats->time > $globals['now'] - 7200
                || $stats->time > $this->get_last_date())
            ) {
                $obj = unserialize($stats->text);
        } else {
                $obj = new stdClass;
                $obj->total_votes = (int) $db->get_var("SELECT count(*) FROM votes WHERE vote_type='links' and vote_user_id = $this->id");
        $obj->total_links = (int) $db->get_var("SELECT count(*) FROM links WHERE link_author = $this->id and link_sent = 1");
        $obj->published_links = (int) $db->get_var("SELECT count(*) FROM links WHERE link_author = $this->id AND link_status = 'published'");
        $obj->total_comments = (int) $db->get_var("SELECT count(*) FROM comments WHERE comment_type != 'admin' AND comment_user_id = $this->id ");
        $obj->total_posts = (int) $db->get_var("SELECT count(*) FROM posts WHERE post_user_id = $this->id");
        $obj->cortos_totales = (int) $db->get_var("SELECT count(*)FROM cortos WHERE por = $this->id AND activado = 1");
        $obj->encuestas_totales = (int) $db->get_var("SELECT count(*)FROM encuestas WHERE encuesta_user_id= $this->id ");
            if ($do_cache) {
                $stats->text = serialize($obj);
                $stats->store();
            }
        }

           foreach(get_object_vars($obj) as $var => $value) $this->$var = $value;

        }

    function ranking() {
        global $db;

        if(!$this->read)
            $this->read();
        return (int) $db->get_var("SELECT SQL_CACHE count(*) FROM users WHERE user_karma > $this->karma") + 1;
    }

        static function get_valid_username($name) {
        $name = strip_tags($name);
        $name = preg_replace('/&.+?;/', '', $name); // kill entities
        $name = preg_replace('/[\s\'\"]/', '_', $name);
        if (preg_match('/^\d/', $name)) $name = 'u_' . $name; // Don't let start with a number
        return substr($name, 0, 24);
         }
    function blogs() {
        global $db;

        return $db->get_var("select  count(distinct link_blog) from links where link_author=$this->id");
    }

    function give_api_key() {
        global $site_key;

        return substr(md5($this->user.$this->date.$this->pass.$site_key), 0, 10);
    }

    function get_api_key() {
        global $db;

        return $db->get_var("SELECT api from api_msg where uid=$this->id ");
    }

    function get_latlng() {
        require_once(mnminclude.'geo.php');

        return geo_latlng('user', $this->id);
    }

    // obtiene la última fecha en la que el usuario realizó alguna acción, y la devuelve en formato DD/MM/YYYY HH:MM:SS
    // función por KayDarks <kepazaman@gmail.com>
    function get_last_date() {
            global $db;
            $lastDate = $db->get_var("SELECT UNIX_TIMESTAMP(max(fecha)) as fecha FROM (
                                        SELECT max(post_date) as fecha FROM posts WHERE post_user_id = $this->id
                                        UNION
                                        SELECT max(vote_date) as fecha FROM votes WHERE vote_user_id = $this->id
                                        UNION
                                        SELECT max(comment_date) as fecha FROM comments WHERE comment_user_id = $this->id
                                        UNION
                                        SELECT max(encuesta_start) as fecha FROM encuestas WHERE encuesta_user_id = $this->id
                                    ) ultimo_movimiento");
            return $lastDate;
    }
}

// Following functions are related to users but not done as a class so can be easily used with User and UserAuth

define('FRIEND_YES', '<img src="'.get_cover_pixel().'" title="'._('amigo').'" class="icon heart-on icono-amigo"/>');
define('FRIEND_NO', '<img src="'.get_cover_pixel().'" title="'._('agregar a la lista de amigos').'" class="icon heart-off icono-amigo"/>');
define('FRIEND_IGNORE', '<img src="'.get_cover_pixel().'" title="'._('ignorado').'" class="icon heart-black icono-amigo"/>');

function friend_exists($from, $to) {
    global $db;

    if ($from == $to)
            return 0;

    return round($db->get_var("SELECT SQL_NO_CACHE friend_value FROM friends WHERE friend_type='manual' and friend_from = $from and friend_to = $to"));
}

function friend_insert($from, $to, $value = 1) {
    global $db;

    if ($from == $to)
        return 0;
    if (intval($db->get_var("SELECT SQL_NO_CACHE count(*) from users where user_id in ($from, $to)")) != 2)
        return false;

    return $db->query("REPLACE INTO friends (friend_type, friend_from, friend_to, friend_value) VALUES ('manual', $from, $to, $value)");
}

function friend_delete($from, $to) {
    global $db;

    return $db->query("DELETE FROM friends WHERE friend_type='manual' and friend_from = $from and friend_to = $to");
}

function friend_add_delete($from, $to) {
    if ($from == $to)
        return '';

    switch (friend_exists($from, $to)) {
        case 0:
        friend_insert($from, $to);
        return FRIEND_YES;
        case 1:
        friend_insert($from, $to, -1);
        return FRIEND_IGNORE;
        case -1:
        friend_delete($from, $to);
        return FRIEND_NO;
    }
}


function friend_teaser($from, $to) {
    if ($from == $to)
        return '';

    switch (friend_exists($from, $to)) {
        case 0:
        return FRIEND_NO;
        case 1:
        return FRIEND_YES;
        case -1:
        return FRIEND_IGNORE;
    }

}