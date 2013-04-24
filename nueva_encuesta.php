<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jon <arano.jon@gmail.com>, Kaydarks <kepazaman@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'ts.php');
include(mnminclude.'log.php');
include(mnminclude.'user.php');
include(mnminclude.'db.php');
include(mnminclude.'encuestas.php');

$globals['extra_js'] = array('polls.js');
$globals['ads'] = true;

// We use the original IP to avoid cheating by httheaders
$globals['original_user_ip_int'] = sprintf("%u", ip2long($_SERVER["REMOTE_ADDR"]));

// Clean return variable
if(!empty($_REQUEST['return']))
	$_REQUEST['return'] = clean_input_string($_REQUEST['return']);

if($_GET["op"] === 'logout') {
	$current_user->Logout($_REQUEST['return']);
}

// We need it because we modify headers
ob_start();
  

if(isset($_POST["fase"])) {

	force_authentication();

	switch ($_POST["fase"]) {
			case 1:
				do_header(_("enviar encuesta 2/4"), "post");
				echo '<div id="singlewrap">' . "\n";
				do_submit1();
				break;
			case 2: 
				do_header(_("enviar encuesta 3/4"), "post");
				echo '<div id="singlewrap">' . "\n";
				do_submit2();
				break;
			case 3:
				do_submit3();
			exit;
			}
} else {
	force_authentication();
	do_header(_("nueva encuesta 1/4"), "post");
	echo '<div id="singlewrap">' . "\n";
	do_submit0();
}

echo "</div>\n"; // singlewrap
do_footer();
exit;


function do_submit0() {
	echo '<h2>'._('envío de una nueva encuesta: paso 1 de 4').'</h2>';
	echo '<div class="faq">';
	echo '<h3>'._('te explicamos como va:').'</h3>';
	echo '<ul class="instruction-list">';
	echo '<li><strong>'._('pregunta').':</strong> '._('utilizamos esta sección para preguntar todo tipo de preguntas, chorras, serias, sobre lo majo que es tu vecino...de la vida misma').'</li>';
	echo '<li><strong>'._('no importa el tema').':</strong> '._('pero si el tema es un coñazo, pasaremos de contestarte').'</li>';
	echo '<li><strong>'._('colabora').':</strong> '._('colabora votando las encuestas de los demás, nadie desea ser ignorado').'</li>';
	echo '<li><strong>'._('opciones').':</strong> '._('puedes añadir tantas opciones como deseas, pero que tengan un sentido mínimo').'</li>';
	echo '<li><strong>'._('carisma').':</strong> '._('las encuestas no cuentan para el cálculo del carisma').'</li>';
	echo '<li><strong>'._('respeto').':</strong> '._('no te rías de las encuestas de los demás, a no ser que sean disparatadas, claro está').'</li>';
	echo '<br/><li>Dicho esto, ¡cuéntanos tu duda!</li></ul>'."\n";
	echo '</ul></div>'."\n";
	print_empty_submit_form();
}


function print_empty_submit_form() {
	global $globals, $pregunta, $_POST;

	echo '<br/><div class="genericform">';
	echo '<h4>m&aacute;ndanos tu pregunta</h4>';
	echo '<fieldset class="fondo-caja"><form action="nueva_encuesta.php" method="post" id="bidali" name="bidali">';
	echo '<input type="text" name="pregunta" id="pregunta" class="form-full" value="" /></p>';
	echo '<input type="hidden" name="fase" value="1" />';
	echo '<input class="button" type="submit" value="'._('siguiente &#187;').'" ';
	echo '/>&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span>';
	echo '</form></fieldset>';
	echo '</div>';

	$pregunta = $_POST['pregunta'];
	$pregunta = clean_text($pregunta);
	$pregunta = clear_whitespace($pregunta);
}

