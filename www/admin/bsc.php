<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'html1.php');
include(mnminclude.'historial.class.php');
include(mnminclude.'user.php');

$page_size = 20;

$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = false;


/*
 * Bakarrik god mailako erabiltzaileak sar daitezke. - aritz
 */
if ((!$current_user->admin || $current_user->user_id == 0)) {

	do_error(_('Esta página es sólo para administradores'), 403);
}

	
	/*HEADER*/
	do_header(_('Historial de BSC'));
	
	admin_tabs();

	echo '<div id="singlewrap">' . "\n";

	mostrar_logs();
	echo '</table>';
	
	do_pages($rows, $page_size, false);
	
	echo "</div>";
	echo "</div>"; // singlewrap
	do_footer();

	function admin_tabs() {
		global $globals;
		$active = ' class="current"';
		echo '<ul class="tabmain">' . "\n";

	$tabs=array("hostname", "punished_hostname", "email", /*"ip", "words",*/ "proxy");

	foreach($tabs as $tab) {
		
			if ($tab == 'proxy') { //pone IP en el texto en vez de proxy
				echo '<li><a href="'.$globals['base_url'].'admin/bans.php?admin='.$tab.'">'._('IP').'</a></li>' . "\n";
			} else {
				echo '<li> <a href="'.$globals['base_url'].'admin/bans.php?admin='.$tab.'">'._($tab).'</a></li>' . "\n";
			}
	}
	
              
		echo '<li class="current"><a  href="'.$globals['base_url'].'admin/bsc.php"><b>'._('BSC').'</b></a></li>' . "\n";

		echo '</ul>' . "\n";
	}


	//logs de BSC
	function mostrar_logs() {
	global $globals, $current_user, $db;
	
		//maneja las ediciones propuestas por los usuarios
		$rows = $db->get_var("SELECT COUNT(log_ref_id) FROM logs WHERE log_type='bsc_new'");
		$eskaera = $db->get_results("SELECT * FROM logs WHERE log_type='bsc_new' ORDER BY log_date ASC");

 
		//nuestro propio menu para BSC
			echo '<div class="genericform" style="margin:0">';

			echo '<div style="float:right;">'."\n";

			echo '</div>'; 
			echo '<table style="font-size: 10pt">';
			echo '<tr><th width="30">'._('ID').'</th>';
			echo '<th>'._('razón').'</th>';
			echo '<th width="10%">'._('realizada por').'</th>';
			echo '<th width="10%">'._('a').'</th>';
			echo '<th width="10%">'._('el').'</th></tr>';
		
		if ($eskaera){	
			$contador = 1;	
			foreach ($eskaera as $log) {

					$historial = new Historial;
					$historial->id = $log->log_ref_id;
					$historial->read_id() ;
					$user=new User();
					$user->id = $log->log_user_id;
			
					// buscar el usuario
				if ($user->read())	$izena = $user->username; else $izena = "(eliminado)";
						echo '<tr>';
						echo '<td width="30">'.$contador.'</td>';
						$contador ++;
						echo '<td style="overflow: hidden; ">'.text_to_html(clean_text($historial->historial_texto)).'</td> ';
	
						if ($izena != "(eliminado)")
						echo '<td width="10%"><a href="'.get_user_uri($user->username).'">'.clean_text($izena).'</a></td>';
						else
						echo '<td>'.clean_text($izena).'</td>';

						echo  '<td>'.clean_text($historial->quien).'</td>';
						echo '<td width="10%">';
						echo $log->log_date; // o $historial->historial_fecha
						echo '</td>';
						echo '</tr>';
				
					}
		}

						echo '<tr>';
						echo '<td>-</td>';
						echo '<td style="overflow: hidden;"></td>';
						echo '<td></td>';
						echo '<td></td>';
						echo '<td></td>';
						echo '</tr>';
						echo '<tr>';
						echo '<td></td>';
						echo '<td style="overflow: hidden;">BSC totales: '.$rows.'</td>';
						if ($rows > 0)
						echo '<td style="overflow: hidden;"></td>';
						echo '<td></td>';
						echo '<td></td>';
						echo '</tr>';


	
	}