<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Aritz <aritz@itxaropena.org>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include_once(mnminclude.'xtea.class.php');
include_once(mnminclude.'mezuak.class.php');

/*
 * Envia mensajes privados globales
 * 
 */
function inprimatu_orokorra() {
	global $db, $rows, $user, $offset, $page_size, $globals, $current_user;
	if ($_POST) {
		$nori = $db->get_results("SELECT * FROM users WHERE user_level != 'disabled'");
		
		foreach ($nori as $zeinei) {
			if (!empty($_POST['titulo']) && !empty($_POST['mensaje'])) {
				$msgprv = new Mezu;
				$xtea = new XTEA($msgprv->getKey($zeinei->user_id));
				$mensajito = $xtea->encrypt(normalize_smileys($_POST['mensaje']));
				$titulo = $xtea->encrypt("(GLOBAL) ".$_POST['titulo']);
				$data = $globals['now'];
				// Ojo: No guardamos copia al que envia el mensaje, solo se la guardamos al que lo recibe.
				$db->query("INSERT INTO mezuak VALUES (NULL, '".$db->escape($current_user->user_id)."', '".$db->escape($zeinei->user_id)."', 'recipient', '0', '".$db->escape($data)."', '".$db->escape($mensajito)."', '".$db->escape($titulo)."')");
				notify_user($current_user->user_id, $zeinei->user_id, $mensajito, $titulo);
			}
			
		} 

		echo '<div class="genericform">';
		echo '<fieldset><legend><span class="sign">'._('¡mensajes enviados!').'</span></legend>';
		echo 'El mensaje global ha sido enviado correctamente.';
		echo '</fieldset>';
		echo '</div>';
	} else {
		
		echo '<h4>enviar un mensaje global</h4><div class="fondo-caja"><fieldset>';
		echo '<form action='.get_mensajes_uri($current_user->user_login).'?orokorra=1" method="post" id="bidali" name="bidali">';
		echo '<p><label for="titulo">'._('titulo/tema').':</label><br />';
		echo '<input type="text" name="titulo" id="titulo" class="form-full" maxlenght=120 /></p>';
		echo '<p><label for="mensaje">'._('mensaje').':</label><br />';
		echo '<textarea name="mensaje" id="mensaje" class="form-full" rows=8 /></textarea></p>';
		echo  print_simpleformat_buttons('mensaje');
		echo '<input type="hidden" name="phase" value="1" />';
		echo '<input type="hidden" name="id" value="c_1" />';
		echo '<p><input class="button" type="submit" value="'._('&iexcl;enviar!').'" ';

		echo '/>&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span></p>';
		echo '</form>';
		echo '</fieldset>';
		echo '</div>';
	}
}

/*
 * Cambia las opciones del usuario
 * 
 */
