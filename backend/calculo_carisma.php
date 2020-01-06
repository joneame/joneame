<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include_once('../config.php');
include(mnminclude.'annotation.php');

header('Content-Type: text/html; charset=utf-8');

if (empty($current_user->user_login)) {
    echo _('usuario no identificado');
    die;
}

$id = intval($_GET['id']);

if (!empty($id) && $current_user->user_level == 'god') {
    $user = $id;
} else {
    $user = $current_user->user_id;
}

$annotation = new Annotation("karma-$user");
echo '<div style="text-align: left">';
if ($annotation->read()) {
    echo '<strong>' . _('última modificación') . ':</strong> ' . get_date_time($annotation->time);
    echo '<ul>';
    foreach (preg_split("\n", $annotation->text) as $line) {
        $line = trim($line);
        if($line) echo "<li>$line</li>\n";
    }
    echo '</ul>';
} else {
    print _('no hay registros para este usuario');
}
echo '</div>';