function do_submit1 () {
	global $globals, $current_user, $_POST;

	if(strlen($_POST['pregunta']) < 7) {
		error_encuesta(_("pregunta demasiado corta"));
	} else {
		echo '<h2>'._('envío de una nueva encuesta: paso 2 de 4').'</h2>'."\n";
		echo '<form class="" action="nueva_encuesta.php" method="post" id="bidali" name="bidali"><br>';
		echo '<div class="genericform">';
		echo '<h4>nueva encuesta</h4>';
		echo '<fieldset class="fondo-caja">';
		echo '<p><label for="testua">'._('tu pregunta será').':</label><br />';
		echo $_POST['pregunta'].'<br><br>';
		echo '<input type="hidden" name="pregunta" id="pregunta" value="'.$_POST['pregunta'].'"/>';

		echo '<label for="testua">'._('introduce una descripción').':</label><br />';
		echo '<p><input type="text" name="contenido" id="contenido" value="" style="width: 90%;"/></p>';

		echo '<label for="testua">'._('duración de la encuesta').':</label>';
		echo '<p><span class="note">'._('ponga la duración de la encuesta, el mínimo será de un día. Por defecto, la encuesta durará 15 días').'</span><br/>';
		echo '<input type="text" name="fechaFin" id="fechaFin" value="15" size="2"/></p>';

		//voto multiple
		echo '<fieldset class="redondo"><legend class="mini barra redondo">'._('voto múltiple').'</legend>'."\n";
		echo _('la activación del voto múltiple supone que los usuarios pueden votar más de una opción al mismo tiempo').'.';
		echo '<br/><br/><input type="checkbox" name="multiple" id="multiple" value="1" />&nbsp;<label for="multiple"><strong>'._('activar voto múltiple').' </strong></label>'."\n";
		echo '</fieldset><br/>'."\n";
	
		/* Desactivar promoción de encuesta */
		echo '<fieldset class="redondo"><legend class="mini barra redondo">'._('notita de promoción automática').'</legend>'."\n";
		echo '<br/><br/><input type="checkbox" name="no_promocion" id="no_promocion" value="1" />&nbsp;<label for="multiple"><strong>'._('no insertar notita de promoción para esta encuesta').' </strong></label>'."\n";
		echo '</fieldset><br/>'."\n";
		

		echo '<p><input class="button" type=button onclick="window.history.go(-1)" value="'._('&#171; retroceder').'"/> ';
		echo '<input type="hidden" name="fase" value="2" />'."\n";
		echo '<input class="button" type="submit" value="'._('siguiente &#187;').'" ';
		echo '/>&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span></p>';
		echo '</fieldset>';
		echo '</div>';
		echo '</form>';
	}
}

function do_submit2 () {
	global $globals, $current_user, $_POST;


	if(strlen($_POST['contenido']) < 13) {
		error_encuesta(_("descripción demasiado corta"));
	} else {
		echo '<h2>'._('envío de una nueva encuesta: paso 3 de 4').'</h2>'."\n";
		echo '<br/><div class="genericform">';
		echo '<form class="" action="nueva_encuesta.php" method="post" id="bidali" name="bidali">';
		echo '<h4>'._($_POST['pregunta']).'</h4><fieldset class="fondo-caja">'."\n";
		echo '<input type="hidden" name="pregunta" id="pregunta" value="'.$_POST['pregunta'].'"/>';

		echo '<p><label for="testua">'._('descripción').':</label><br />';
		echo $_POST['contenido'].'<br/><br/>';
		echo '<input type="hidden" name="contenido" id="contenido" value="'.$_POST['contenido'].'"/>';

		echo '<p><label for="testua">'._('la encuesta finalizará').':</label><br />';

		if ((empty($_POST['fechaFin'])) || ($_POST['fechaFin'] == '15') || $_POST['fechaFin'] < 1) {
			echo '15 días después del envío';
			$_POST['fechaFin'] = 15;
			}
		else {
                       if ($_POST['fechaFin'] > $globals['tiempo_maximo_encuesta'] ) $_POST['fechaFin'] = $globals['tiempo_maximo_encuesta'];
			$unixtime_gen = $globals['now'] + ($_POST['fechaFin']*3600*24);
			echo 'el ' . date("d/m/Y", $unixtime_gen).'';
		}
		echo '<input type="hidden" name="fechaFin" id="fechaFin" value="'.$_POST['fechaFin'].'"/><br/><br/>';

		if ($_POST['multiple']) {
			echo '<p><label for="testua">'._('multivoto activado').'</label> <br/><br />';
			echo '<input type="hidden" name="multiple" id="multiple" value="'.$_POST['multiple'].'"/>';
		} else {
			echo '<p><label for="testua">'._('multivoto desactivado').'</label> <br/><br/>';
		}

		if ($_POST['no_promocion'] == 1) {
			echo '<p><label for="testua">'._('no se insertará notita de promoción').'</label> <br/><br />';
			echo '<input type="hidden" name="no_promocion" id="no_promocion" value="1"/>';
		} else {
			echo '<p><label for="testua">'._('¡ojo! se insertará notita de promoción').'</label> <br/><br />';
			echo '<input type="hidden" name="no_promocion" id="no_promocion" value="0"/>';
		}

		echo '<input type="hidden" name="cantidadOpciones" id="cantidadOpciones" value="'.$_POST['cantidadOpciones'].'"/>';

		echo '<fieldset class="redondo"><legend class="mini barra redondo">'._('opciones de encuesta').'</legend>'."\n";
		echo _('introduce las opciones').':<br />';

		echo '<dl class="encuesta-opciones" id="encuesta-opciones">';
		for ($i=0 ; $i < 3 ; $i++) {
			echo '<dt id="opcion-'.$i.'">'._('opción '.($i+1)).'</dt><dd id="opcion-t-'.$i.'"><input type="text" name="opts['.$i.']" id="opts['.$i.']" type="text" size="70" value=""/>'."\n";
			if ($i == 2) echo '<span id="delete-2"><a src="'.get_cover_pixel().'" href="javascript:delete_option(2)"><img class="icon delete img-flotante" alt="eliminar opcion" title="eliminar opcion"/></a></span></dd>';
			else echo '</dd>';

		}

		echo '</dl>';
		echo '<a src="'.get_cover_pixel().'" href="javascript:add_option()"><img class="icon add" alt="añadir opcion" title="añadir opcion"/></a>';
		echo '</fieldset><br/>'."\n";

		echo '<p><input class="button" type=button onclick="window.history.go(-1)" value="'._('&#171; retroceder').'"/> ';
		echo '<input type="hidden" name="fase" value="3" />'."\n";
		echo '<input class="button" type="submit" value="'._('enviar y finalizar &#187;').'" ';
		echo '/>&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span></p>';
		echo '</form>';

		echo '</fieldset><br/>'."\n";

	}
}