function inprimatu_aldaketak() {
	global $current_user, $db, $user;
	if ($_POST) {
		
		if (!empty($_POST['ada']) && ($_POST['ada'] == 'todos' || $_POST['ada'] == 'nadie' || $_POST['ada'] == 'amigos')) {

			$id = $db->get_var("SELECT id FROM mezuak_nork WHERE idusr =$current_user->user_id");

			if ($id > 0) 
			$db->query("UPDATE mezuak_nork SET nori = '".$db->escape($_POST['ada'])."' WHERE idusr=$current_user->user_id");

			else $db->query("INSERT INTO mezuak_nork (id, idusr, nori) VALUES (NULL, '".$current_user->user_id."', '".$db->escape($_POST['ada'])."')");
		
		}

		$existe = $db->get_var("SELECT pref_user_id FROM prefs WHERE pref_key='email_not' AND pref_user_id=$current_user->user_id");

		if ($_POST['mail'] == true){
			if (!$existe)
				$db->query("INSERT INTO prefs (pref_user_id, pref_key, pref_value) VALUES ($current_user->user_id, 'email_not', '1')");
			else    $db->query("UPDATE prefs SET pref_value=1  WHERE pref_user_id=$current_user->user_id AND pref_key='email_not'");
		} else {
			if (!$existe)
			$db->query("INSERT INTO prefs (pref_user_id, pref_key, pref_value) VALUES ($current_user->user_id, 'email_not', '0')");
			else    $db->query("UPDATE prefs SET pref_value=0  WHERE pref_user_id=$current_user->user_id AND pref_key='email_not'");
		}

		echo '<div class="genericform">';
		echo '<h4>'._('cambios realizados').'</h4><fieldset class="fondo-caja redondo">';
		echo 'Se han guardado tus cambios.';
		echo '</fieldset>';
	
		echo '</div>';
	} else {
		$zein = Array ("todos" => "Todos", "amigos" => "Amigos", "nadie" => "Nadie, ¡cojones ya!");
		
		echo '<div class="genericform">';
		echo '<h4>'._('configuración de mensajes privados').'</h4><fieldset class="fondo-caja redondo">';
		echo '<form action="'.get_mensajes_uri($user->username).'?settings=1" method="post" id="bidali" name="bidali">';
		echo '<p><label for="testua">'._('recibir mensajes de').':</label><br/><br/>';
		echo '<select name="ada" id="ada">';

		if ($a = $db->get_row("SELECT * FROM mezuak_nork WHERE idusr = '".$current_user->user_id."'")) {
			foreach ($zein as $jk => $balio) {
				if ($a->nori == $jk)
				echo ' <option value="'.$jk.'" selected="selected">'.$balio.'</option>';
				else
				echo ' <option value="'.$jk.'">'.$balio.'</option>';
			}
		} else {
			foreach ($zein as $jk => $balio) {
				if ('todos' == $jk)
				echo ' <option value="'.$jk.'" selected="selected">'.$balio.'</option>';
				else
				echo ' <option value="'.$jk.'">'.$balio.'</option>';
			}
		}

		echo '</select></p>';

		$existe = $db->get_var("SELECT pref_value FROM prefs WHERE pref_key='email_not' AND pref_user_id=$current_user->user_id");

		if ($existe > 0) $checked = 'checked="checked"';
		else $checked = '';

		echo '<p><br/><label for="testua">'._('recibir mensajes privados por email').':</label><br/><br/>';
		echo '<input type="checkbox"'. $checked .' name="mail" value="1" />';
		
		echo '<input type="hidden" name="phase" value="1" />';
		echo '<input type="hidden" name="id" value="c_1" />';
		echo '<br/><br/><p><input class="button" type="submit" value="'._('&iexcl;chachi!').'" />';
		echo '&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span></p>';
		echo '</form>';
		echo '</fieldset>';
		echo '</div>';
		
	}
}


/*
 * Envia mensajes privados a los usuarios
 * 
 */

