<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jon Arano <arano.jon@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include_once('../config.php');
include_once(mnminclude.'encuestas.php');

$id = intval($_POST['poll_id']);
$encuesta = new Encuesta;
$encuesta->id = $id;
$encuesta->read();

if (!$encuesta->read)
	die(_('la encuesta no existe'));

if ($encuesta->userVoted() || $encuesta->finished)
	die(_('ya has votado la encuesta, o ha terminado el periodo de voto'));

if ($current_user->user_id == 0) 
	die(_('debes estar registrado para votar'));
 
insert_vote();
 
function insert_vote () {
	global $encuesta, $db, $current_user, $globals;

	$ids = explode(',', $_POST['opciones']);

	foreach ($ids as $votado) {

		if ($votado > 0){
			$encuesta->doVote(intval($votado));
			$votado_h  = 1;
		} 
	}

	if (!$votado_h) die(_('no has elegido ninguna opción<br/>'));

	$db->query("UPDATE encuestas SET encuesta_total_votes= encuesta_total_votes + 1 WHERE encuesta_id = ".$encuesta->id);

	$encuesta->read();

	$encuesta->print_stats();
	

}