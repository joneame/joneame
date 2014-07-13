<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include_once('../config.php');

$id = intval($_GET['id']);
if (! $id > 0) die;

// Example to change the image for a give domain
//if (preg_match('/domain.com/', $_SERVER['HTTP_REFERER'])) {
//}

$votes_comments = $db->get_row("select link_votes, link_anonymous, link_comments from links where link_id=$id");
$im = imagecreate(200, 16);

$bg = imagecolorallocatealpha($im, 66, 158, 233, 207);
$textcolor = imagecolorallocate($im, 66, 158, 233);

imagestring($im, 3, 2, 1, ($votes_comments->link_votes+$votes_comments->link_anonymous). ' ' . _('joneos') . ", $votes_comments->link_comments ". _('comentarios'), $textcolor);

header("Content-type: image/png");
header('Cache-Control: max-age=120, must-revalidate');
header('Expires: ' . date('r', time()+120));
imagepng($im);