function bidali_pribatua() {
	global $db, $rows, $user, $offset, $page_size, $globals, $current_user;

	if ($user->level == 'disabled')
		do_error('No puedes enviar un mensaje a '.$user->username.': está baneado', 403, false, false);

	if ($_POST && !$_GET['env_pst']) {
		if (!empty($_POST['mensaje'])) {
			if (empty($_POST['titulo'])) $titulo = "(sin t&iacute;tulo)"; else $titulo = $_POST['titulo'];
			$msgprv = new Mezu;
			$xtea = new XTEA($msgprv->getKey($user->id));
			$contenido_mensaje = normalize_smileys(clean_text(clean_lines($_POST['mensaje'])));
			$mezua = $xtea->encrypt($contenido_mensaje);
			$titulo2 = $xtea->encrypt($titulo);
			// Prozesatu mezua.
			// $data = date("d-m-Y H:m");
			$data = $globals['now'];
			// ATENCIÓN, LAS FECHAS SE METEN EN UNIXTIME
			// Sender (ojo: metemos el mensaje como si estuviese leido)
			$db->query("INSERT INTO mezuak VALUES (NULL, '".$current_user->user_id."', '".$user->id."', 'sender', '1', '".$data."', '".$mezua."', '".$titulo2."')"); 
			// Recipient
			$db->query("INSERT INTO mezuak VALUES (NULL, '".$current_user->user_id."', '".$user->id."', 'recipient', '0', '".$data."', '".$mezua."', '".$titulo2."')"); 

			notify_user($current_user->user_id, $user->id, $contenido_mensaje, $titulo);
			
			echo '<div class="genericform">';
			echo '<h4>'._('se ha enviado el mensaje con éxito').'</h4><fieldset class="fondo-caja">';
			echo '<ul class="barra redondo herramientas">';
			echo '<li><a href="'.get_mensajes_uri($user->username).'" class="icon message-send">'._('enviar otro mensaje a').' '.$user->username.'</a></li>';
			echo '<li><a href="'.get_mensajes_uri($current_user->user_login).'" class="icon message">'._('ir a mis mensajes').'</a></li>';
			echo '<li><a href="'.get_user_uri($user->username, '').'" class="icon friend">'._('ir al perfil de').' '.$user->username.'</a></li>';
			echo '</ul>';
			echo '<div style="padding: 30px; text-align: center; clear: both;">¡Mensaje enviado con éxito a <strong>'.$user->username.'</strong>!</div>';
			echo '</fieldset>';
			echo '</div>';
		
		}
	} else {
		if ($_POST['titu_bidali']){

			// Añadimos respuesta en el asunto si no la tiene
			if (substr($_POST['titu_bidali'], 0, 4) != 'Re: ') $respuesta = 'Re: ';

		$vals = 'value="'.$respuesta.addslashes($_POST['titu_bidali']).'"'; 

		} else $vals = 0;

		echo '<div class="genericform">';
		
		echo '<h4>enviar un mensaje privado a '.$user->username.'</h4><div class="fondo-caja"><fieldset>';

		echo '<ul class="barra redondo herramientas">';
		echo '<li><a href="'.get_user_uri($user->username).'" class="icon friend">'._('ir al perfil de').' '.$user->username.'</a></li>';
		echo '<li><a href="'.get_mensajes_uri($current_user->user_login).'" class="icon message">'._('ir a mis mensajes').'</a></li>';
		echo '</ul><br/><br/><br/>';

		echo '<form action="'.get_mensajes_uri($user->username).'?enviar=1" method="post" id="bidali" name="bidali">';
		echo '<p><label for="titulo">'._('titulo/tema').':</label><br />';
		echo '<input type="text" name="titulo" id="titulo" class="form-full" maxlenght=120 '.$vals.'/></p>';
		echo '<p><label for="mensaje">'._('mensaje').':</label><br />';
		echo '<textarea name="mensaje" id="mensaje" class="form-full" maxlenght=120 /></textarea></p>';
		echo  print_simpleformat_buttons('mensaje');
		echo '<input type="hidden" name="phase" value="1" />';
		echo '<input type="hidden" name="id" value="c_1" />';
		echo '<p><input class="button" type="submit" value="'._('&iexcl;enviar!').'" ';

		echo '/>&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span></p>';
		echo '</form>';
		echo '<br />';
		echo '<div class="redondo atencion">La posibilidad de usar la mensajería privada podría ser desactivada por la administración de Jonéame en un caso de abuso como podr&iacute;an ser mensajes ofensivos contra otros usuarios, pudiendo llegar a la cancelaci&oacute;n de la cuenta. Si est&aacute;s sufriendo este tipo de abusos, por favor, env&iacute;anos un correo electr&oacute;nico a <a href="mailto:admin@joneame.net">admin@joneame.net</a> exponiendo el caso detalladamente y as&iacute; podremos tomar las medidas oportunas.</div>';
		echo '</fieldset></div>';
		// Posicionamos el cursor según lo que nos interese para facilitar a los usuarios
		if ($vals) echo "<script type=\"text/javascript\">document.getElementById('mensaje').focus()</script>";
		else echo "<script type=\"text/javascript\">document.getElementById('titulo').focus()</script>";
		echo '</div>';
	}

}

