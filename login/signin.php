<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

error_reporting(E_ALL);
ini_set("display_errors", 1);

$base = dirname(dirname($_SERVER["SCRIPT_FILENAME"])); // Get parent dir that works with symbolic links
include("$base/config.php");

$service = clean_input_string($_GET['service']);
$op = clean_input_string($_GET['op']);

switch ($service) {
    case 'twitter':
    default:
        require_once('twitter.php');
        $req = new TwitterOAuth();
        if ($op == 'init') {
            $req->authRequest();
        } else {
            $req->authorize();
        }
}