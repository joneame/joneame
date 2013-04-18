<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Aritz <aritz@itxaropena.org>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Mezu {
	 var $datuak;
	 var $id;
	 var $rows;
	 var $error =0;

	 
	 // Elimina un dato de la base de datos
	 function ezabatu() {
				global $db;
				$sqldel = "DELETE FROM mezuak WHERE id='".$this->id."'";
				if($db->query($sqldel)) return true;
				return false;
	 }
	 
	 // Devuelve mensajes en un array.
	 function mezuak_jaso($mota, $offset, $page_size) {
		global $db;
	 	// $this->id dato que quiero leer

		$this->id = $db->garbitu($this->id, false);
	 	if ($mota == 'inbox') {	
		$rows = $db->get_var("SELECT count(*) FROM mezuak WHERE nori=".$this->id. " AND posta='recipient'");
		$comments = $db->get_results("SELECT * FROM mezuak WHERE nori=".$this->id." AND posta='recipient' ORDER BY id desc LIMIT $offset,$page_size");
		} else {
		$rows = $db->get_var("SELECT count(*) FROM mezuak WHERE nork=".$this->id. " AND posta='sender'");
		$comments = $db->get_results("SELECT * FROM mezuak WHERE nork=".$this->id." AND posta='sender' ORDER BY id desc LIMIT $offset,$page_size");
		}
	
		if ($comments) {
			$this->rows = $rows;
			$i = 0;	
			foreach ($comments as $mezu) {
				// Sacamos los datos del usuario.
				$erab=new User();
			
				if ($mota == 'inbox')
				$erab->id = $mezu->nork;
				else
				$erab->id = $mezu->nori;
			
			
				if ($erab->read())	{ 
					$avatar = get_avatar_url($erab->id, $erab->avatar, 20);
					$izena = $erab->username; 
				} else  continue;
			
				// Si es el outbox..
				if ($mota == 'outbox')
				$this->id = $mezu->nori;
		
				// Desencriptamos los mensajes
				$xtea = new XTEA($this->getKey($this->id));
			
				$mezu->testua = $xtea->decrypt($mezu->testua);
				$mezu->titulua = $xtea->decrypt($mezu->titulua);
			
				// Cortamos los titulos
				if (strlen($mezu->titulua) > 70) $mezu->titulua = substr($mezu->titulua, 0, 70)."...";

				// Protegemos contra XSS
				$mezu->testua = clean_text($mezu->testua);
				$mezu->titulua = clean_text($mezu->titulua);
				if (preg_match('(GLOBAL)', $mezu->titulua))
				$mezu->mensaje_global = true;
				else $mezu->mensaje_global = false;
			
				// Y metemos todos los valores en un nuevo array.
				$bidali[0] = $mezu;
				$bidali[1] = $avatar;
				$bidali[2] = $izena;
				$bidali[3] = $erab->id;
				$bidali[5] = $mezu->id;
				$bidaltzeko_datuak[$i] = $bidali;
				$i++;
				}
			
				// Los metemos en datos para que pueda recogerlos.
				$this->datuak = $bidaltzeko_datuak;
		} // existe?
		else $this->error = "EMPTY";
	
	 }
	 
	 // Devuelve el mensaje en un array.
	 function jaso_mezua($zein, $nondik) {
	 	global $db;

		$this->id = $db->garbitu($this->id, false);
		$zein = $db->garbitu($zein, false);

	 	if ($nondik == 'inbox')
		$mezu = $db->get_row("SELECT * FROM mezuak WHERE nori=".$this->id." AND id=".$zein." AND posta='recipient' LIMIT 1");
		else
		$mezu = $db->get_row("SELECT * FROM mezuak WHERE nork=".$this->id." AND id=".$zein." AND posta='sender' LIMIT 1");
	
		if ($mezu)
		{
		$erab=new User();
		if ($nondik == 'inbox')
		$erab->id = $mezu->nork;
		else {
		$erab->id = $mezu->nori;
		$this->id = $mezu->nori;
		}
		
		if ($erab->read())	{ 
		$avatar = get_avatar_url($erab->id, $erab->avatar, 40);
		$avatar2 = get_avatar_url($erab->id, $erab->avatar, 20);
		$izena = $erab->username; 
		} else { 
		$avatar = 0;
		$izena = "(desconocido)"; 
		}
	
		// Desencriptamos los mensajes
		$xtea = new XTEA($this->getKey($this->id));
	
		$mezu->testua = $xtea->decrypt($mezu->testua);
		$mezu->titulua = $xtea->decrypt($mezu->titulua);

		// Orain irakurrita dago.	
		if ($mezu->irakurrita == '0')
		$db->query("UPDATE mezuak SET irakurrita = '1' WHERE (id = '".$zein."')");

		if (preg_match('(GLOBAL)', $mezu->titulua))
		$mezu->mensaje_global = true;
		else $mezu->mensaje_global = false;
		
		// Y metemos todos los valores en un nuevo array.
		$bidali[0] = $mezu;
		$bidali[1] = $avatar;
		$bidali[2] = $izena;
		$bidali[3] = $erab->id;
		$bidali[4] = $avatar2;
	
		$this->datuak = $bidali;
	
		 } else $this->error = "NO_MESSAGE";
	 }
	 
	 
	 function getKey($id)
	 {
		global $globals;

		$db_securekey = new ezSQL_mysql($globals['db_user_secure'], $globals['db_password_secure'], $globals['db_name_secure'], $globals['db_server_secure'], $globals['db_master']);

		$bera = $db_securekey->get_row("SELECT * FROM api_msg WHERE uid='".$db_securekey->escape($id)."'");

		if ($bera) return $bera->api; else return 0;
	 }

	function generateKey($user) {
			global $globals;
			if ($this->getKey($user->id)) return 0; // si el id ya existe.. hay un error
		
			// conexion al servidor	
			$db_securekey = new ezSQL_mysql($globals['db_user_secure'], $globals['db_password_secure'], $globals['db_name_secure'], $globals['db_server_secure'], $globals['db_master_secure']);

		
			$api =substr(md5($user->id.time().$user->ip.rand(1, 9999)), 0, 10);
			$db_securekey->query("UNLOCK TABLES"); // desbloqueo de tablas
			$db_securekey->query("INSERT INTO api_msg VALUES ('$user->id', '$api')");	
			$db_securekey->query("LOCK TABLES `api_msg` WRITE"); // bloqueo de tablas
	}
	 
 
 }