function do_submit3 () {
global  $db, $globals, $current_user, $_POST;

	/* inserción de datos */
	$encuesta=new Encuesta;
	$encuesta->id=$db->escape($_POST['id']);
	$encuesta->titulo= $db->escape($_POST['pregunta']);
	$encuesta->contenido = clean_text($_POST['contenido']);
	
	
	if ((empty($_POST['fechaFin'])) || $_POST['fechaFin'] < 1) // por si intenta hacer algo estúpido
			$_POST['fechaFin'] = 15;
	
	/* fecha de finalización */
	$fechaFin = time() + (intval($_POST['fechaFin'])*3600*24);
	$encuesta->finish = $fechaFin;
	
	/* datos sobre el user */
	$encuesta->autor = $current_user->user_id;
	$encuesta->ip = $globals['user_ip'];

	if (!$_POST['multiple'])
		$encuesta->multiple = 0;
	else
		$encuesta->multiple = 1;

	$opts = count($_POST['opts']);


	$totales = $opts;
	$id = 0;
	
	for ($i = 0; $i < $opts; $i++) // opciones del usuario, leer y eliminar las casillas vacias
	{
		if (!empty($_POST['opts'][$id])) { 
			$encuesta->opciones[$id] = clean_text($_POST['opts'][$id]); 
			$id ++;
		} else $totales --;
		
	}
	
	$totales = ($totales >= 0) ? $totales : 0;
	
	if ($totales <= 1) $errores = 'un monton';
	
	$encuesta->opciones['count'] = $totales;

	/* por si hay algun error */
	if ($errores) {
		do_header(_("enviar encuesta 3/4"), "post");
		error_encuesta(_("el minimo de opciones son 2"));
		do_footer();
		return;
	} 

	/* Verificamos que no haya sido grabada previamente por el mismo autor, para evitar encuestas duplicadas */
	$verificar = $db->get_row("SELECT encuesta_id, encuesta_title, encuesta_user_id FROM encuestas WHERE encuesta_title LIKE '$encuesta->title'");

	if ($verificar && $encuesta->titulo == $verificar->encuesta_title && $verificar->encuesta_user_id == $current_user->user_id){
		header('Location: '.get_encuesta_uri($verificar->encuesta_id));
		die;
	}

	/* almacenamos todo */
	$encuesta->id = 0;
	$encuesta->almacenar();
	/* Promoción en notitas, si el usuario lo desea */
	if ($_POST['no_promocion'] == 0)
	$encuesta->insert_promotion_post();
	
	/*Guardamos el ID, $encuesta->destroyData() nos lo quita y lo necesitamos para introducir el log
		Otra solución sería quitar el destroyData()*/
	$id = $encuesta->id;

	/* destrucción masiva, muahahahahhaa */
	$encuesta->destroyData();
		
	// Add the new link log/event	
	require_once(mnminclude.'log.php');
	log_conditional_insert('encuesta_new', $id, $encuesta->autor);
	
	header('Location: '.get_encuesta_uri($id));

}

function error_encuesta($mess) {
	static $previous_error=false;
	
	if (!$previous_error) {
		// ex container-wide
		echo '<div class="genericform">'."\n"; // this div MUST be closed after function call!
		echo '<h2>'._('uups!').'</h2>'."\n";
		$previous_error = true;
	}
	echo '<h4 class="redondo">&nbsp;&nbsp;'._($mess).'</h4><br/>'."\n";

	echo '<form class="genericform">'."\n";
	echo '<p><input class="button" type=button onclick="window.history.go(-1)" value="'._('&#171; retroceder').'"/></p>'."\n";
	echo '</form>'."\n";
	echo '</div>'."\n";
	
}
