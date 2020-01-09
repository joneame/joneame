<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

class UserAuth {
    var $user_id  = 0;
    var $user_login = '';
    var $user_email = '';
    var $md5_pass = '';
    var $authenticated = FALSE;
    var $user_level='';
    var $user_karma=0;
    var $admin = false;
    var $devel = false;
    var $user_avatar=0;
    var $mnm_user = False;
    var $especial = false;


    function UserAuth() {
        global $db, $site_key, $globals;

        $this->now = $globals['now'];
        if(!empty($_COOKIE['joneame_2'])) {
            $this->mnm_user=explode(":", $_COOKIE['joneame_2']);
        }

        if($this->mnm_user && $this->mnm_user[0] && !empty($_COOKIE['joneame_key_2'])) {
            $userInfo=explode(":", base64_decode($_COOKIE['joneame_key_2']));
            if($this->mnm_user[0] === $userInfo[0]) {
                $cookietime = (int) $userInfo[3];
                $dbusername = $db->escape($this->mnm_user[0]);
                $user=$db->get_row("SELECT SQL_CACHE user_id, user_pass, user_level, user_thumb, UNIX_TIMESTAMP(user_validated_date) as user_date, user_karma, user_email, user_avatar, user_prev_carisma FROM users WHERE user_login = '$dbusername'");

                // We have two versions from now
                // The second is more strong agains brute force md5 attacks
                switch ($userInfo[2]) {
                    case '3':
                        if (($this->now - $cookietime) > 864000) $cookietime = 'expired'; // after 10 days expiration is forced
                         $key = md5($user->user_email.$site_key.$dbusername.$user->user_id.$cookietime);
                        break;
                    case '2':
                        $key = md5($user->user_email.$site_key.$dbusername.$user->user_id);
                        $cookietime = 0;
                        break;
                    default:
                        $key = md5($site_key.$dbusername.$user->user_id);
                        $cookietime = 0;
                }

                if ( !$user || !$user->user_id > 0 || $key !== $userInfo[1] ||
                    $user->user_level == 'disabled' ||
                    empty($user->user_date)) {
                        $this->Logout();
                        return;
                }

                $this->user_id = $user->user_id;
                $this->user_login  = $userInfo[0];
                $this->md5_pass = $user->user_pass;
                $this->user_level = $user->user_level;
                if ($this->user_level == 'admin' || $this->user_level == 'god') $this->admin = true;
        if ($this->user_level == 'admin' || $this->user_level == 'god' || $this->user_level == 'devel') $this->devel = true;
        if ($this->user_level == 'special' || $this->user_level == 'devel') $this->especial = true;
                $this->user_karma = $user->user_karma;
                $this->user_email = $user->user_email;
                $this->user_avatar = $user->user_avatar;
                $this->user_prev_carisma = $user->user_prev_carisma;
                $this->user_date = $user->user_date;
                if ($this->user_id == 0) $this->thumb= 1;
                else $this->thumb = $user->user_thumb;
        $this->unread_messages = $this->unread_messages();
                $this->authenticated = TRUE;

                if ($userInfo[2] != '3') { // Update the cookie to version 3
                    $this->SetIDCookie(2, true);
                } elseif ($this->now - $cookietime > 3600) { // Update the time each hour
                    $this->SetIDCookie(2, $userInfo[4] > 0 ? true : false);
                }
            }
        }

    // Mysql variables to use en join queries
        $db->query("set @user_id = $this->user_id, @ip_int = ".$globals['user_ip_int'].
            ", @ip_int = ".$globals['user_ip_int'].
            ", @enabled_votes = date_sub(now(), interval ". intval($globals['time_enabled_votes']/3600). " hour)");
    }

    function unread_messages(){
        global $db;

        return intval($db->get_var("SELECT count(*) FROM mezuak WHERE nori = '".$this->user_id."' AND irakurrita = '0' AND posta='recipient' "));
    }

