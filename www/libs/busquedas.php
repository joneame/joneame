<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jon Arano <arano.jon@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

define('FAV_SEARCH_YES', '<img src="'.get_cover_pixel().'" class="img-flotante icon heart-on" title="'._('en favoritos').'" alt="del"/>');
define('FAV_SEARCH_NO', '<img src="'.get_cover_pixel().'" class="img-flotante icon heart-off" title="'._('agregar a favoritos').'" alt="add"/>');

function existe($user, $texto) {
	global $db;
	return intval($db->get_var("SELECT count(*) FROM busquedas_guardadas WHERE usuario=$user and texto='$texto'"));
}

function insertar($user, $texto) {
	global $db;
	return $db->query("REPLACE INTO busquedas_guardadas (id, texto, usuario) VALUES (NULL, '$texto', $user)");
}

function borrar($user, $texto) {
	global $db;
	return $db->query("DELETE FROM busquedas_guardadas WHERE usuario=$user and texto='$texto'");
}

function anadir($user, $texto ) {
	if(existe($user, $texto)) {
		borrar($user, $texto);
		return FAV_SEARCH_NO;
	} else {
		insertar($user, $texto);
		return FAV_SEARCH_YES;
	}
}

function imagen($user, $texto) {
	if (existe($user, $texto)) {
		return FAV_SEARCH_YES;
	} else {
		return FAV_SEARCH_NO;
	}
}