<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// David Martín :: Suki_ :: <david at sukiweb dot net>.
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>

if (! defined('mnmpath')) {
    include_once('../config.php');
    header('Content-Type: text/html; charset=utf-8');
}
//include_once(mnminclude.'user.php');
//include_once(mnminclude.'post.php');

if (empty($_GET['id']) || (!$current_user->admin)) die;
$id = intval($_GET['id']);
require_once(mnminclude.'ban.php');
$ban=new Ban();
$ban->ban_id=$id;
if (! $ban->read())  die;
echo '<strong>' . _($ban->ban_type) . ':</strong>&nbsp;' . $ban->ban_text . '<br/>';
if ($ban->ban_comment) echo '<strong>' . _('Comentario') . ':</strong>&nbsp;' . $ban->ban_comment . '<br/>';
if ($ban->ban_expire)  echo '<strong>' . _('Expira') . ':</strong>&nbsp;' . $ban->ban_expire . '<br/>';