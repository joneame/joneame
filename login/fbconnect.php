<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

$base = dirname(dirname($_SERVER["SCRIPT_FILENAME"])); // Get parent dir that works with symbolic links
include("$base/config.php");

include('base.php');
include_once(mnminclude.'fbconnect/facebook.php');


class FBConnect extends OAuthBase {

    function __construct() {
        global $globals;
        $this->service = 'facebook';

        $server = 'www.facebook.com';

        // Store de FB URL for login
        $location_ok = urlencode('http://'.  get_server_name() . $globals['base_url'] . 'login/fbconnect.php?op=ok'.'&t='.time());
        $location_cancel = urlencode('http://'.  get_server_name() . $globals['base_url'] . 'login/fbconnect.php?op=cancel'.'&t='.time());
        $this->authorize_url = "http://$server/login.php?api_key=".$globals['facebook_key'].'&extern=1&fbconnect=1&return_session=1&v=1.0&next='.$location_ok.'&cancel_url='.$location_ok;
        parent::__construct();
    }

    function authRequest() {
        global $globals;

        // Print html needed for FB Connect API
        echo "<html><head>\n";
        echo '<script src="http://static.new.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script>';
        echo '<script>';
        echo 'FB.init("'.$globals['facebook_key'].'", "'.$globals['base_url'].'libs/fbconnect/xd_receiver.php",{"reloadIfSessionStateChanged":true});';
        echo 'self.location = "'.$this->authorize_url.'";';
        echo '</script>';
        echo '</head><body></body></html>';
        exit;
    }

    function authorize() {
        global $globals, $db;

        $fb = new Facebook($globals['facebook_key'], $globals['facebook_secret']);
        $fb->require_login();
        $fb_user = $fb->get_loggedin_user();

        $user_details = $fb->api_client->users_getInfo($fb_user, array('uid', 'name', 'profile_url', 'pic_square'));

        if ($_GET['op'] != 'ok' || ! $fb_user || !is_array($user_details) || !is_array($user_details[0])) {
            $this->user_return();
        }


        $this->token = $user_details[0]['uid'];
        $this->secret = $user_details[0]['uid'];
        $this->uid = $user_details[0]['uid'];
        $this->username = preg_replace('/.+?\/.*?([\w\.\-_]+)$/', '$1', $user_details[0]['profile_url']);

        // Most Facebook users don't have a name, only profile number
        if (!$this->username || preg_match('/^\d+$/', $this->username)) {
            // Create a name like a uri used in stories
            if (strlen($user_details[0]['name']) > 2) {
                $this->username = User::get_valid_username($user_details[0]['name']);
            } else {
                $this->username = 'fb'.$this->username;
            }
        }

                if (!$this->user_exists()) {
            $this->store_user();
        }

        $this->store_auth();

        $this->user_login();
    }
}


$auth = new FBConnect();

switch ($_GET['op']) {
    case 'ok':
    case 'cancel':
        $auth->authorize();
        break;
    case '':
        $auth->authRequest();
        break;
    default:
        die;
}

?>