/*
 * Imprime el listado de los mensajes
 * de las carpetas OUTBOX e INBOX
 */

function inprimatu_mezupribatuak($mota) {
	global $db, $rows, $user, $offset, $page_size, $globals, $current_user;
	$mezuak = new Mezu;
	$mezuak->id = $current_user->user_id;
	$mezuak->mezuak_jaso($mota, $offset, $page_size);
	
	if (!$mezuak->error) { // No hay errores.
		$rows = $mezuak->rows;
		foreach ($mezuak->datuak as $mensaje) {
			// Leemos los datos.
			$datos_mensaje = $mensaje[0];
			$avatar = $mensaje[1];
			$usuario = $mensaje[2];
			$id_usr = $mensaje[3];
			$id_mezu = $mensaje[5];
			$md5a =  md5($current_user->user_id.$current_user->user_date);
			
			//convertir a fecha/Hora
			$datos_mensaje->data = date("d/m/Y G:i ", $datos_mensaje->data);

			if ($mota == 'inbox') {
				if ($datos_mensaje->irakurrita == '0') $beltz = '<strong>('.$datos_mensaje->data. 'Europe/Madrid )</strong>';
				else $beltz = '('.$datos_mensaje->data.'  Europe/Madrid)';

				// Y finalmente los imprimimos.
				if (pribatuetako_sarbidea($current_user->user_id, $datos_mensaje->nork) && !$datos_mensaje->mensaje_global)
					$asunt='	<a href="'.get_mensajes_uri($usuario).'"><img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$id_usr.');" onmouseout="tooltip.clear(event);" class="avatar" src="'.$avatar.'" alt="'.$usuario.'" /></a>';
				else if ($datos_mensaje->mensaje_global)
					$asunt='	<a href="'.get_user_uri(get_server_name()).'"><img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', 73);" onmouseout="tooltip.clear(event);" class="avatar" src="'.get_admin_avatar('20').'" alt="admin" /></a>';
				else 
					$asunt='	<img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$datos_mensaje->nork.');" onmouseout="tooltip.clear(event);" class="avatar" src="'.$avatar.'" alt="'.$usuario.'" />';
			} else {
				if ($datos_mensaje->nori == $current_user->user_id) continue; // <<-- No, no estoy loco: Mensajes globales. 
				$beltz = '('.$datos_mensaje->data.')';
				// Lo mismo cambiando unas cositas
				if (pribatuetako_sarbidea($current_user->user_id, $datos_mensaje->nori))
					$asunt='	<a href="'.get_mensajes_uri($usuario).'"><img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$id_usr.');" onmouseout="tooltip.clear(event);" class="avatar" src="'.$avatar.'" alt="'.$usuario.'" /></a>';
				else
					$asunt='	<img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$datos_mensaje->nori.');" onmouseout="tooltip.clear(event);" class="avatar" src="'.$avatar.'" alt="'.$usuario.'" />';
			}
	
			$ezabatu = '<a href="javascript:void(0)" title="'._('eliminar mensaje').'" onclick="ezabatu_mezua(\''.$datos_mensaje->id.'\', \''.$current_user->user_id.'\', \''.$md5a.'\', \''.$mota.'\')"><img class="icon delete" src="'.get_cover_pixel().'" alt="'._('eliminar mensaje').'" title="'._('eliminar mensaje').'"/></a>';

			echo '<span id="mezuak'.$datos_mensaje->id.'"><div class="mezuko_normala" href="javascript:void(0)"  onclick="irakurri_mezua(\''.$datos_mensaje->id.'\', \''.$current_user->user_id.'\', \''.$md5a.'\', \''.$mota.'\', \'0\')" onmouseover="className=\'mezuko_aldatuta\';"  onmouseout="className=\'mezuko_normala\';">'.$asunt.'  '.$ezabatu.' '.$beltz.' '.str_replace("(GLOBAL)", '<img src="'.get_cover_pixel().'" alt="'._('mensaje global').'" title="'._('mensaje global').'" class="icon global"/>', $datos_mensaje->titulua).'</div></span>';
		}

	} else echo _('¡No has recibido ningún mensaje privado!').'<br/>'/*._('Si quieres enviar un mensaje privado, ve al perfil del usuario y haz clic en "privados".')*/;
  
}
 

