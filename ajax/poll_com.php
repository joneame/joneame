<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include_once('../config.php');
include(mnminclude.'encuestas.php');

// Sanear un poco. Porsiaca...
if ($current_user->user_level == 'disabled')
	die(_('estás baneado'));

if (!is_numeric($_POST['poll_id']))
	die(_('número de encuesta no válido'));

if (!is_numeric($_POST['user_id']))
	die(_('usuario incorrecto'));

$id = intval($_POST['poll_id']);
$encuesta = new Encuesta;
$encuesta->id = $id;
$encuesta->read();

if (!$encuesta->read)
	die(_('la encuesta no existe'));



// Vamos al lío 
echo insert_comment();

function insert_comment () {
	global $encuesta, $db, $current_user, $globals;

	$error = '';


	require_once(mnminclude.'ban.php');
	if(check_ban_proxy()) return _('dirección IP no permitida');

	// Check if is a POST of a comment
	if(($encuesta->read) && intval($_POST['poll_id']) == $encuesta->id && $current_user->authenticated && 
			intval($_POST['user_id']) == $current_user->user_id &&
			mb_strlen(trim($_POST['poll_content'])) > 2 ) {

		require_once(mnminclude.'opinion.php');
		$comment = new Opinion;
		$comment->encuesta_id=$encuesta->id;
		
		$comment->contenido=clean_text($_POST['poll_content'], 0, false, 10000);
		$comment->store();
				

	} else {
		$error .= ' ' . ('texto muy breve, carisma bajo o usuario incorrecto');
	}

	if ($error)
		return 'KO:'.$error;

	
		$commentblock = '<ol class="comments-list">';
		
		$comment->read();
		$commentblock .= $comment->print_opinion();
		$commentblock .= "\n";
		
		$commentblock .= "</ol>\n";
	

	return 'OK:'.$commentblock;

}