    function SetIDCookie($what, $remember) {
        global $site_key, $globals;
        switch ($what) {
            case 0:    // Borra cookie, logout
                setcookie ('joneame_key_2', '', $this->now - 3600, $globals['base_url'], get_server_name()); // Expiramos el cookie
                $this->SetUserCookie(false);
                break;
            case 1: // Usuario logeado, actualiza el cookie
                $this->AddClone();
                $this->SetUserCookie(true);
            case 2: // Only update the key
                // Atencion, cambiar aquí cuando se cambie el password de base de datos a MD5
                if($remember) $time = $this->now + 3600000; // Valid for 1000 hours
                else $time = 0;
                $strCookie=base64_encode(
                        $this->user_login.':'
                        .md5($this->user_email.$site_key.$this->user_login.$this->user_id.$this->now).':'
                        .'3'.':' // Version number
                        .$this->now.':'
                        .$time);
                setcookie('joneame_key_2', $strCookie, $time, $globals['base_url'], get_server_name(), $globals['https'], true);
                break;
        }
    }

    function Authenticate($username, $hash, $remember=0/* Just this session */) {
            global $db;

        $dbusername=$db->escape($username);
        if (preg_match('/.+@.+\..+/', $username)) {
            // It's an email address, get
            $user=$db->get_row("SELECT user_id, user_login, user_pass md5_pass, user_level, UNIX_TIMESTAMP(user_validated_date) as user_date, user_karma, user_email FROM users WHERE user_email = '$dbusername'");
        } else {
            $user=$db->get_row("SELECT user_id, user_login, user_pass md5_pass, user_level, UNIX_TIMESTAMP(user_validated_date) as user_date, user_karma, user_email FROM users WHERE user_login = '$dbusername'");
        }
        if ($user->user_level == 'disabled' || ! $user->user_date) return false;
        if ($user->user_id > 0 && $user->md5_pass == $hash) {
            foreach(get_object_vars($user) as $var => $value) $this->$var = $value;
            $this->authenticated = true;
            $this->SetIDCookie(1, $remember);
            return true;
        }
        return false;
    }

    function Logout($url='./') {
        $this->user_id = 0;
        $this->user_login = "";
        $this->authenticated = FALSE;
        $this->SetIDCookie (0, false);

        //header("Pragma: no-cache");
        header("Cache-Control: no-cache, must-revalidate");
        header("Location: $url");
        header("Expires: " . gmdate("r", $this->now - 3600));
        header('ETag: "logingout' . $this->now . '"');
        die;
    }

    function Date() {
        return (int) $this->user_date;
    }

    function SetUserCookie($do_login) {
        global $globals;
        if ($do_login) {
            setcookie('joneame_2', $this->user_login.':'.$this->mnm_user[1], $this->now + 3600000, $globals['base_url'], get_server_name(), $globals['https'], true);
        } else {
            setcookie('joneame_2', '_:'.$this->mnm_user[1], $this->now + 360000, $globals['base_url'], get_server_name(), $globals['https'], true);
        }
    }

    function AddClone() {
            if (!empty($this->mnm_user[1])) {
                $ids = explode("x", $this->mnm_user[1]);
                while(count($ids) > 4) {
                    array_shift($ids);
                }
            } else {
                $ids = array();
            }
            array_push($ids, $this->user_id);
            $this->mnm_user[1] = implode('x', $ids);
    }

    function GetClones() {
        $clones = array();
        foreach (explode('x', $this->mnm_user[1]) as $id) {
            $id = intval($id);
            if ($id > 0 && $id != $this->user_id) {
                array_push($clones, $id);
            }
        }
        return $clones;

    }

    function GetOAuthIds($service = false) {
        global $db;
        if (! $this->user_id) return false;
        if (! $service) {
            $sql = "select service, uid from auths where user_id = $this->user_id";
            $res = $db->get_results($sql);
        } else {
            $sql = "select uid from auths where user_id = $this->user_id and service = '$service'";
            $res = $db->get_var($sql);
        }
        return $res;
    }

}

$current_user = new UserAuth();