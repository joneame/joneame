<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

        //imprime el cuadro de edicion del corto
        function print_edit_form($corto) {
                global $current_user, $site_key, $globals, $db;


        if (is_numeric($_REQUEST['id']))
        $id = $_REQUEST['id'];


        if ($current_user->user_level != 'god'  && $current_user->user_id != $corto->id_autor ) die;

                $rows = min(40, max(substr_count($corto->texto, "\n") * 2, 8));
                echo '<div class="genericform"><div class="genericform">'."\n";
                echo '<div class="commentform">'."\n";
                echo '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="post">'."\n";
                echo '<h4>'._('editar el corto').' #'.$corto->id.'</h4>'."\n";
                echo '<fieldset class="fondo-caja">';
                echo '<div class="fondo-caja"><textarea name="corto_content" id="editar'.$corto->id.'" rows="'.$rows.'" style="width: 99%;">'.$corto->texto.'</textarea></div>'."\n";
                echo '<input class="button" type="submit" name="submit" value="'._('guardar edición').'" />'."\n";
                echo '<input type="hidden" name="process" value="editcomment" />'."\n";
                echo '<input type="hidden" name="key" value="'.md5($current_user->user_id.$current_user->user_date.$site_key).'" />'."\n";
                echo '<input type="hidden" name="id" value="'.$corto->id.'" />'."\n";
                echo '<input type="hidden" name="user_id" value="'.$current_user->user_id.'" />'."\n";
                echo '</fieldset>'."\n";
                echo '</form>'."\n";
                echo "</div></div></div>\n";
        }


        //guarda el corto (sólo lo usan gods)

        function save_corto () {
                global $db, $current_user, $globals,  $site_key;
    

                //nos aseguramos que tenemos un ID numérico
                if (is_numeric($_REQUEST['id']))
                $id = $_REQUEST['id'];

                //esto será lo que vamos a guardar
                $texto = clean_text($_POST['corto_content'], 0, false, 10000);
                $md5 = md5($current_user->user_id.$current_user->user_date.$site_key);

                if($_POST['key'] == $md5 && strlen($texto) > 2) {
                        
			if ($current_user->user_level == 'god'){

		                //el admin guarda el corto (sin necesidad de enviar a propuestas)
		                $db->query("UPDATE cortos set texto='$texto' WHERE id=$id");  
				// echo _('se ha guardado la propuesta <br/>');

				//y llevamos de vuelta a ediciones
		                header('Location: '.$globals['base_url'].'admin/ediciones_cortos.php');                
				die;
                        } else {
        
		                echo _('no se ha guardado ¿tienes permisos para ello? ');
		                die;
                        
			}
                } else {
                        echo _('error actualizando. Seguramente clave incorrecta ');
                        die;
                }
        }


        function aceptar_propuesta ($key, $corto) { //aceptar propuesta de edicion (esta funcion solo la usaran admins)
                global $db, $current_user, $globals, $site_key;


                //si tiene la llave correcta 
                if($key == get_security_key()) {

                 //averiguar cual era la propuesta
       		 $nuevo_corto = $db->get_row("SELECT texto_propuesta FROM edicion_corto WHERE id_corto='$corto->id'");
                
                        //si es god actualizamos
                         if ($current_user->user_level == 'god'){

		                //ahora está actualizado
		                 $db->query("UPDATE cortos set texto='$nuevo_corto->texto_propuesta', ediciones=ediciones+1 WHERE id=$corto->id");

		                //borramos la propuesta de la tabla
		         	$db->query("DELETE FROM edicion_corto WHERE id_corto='$corto->id'");

		                //y llevamos de vuelta a ediciones
		                header('Location: '.$globals['base_url'].'admin/ediciones_cortos.php');

                        }else{

                            echo _('no se ha guardado ¿tienes permisos para ello?');
			    die;

                             }
                
                } else {
                        echo _('error actualizando. Seguramente clave incorrecta');
                
                        die;
                }
        }

        //borrar la propuesta de edicion
        function borrar_propuesta ($key, $corto) { //esta funcion solo la usaran admins
                global $db, $current_user, $globals, $site_key;


                //si tiene la llave correcta y es GOD
                if($key == get_security_key() && $current_user->user_level == 'god') {
                
		        //borramos la propuesta de la tabla
		        $db->query("DELETE FROM edicion_corto WHERE id_corto='$corto->id'");
		        
			//y le llevamos de vuelta a las propuestas
		        header('Location: '.$globals['base_url'].'admin/ediciones_cortos.php');         
                
                } else {
                        echo _('error actualizando ¿tienes permisos para ello? ');
                        die;
                }
        }

        //el usuario introduce una propuesta de edicion de corto, guarda en la BD el corto original y la copia
        function guardar_copia($corto){
		global $db, $current_user, $site_key;

		if ($corto->existe_pendiente_de_edicion()) {
		
		do_header('Cortos | Jonéame'); //inicializamos header, puesto que antes no se ha inicializado
		echo _('Ya existe una edición pendiente para este mismo corto. <br/>');
		return;
		}
                
		$key = md5($current_user->user_id.$current_user->user_date.$site_key);

                //el texto original 
                $copia_texto = $corto->texto; 
        
                //esta sera la propuesta
                $corto->texto= clean_text($_POST['corto_content'], 0, false, 10000);
                      
                //requisitos->Llave correcta, que sea el autor, y que haya algo escrito

         if($_POST['key'] == $key && $current_user->user_id == $corto->id_autor && strlen($corto->texto) > 2 ) {            
                        
              //introducimos los 2, la propuesta y la copia por seacaso
              $db->query("INSERT INTO edicion_corto (texto_copia,autor, texto_propuesta, id_corto) VALUES ('$copia_texto', '$current_user->user_id','$corto->texto','$corto->id')");
        
              //una vez guardado redirigimos al perfil
              header('Location: '.get_user_uri($current_user->user_login).'/cortos'); 
                       
         } else {
                        echo _('error actualizando. clave incorrecta seguramente.');
			die;
                }

        }

	function borrar_corto_y_propuesta($key, $corto) {
		global $db, $current_user, $globals, $site_key;

                //si tiene la llave correcta y es GOD
                if($key == get_security_key() && $current_user->user_level == 'god') {

			//borramos el corto
                	$db->query("DELETE FROM cortos WHERE id=$corto->id");
		        //borramos la propuesta de la tabla
		        $db->query("DELETE FROM edicion_corto WHERE id_corto='$corto->id'");
		        
			//y le llevamos de vuelta a las propuestas
		        header('Location: '.$globals['base_url'].'admin/ediciones_cortos.php');         
                
                } else {
                        echo _('error actualizando ¿tienes permisos para ello? ');
                        die;
                }

	}