/*
 * Activa las imagenes segun el valor de las variables
 * 
 */
function irudiak_atera($settings, $orokorra, $outbox) {
	global $globals, $current_user,$user;
	// una chapuza en toda regla.
	if (empty($settings) && empty($orokorra) && empty($outbox)) $ak0 = True; else $ak0 = False;
	if ($settings && !$orokorra && !$outbox) $ak1 = True; else $ak1 = False;
	if (empty($settings) && !empty($orokorra) && empty($outbox)) $ak2 = True; else $ak2 = False;
	if (empty($settings) && empty($orokorra) && !empty($outbox)) $ak3 = True; else $ak3 = False;

	$entrada_activo = '<li><a href="'.get_mensajes_uri($user->username).'" class="icon refresh">recargar</a></li>';
	$entrada_inactivo = '<li><a href="'.get_mensajes_uri($user->username).'" class="icon message">bandeja de entrada</a></li>';

	$entrada = $ak0 ? $entrada_activo : $entrada_inactivo;

	$opciones_activo = '<li><a href="'.get_mensajes_uri($user->username).'?settings=1" class="icon wrench">configuración de los mensajes</a></li>';
	$opciones_inactivo = $opciones_activo;

	$opciones = $ak1 ? $opciones_activo : $opciones_inactivo;

	$global_activo = '<li><a href="'.get_mensajes_uri($user->username).'?orokorra=1" class="icon global">enviar un mensaje global</a></li>';
	$global_inactivo = $global_activo;

	if ($current_user->admin) 
	$globalmsg = $ak2 ? $global_activo : $global_inactivo;
	else $globalmsg = '';

	$outbox_activo = '<li><a href="'.get_mensajes_uri($user->username).'?enviados=1" class="icon message-send">bandeja de salida</a></li>';
	$outbox_inactivo = $outbox_activo;

	$outbox2 = $ak3 ? $outbox_activo : $outbox_inactivo;

	return '<ul class="barra redondo herramientas">'.$entrada.' '.$outbox2.' '.$opciones.' '.$globalmsg.'</ul>';
}


/*
 * Funcion general de mensajes privados.
 * Esta funcion es llamada desde ../postbox.php
 */
function do_privados ($tipo) {
	global $db, $rows, $user, $offset, $page_size, $globals, $current_user;

	if ($globals['bot']) return;

	/* Temporal: Asumimos por defecto que el usuario desea que se le notifique por mail */
	if ($db->get_var("SELECT count(pref_value) FROM prefs WHERE pref_key='email_not' AND pref_user_id=$current_user->user_id") == 0)
	     $db->query("INSERT INTO prefs (pref_user_id, pref_key, pref_value) VALUES ($current_user->user_id, 'email_not', '1')");

	// user == login?
	if ($tipo == 1) {
		echo '<div class="notes">';

		$irudiak = irudiak_atera($_REQUEST['settings'], $_REQUEST['orokorra'], $_REQUEST['enviados']);
		echo $irudiak;
		echo '<br/><br/><br/>';

		if ($_REQUEST['settings']) 
			echo inprimatu_aldaketak();
		elseif ($_REQUEST['orokorra'])  {
			if ($current_user->admin) 
			echo inprimatu_orokorra();
			}
		elseif ($_REQUEST['enviados'])
			echo inprimatu_mezupribatuak('outbox');
		else echo inprimatu_mezupribatuak('inbox');
			
		echo '</div>';
		
	} else echo bidali_pribatua(); // enviar mensaje privado al user
}

/* Otras funciones */

