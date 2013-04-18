<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

// header("Location: http://joneame.net/lounge.php");

if (0 && $_SERVER['REMOTE_ADDR'] != "ip de devel" && $_SERVER['REMOTE_ADDR'] != "") {
	header("HTTP/1.0 503");
	die("De momento, configurando el servidor. Paciencia.");
}

if ($_SERVER['HTTP_HOST'] && $_SERVER['HTTP_HOST'] != 'joneame.net') {
	header('Location: http://joneame.net'.$_SERVER['REQUEST_URI']);
	die;
}

$globals['db_server'] = '';
$globals['db_name'] = '';
$globals['db_user'] = '';
$globals['db_password'] = '';

$globals['mysql_persistent'] = true;
$globals['mysql_master_persistent'] = true;
$globals['mysql_cache_dir'] = '';

$globals['thumbs_dir'] = '';
$globals['thumbs_url'] = '';

if ($_SERVER['REQUEST_URI'] == '/') {
	$globals['description'] = 
		'Web de entretenimiento donde los propios usuarios pueden enviar y votar noticias, imágenes y vídeos de humor.';
}

// secure db
$globals['db_server_secure'] = '';
$globals['db_name_secure'] = '';
$globals['db_user_secure'] = '';
$globals['db_password_secure'] = '';

//google maps API
$globals ['google_maps_api'] = '';

// clave pública y privada de Recaptcha 
$globals['recaptcha_public_key'] = '';
$globals['recaptcha_private_key'] = '';

//buscador
$globals['sphinx_server'] = '';
$globals['sphinx_port'] = '';

// For Facebook authentication
$globals['facebook_key'] = '';
$globals['facebook_secret'] = '';

// Twitter authentication
$globals['oauth']['twitter']['consumer_key'] = '';
$globals['oauth']['twitter']['consumer_secret'] = '';
$globals['oauth']['twitter']['oauth_token'] = '';
$globals['oauth']['twitter']['oauth_token_secret'] = '';