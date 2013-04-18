<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jon Arano <arano.jon@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include mnminclude.'user.php';
include 'funciones.jopi.php';

class Jonevision {
	var $id = 0;
	var $titulo;
	var $link;
	var $votos = 0;
	var $puntos = 0;
	var $user_id = 0;
	var $login = '(??)';
	var $avatar = 0;

	/* lectura */

	function read() {
		global $db;

		// curamos en salud..
		$this->id = intval($this->id);

		if ($jonevision = $db->get_row("SELECT * FROM jonevision WHERE id=".$this->id)) {

			$this->id=$jonevision->id;
			$this->titulo=$jonevision->titulo;
			$this->link=$jonevision->link;
			$this->votos=$jonevision->votos;
			$this->puntos=$jonevision->puntos;

			// variables de usuario
			if ($jonevision->user_id > 0){

				/* User info */
				$datos_usuario = new User($jonevision->user_id);

				/* Global data */
				$this->user_id=$jonevision->user_id;
				$this->login=$datos_usuario->username;
				$this->avatar=$datos_usuario->avatar;

			} else {

				$this->login = 'Anónimo';
				$this->avatar = 0;
				$this->user_id = 0;

			}

			return true;
		}

		return false;
	}

	function print_jonevision() {
		global $globals;

		$post_class = 'notita-body fondo-caja';
		$post_meta_class = 'barra';
		echo '<div class="'.$post_meta_class.'">';
		echo '<div class="notitas-info">';

		if ($this->user_id > 0)
			echo '<a href="'.get_user_uri($this->login).'" class="uname'.$post_meta_class_link.'">'.$this->login.'</a> »'. $this->titulo;
		else
			echo "Anónimo »".$this->titulo;

		echo '</div>';
		echo '<div class="notitas-votes-info">';
		if ($globals['now'] > 1292184000){ // 21.00
		echo 'Votos: '.$this->votos.' Puntos: '.$this->puntos; 
		}	
		else 
		echo 'Los puntos obtenidos se harán públicos el domingo 12 a partir de las 21.00 y hasta las 22.00 ';
		echo '</div></div>';
		echo '<div class="'.$post_class.'" id="pid">';
		
		if ($this->user_id > 0 && $this->avatar > 0 )
			echo '<a class="user-avatar" href="'.get_user_uri($this->login).'"><img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$this->user_id.');" onmouseout="tooltip.clear(event);" class="avatar" src="'.get_avatar_url($this->user_id, $this->avatar, 80).'" width="80" height="80" alt="'.$this->login.'"/></a>';
		else
			echo '<div class="user-avatar"><img src="'.get_no_avatar_url(80).'"/></div>';

		echo '<div class="notita-text">';
		echo do_jonevision_convert($this->link);
		echo '</div>';     
		echo '</div>';
	
		echo '<br/>';
		
	}
}

function do_joneinfo() {

	// Variables generales
	$ult_actu = "09/12/2010 12:30"; // cambia eso cada vez que lo actualices

	echo '<div id="singlewrap">';
	echo '<h2 class="faq-title">Jonevisión</h2><br/>';
	echo '<div class="faq" style="margin: 0 30px 75px 150px;">';
	echo '<ol>';

	echo '<h5>¿Qué es jonevisión?</h5>';

	
	echo '<p>Jonevisión (idea original de <a href="mafioso/zoidberg">Zoidberg</a>) es un concurso made in Jonéame. Trata de enviar <strong>canciones</strong> conocidas (o no), <strong>versionadas por los usuarios</strong>. Éstas serán subidas a una cuenta de <a href="http://www.goear.com">Goear</a>, y serán expuestas en esta misma página.</p>';

	echo '<h5>¿C&oacute;mo participo?</h5>';
	echo '<p>Cada joneante podrá subir tantas canciones como quiera siempre y cuando las suba todas a la misma cuenta de Goear. 
	Una vez subida la canción a Goear, se enviará un correo electrónico indicando la URL a <em>jonevision@joneame.net</em></p>';
	echo '<h5>¿Y las votaciones?</h5>';
	echo '<p>Cada joneante registrado votará con el clásico sistema Eurovisivo de 12, 10, 8...Las votaciones comenzarán el día 10/12/2010, a las 22.00 GMT +01';
	echo '</p>';
	echo '<p>¿No te fías de nosotros? Tienes más información <a href="notitas/jonarano/20207">aquí</a>, <a href="notitas/Zoidberg/20233">aquí</a>, <a href="notitas/Zoidberg/20233">aquí</a>, <a href="notitas/Zoidberg/20234">aquí</a>, <a href="notitas/Zoidberg/20235">aquí</a>, <a href="notitas/Zoidberg/20239">aquí</a>, o <a href ="notitas/Zoidberg/20259">aquí.</a></p>';

	echo '</ol>';
	echo '</div>';
	echo '</div>';
}

