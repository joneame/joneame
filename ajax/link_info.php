<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include '../config.php';
include mnminclude.'link.php';

$link = new Link;
$link->id = intval($_REQUEST['id']);

$new_link = $db->get_row("SELECT link_id, link_votes, link_aleatorios_positivos, link_anonymous, link_negatives, link_karma FROM links WHERE link_id=".$link->id);

$link->votes = $new_link->link_votes;
$link->anonymous = $new_link->link_anonymous;
$link->negatives = $new_link->link_negatives;
$link->karma = $new_link->link_karma;
$link->aleatorios_positivos = $new_link->link_aleatorios_positivos;

echo $link->json_votes_info();
