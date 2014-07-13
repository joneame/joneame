<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jon Arano <arano.jon@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include_once('../config.php');

if (!$current_user->admin) {
    echo _('usuario no autorizado');
    die;
}

$user_id = intval($_GET['id']);
if (! $user_id > 0) {
    echo _('usuario no identificado');
    die;
}


$clones = $db->get_results("select distinct user_login, clon_ip from clones, users where clon_from = $user_id and user_id = clon_to order by clon_date desc limit 20");
if (! $clones) {
    print _('no hay ningún clon'); //no hay ningun clon
    die;
}
foreach ($clones as  $clon) {

        if (preg_match('/COOK:/i', $clon->clon_ip)){
            echo '<ul>';
            echo '<li><a href="'.get_user_uri($clon->user_login).'">'.$clon->user_login."</a></li>\n";
            echo '</ul>';
        }

    }

?>