function laguna_da($prefered_id) {
	if ($prefered_id != $current_user->user_id) {
		$friend_value = 'AND friend_value > 0';
	} else {
		$friend_value = '';
	}

	$prefered_id = $db->escape($prefered_id);
	$prefered_total= $db->get_var("SELECT count(*) FROM friends WHERE friend_type='manual' AND friend_from=$prefered_id $friend_value");
	$dbusers = $db->get_results("SELECT friend_to as who FROM friends, users WHERE friend_type='manual' AND friend_from=$prefered_id and user_id = friend_to $friend_value order by user_login asc LIMIT $prefered_offset,$prefered_page_size");
	break;

	if ($dbusers) {
		$friend = new User;
		foreach($dbusers as $dbuser) {
			$friend->id=$dbuser->who;
			$friend->read();
			echo '<div class="friends-item">';
			echo '<a href="'.get_user_uri($friend->username).'" title="'.$friend->username.'">';
			echo '<img src="'.get_avatar_url($friend->id, $friend->avatar, 20).'" width="20" height="20" alt="'.$friend->username.'"/>';
			echo $friend->username.'</a>&nbsp;';
			if ($current_user->user_id > 0 && $current_user->user_id != $friend->id)
				echo '<a id="friend-'.$prefered_type.'-'.$current_user->user_id.'-'.$friend->id.'" href="javascript:obtener(\'amigos.php\',\''.$current_user->user_id.'\',\'friend-'.$prefered_type.'-'.$current_user->user_id.'-'.$friend->id.'\',0,\''.$friend->id.'\')">'.friend_teaser($current_user->user_id, $friend->id).'</a>';
		}
	}
}

/*
 * Comprueba si un usuario tiene acceso para enviar mensajes
 */

function pribatuetako_sarbidea($zeinek, $zeinei)
{
	global $db, $rows, $user, $offset, $page_size, $globals, $current_user;
	
	// Erregistratutako erabiltzaileak soilik.
	if (!($current_user->authenticated)) return false;
	// Nor sar daiteke? (lagunak, guztiak, inor ez)
	if (lagunen_konfigurazioa($zeinek, $zeinei)) return true;
	
	// Akatsa egon da egoera aurkitzean.
	return false;
}

/*
 * Configuración de los mensajes a recibir.
 */
 
function lagunen_konfigurazioa($nork, $nori)
{
	global $db;
	$nori = $db->escape($nori);
	$mezuaren_kfg = $db->query("SELECT * FROM mezuak_nork WHERE idusr='$nori' ORDER BY id asc");
	if ($mezuaren_kfg) { 
		$zeintzuai = $db->get_row("SELECT * FROM mezuak_nork WHERE idusr='$nori' ORDER BY id asc");
		if ($zeintzuai->nori == "todos") return true;
		if ($zeintzuai->nori == "amigos") return !(friend_exists($nork, $nori) <= 0);
		if ($zeintzuai->nori == "nadie") return false;
	} else return true;
}

function notify_user($from, $to, $text, $titulo) {
    global $current_user, $globals, $db;

    include_once mnminclude.'user.php';

    $sender = new User();
    $sender->id = $from;

    $user = new User();
    $user->id = $to;
   
    if (! $user->read() || ! $sender->read()) return;

    $quiere = $db->get_var("SELECT pref_value FROM prefs WHERE pref_key='email_not' AND pref_user_id=$user->id");

    if (!$quiere) return;

    if (! check_email($user->email)) return;
    
    $url = '(Puedes leerlo también en: http://'.get_server_name().$globals['base_url'].'postbox/'.$user->username.')';

    if (!$titulo)
         $subject = "Notificación de mensaje privado de $sender->username";
    else $subject = "Mensaje de $sender->username: ".$titulo;

    $adv = 'Este mensaje ha sido enviado con tu autorización. Si no deseas seguir recibiendo emails desde esta dirección, deshabilita la opción en http://'.get_server_name().$globals['base_url'].'postbox/'.$user->username.'?settings=1'; 
    
    $message = $url."\n\n".$text."\n\n\n\n\n\n".$adv;

    require_once(mnminclude.'mail.php');
    send_mail($user->email, $subject, $message);
}