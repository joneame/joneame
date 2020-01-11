<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

// IMPORTANT:
// You should not edit anything here. These values are meant to be edited in
// local.php. If a certain value doesn't exist there, just copy it from here
// and paste it there before you edit it. Anything in local.php will override
// whatever is set here.

define("mnmpath", dirname(__FILE__));
define("mnminclude", dirname(__FILE__).'/libs/');
ini_set("include_path", '.:'.mnminclude.':'.mnmpath);

$dblang            = 'es';
$page_size        = 40;
$anonnymous_vote = true;
$external_ads = true;

//Configuration values
$globals['external_ads'] = false;
$globals['external_user_ads'] = false;

// Specify you base url, "/" if is the root document
$globals['base_url'] = '/';

// Base domain
$globals['domain'] = 'joneame.net';
// is https used?
$globals['https'] = true;

// lounge
//$globals['lounge_mezua'] = 'Estamos de mantenimiento.';
//$globals['lounge_general'] = 'lounge.php';

//conditions
$globals['normas'] = $globals['base_url'].'ayuda.php?id=uso';

//legal terms
$globals['legal'] = $globals['base_url'].'ayuda.php?id=legal';

//leave empty if you don't have the rewrite rule in apache
//RewriteRule ^/historia/(.+)$ /historia.php/$1  [L,NS]
$globals['base_story_url'] = 'historia/';

//RewriteRule ^/c/(.+)$ /comment.php/$1  [L,NS]
$globals['base_comment_url'] = 'c/';

//RewriteRule ^/corto/(.+)$ /corto.php/$1  [L,NS]
$globals['base_corto_url'] = 'corto/';

//RewriteRule ^/encuesta/(.+)$ /encuesta.php/$1  [L,NS]
$globals['base_encuesta_url'] = 'encuesta/';

//RewriteRule ^/mafioso/(.+)$ /mafioso.php/$1  [L,NS]
$globals['base_user_url'] = 'mafioso/';

//RewriteRule ^/notitas(/.*)$ /sneakme/index.php$1 [L,NS]
$globals['base_sneakme_url'] = 'notitas/';

//RewriteRule ^/postbox(/.*)$ /postbox.php$1 [L,NS]
$globals['base_mensaje_url'] = 'postbox/';

// Comentarios encuestas
$globals['base_poll_comment_url'] = 'opinion/';

// Memcache, set hostname if enabled
$globals['memcache_host'] = '';
$globals['memcache_port'] = ''; // optional

// páginas comentarios
$globals['comments_page_size'] = 30;
$globals['comments_page_threshold'] = 1.10;

// Tiempo en segundos disponible para editar comentarios
$globals['comment_edit_time'] = 360;

//favicon general
$globals['favicon'] = 'img/favicons/favicon-jnm.png';

// tamaño thumbnails
$globals['thumb_size'] = 70;

// url del thumbnail
$globals['thumbnail_url'] = 'img/v2/no-avatar-80.png';

// How many *global* links for last 3 minutes
// If user->karma > 10 then limit = limit*1.5
$globals['limit_3_minutes'] = 20;

//limite máximo de noticias en 24 horas
$globals['limit_user_24_hours'] = 50;

//límite de joneos en cola consecutivos
$globals['max_successive_links_in_queue'] = 5;

$globals['max_sneakers'] = 250;
$globals['max_comments'] = 255;

$globals['time_enabled_comments'] = 2147483647; // 68 años
$globals['time_enabled_votes'] = 5184000; // 2 meses

// enable or disable the detection of real IP behind transparents proxies
$globals['check_behind_proxy'] = false;

// 1 muestra negativos, 0 no los muestra
$globals['show_negatives'] = 1;

$globals['min_karma_for_negatives'] = 7.2;
$globals['min_user_votes'] = 0;  // For new users and also enable check of sent versus voted
$globals['min_karma_for_links'] = 5.7;
$globals['min_karma_for_comments'] = 6.9;
$globals['carisma_para_votar_cortos'] = 7.5;
$globals['time_enabled_note_votes'] = 864000;
$globals['min_karma_for_posts'] = 6.8;
$globals['min_karma_for_sneaker'] = 6.1;
$globals['min_time_for_comments'] = 0; // Time to wait until first comment (from user_validated_date)
$globals['min_karma_for_comment_votes'] = 7.1;

//cache
$globals['cache_dir'] = 'cache';

// Haanga templates dir
$globals['haanga_templates'] = 'templates';

//avatars
$globals['avatars_max_size'] = 200000;
$globals['avatars_files_per_dir'] = 1000;
$globals['avatars_allowed_sizes'] = Array (80, 40, 25, 20);

// ruta del archivo CSS
$globals['css_main'] = 'css/joneame.css';

//caracteres máximos de notitas y tiempo de espera entre ellas
$globals['longitud_notitas'] = 425;
$globals['tiempo_entre_notitas'] = 30;

//carisma de comentarios para ser resaltadas
$globals['resaltar_comentarios'] = '110';
$globals['ocultar_comentarios'] = '-60';

//carisma de notas para ser resaltadas
$globals['resaltar_notas'] = '90';
$globals['ocultar_notas'] = '-60';

//¿pueden los usuarios votar aleatorio?
$globals ['aleatorios_usuarios_activados'] = true;

//aleatorios máximos por usuario en 10 ultimos minutos
$globals['aleatorios_maximos_por_usuario'] = 5;

//votos aleatorios máximos por joneo
$globals['aleatorios_maximos'] = 4;

// store access stats
$globals['save_pageloads'] = false;

//cajas de populares
$globals['mostrar_caja_pendientes'] = true;
$globals['mostrar_caja_publicadas'] = true;
$globals['mostrar_caja_pron'] = true;

//¿están activados los cortos?
$globals['cortos_activados'] = true;

//está el buscador activado?
$globals['buscador_activado'] = true;

//¿está la versión movil funcionando?
$globals['version_movil'] = false;

//¿blog en nuestro servidor?
$globals['blog'] = true;

//numero máximo de veces que puede editar un corto el autor del mismo
$globals['ediciones_max_cortos'] = 2;

//tiempo de edición, en segundos, que disponen los usuarios para editar una historia
$globals['edicion_historias_usuario'] = 3600;

//tiempo máximo (en días) que puede durar una encuesta
$globals['tiempo_maximo_encuesta'] = 45;

//¿permitir no escribir entradilla?
$globals['permitir_sin_entradilla'] = true;

//¿reports en notitas?
$globals['reports_notitas'] = false;

//¿preview de youtube en comentarios/notitas?
$globals['do_video'] = false;

//page_size_pendientes
$globals['pendientes_page_size'] = 40;

// clave pública y privada de Recaptcha
//$globals['recaptcha_public_key'] = '';
//$globals['recaptcha_private_key'] = '';

//buscador
//$globals['sphinx_server'] = '';
//$globals['sphinx_port'] = '';

// Mailgun stuff
$globals['mailgun_domain'] = '';
$globals['mailgun_key'] = '';

// código de analytics
$globals['analytics_code'] = '';

// Greeting in several languages
// Unfortunately, array constructor does not work properly with GNU _()

/*
$greetings = array('bienvenid@'=>'españolo y española ;-)','hola'=>'español','kaixo'=>'euskera',
        'apa'=>'catalán','com va'=>'catalán','com vas'=>'catalán','cómo andás'=>'argentino','epa'=>'catalán',
        'aupa'=>'euskera','ieup'=>'vasco','gñap'=>'gñapés','aiya'=>'sindarin','hello'=>'inglés',
        'uep'=>'catalán','hey'=>'inglés','passa'=>'catalán','hi'=>'inglés','hunga hunga'=>'troglodita',
        'salut'=>'francés','bonjour'=>'francés','hallo'=>'alemán','guten tag'=>'alemán','moin moin'=>'frisón',
        'Dobrý de.'=>'eslovaco','helo'=>'SMTP','minjhani'=>'tsonga','kunjhani'=>'tsonga','ciao'=>'italiano',
        'hej'=>'danés','god dag'=>'noruego','have a nice day'=>'inglés','as-salaam-aleykum'=>'Árabe',
        'marhabah'=>'árabe','sabbah-el-khair'=>'árabe','salaam or do-rood'=>'árabe','namaste'=>'hindi',
        'ahn nyeong ha se yo'=>'coreano','ahn nyeong'=>'coreano','goedendag'=>'neerlandés','priviet'=>'ruso',
        'zdravstvuyte'=>'ruso','ni hao'=>'chino','nei ho'=>'chino','shalom'=>'hebreo','hei'=>'finés',
        'oi'=>'portugués','olá'=>'portugués','hej'=>'sueco','god dag'=>'sueco','mingalarbar'=>'birmano',
        'merhaba'=>'turco','ciao'=>'italiano','kumusta ka'=>'tagalo','saluton'=>'esperanto','vanakkam'=>'tamil',
        'jambo'=>'swahili','mbote'=>'lingala','namaskar'=>'malayalam','dzie. dobry'=>'polaco','cze..'=>'polaco',
        'aloha'=>'hawaiano','jo napot'=>'húngaro','szervusz'=>'húngaro','dobriy ranok'=>'ucraniano',
        'labdien'=>'letón','sveiki'=>'letón','chau'=>'letón','hyv&auml;&auml; p&auml;iv&auml;'=>'finés','moi'=>'finés',
        'hei'=>'finés','yia sou'=>'griego','yia sas'=>'griego','gó&eth;an dag'=>'islandés','h&aelig;'=>'islandés',
        'ellohay'=>'pig latin','namaskkaram'=>'telugú','adaab'=>'urdu','baagunnara'=>'telugú','niltze'=>'náhuatl',
        'hao'=>'náhuatl','bok'=>'croata','ya\'at\'eeh'=>'navajo','mer.ba'=>'maltés','mambo'=>'congo',
        'salam aleikum'=>'senegalés','gr&uuml;zi'=>'alemán suizo','haj'=>'escandinavo','hall&aring;'=>'escandinavo',
        'mo&iuml;en'=>'luxemburgués','talofa'=>'samoano','malo'=>'samoano','malo e lelei'=>'tongano',
        'la orana'=>'tahitiano','kia ora'=>'maorí','buna ziua'=>'rumano','kem che'=>'guyaratí',
        'namaskar'=>'canarés','kwe kwe'=>'tailandés','hola, oh'=>'asturiano','h&acirc;u'=>'nicolino',
        'vary'=>'nicolino','Привет'=>'ruso','konnichiwa'=>'japonés','hello world'=>'holamundo',
        'klaatu barada nikto'=>'el idioma de Klatu y Gort','ola'=>'gallego','boas'=>'gallego',
        'bonos díes'=>'asturiano', 'hola karmorrero'=>'el idioma karmorrero','OLA HAMIJO'=>'HOYGAN',
        'eres un serdo,'=>'oriolet', 'hola furcia'=>'el_fail',
        'holaaaaaaaaaa genteeeee'=>'me_joneo_pensando_en_ti',
                'juguémos, '=>'jugador', 'viciémos, '=>'jugador',

    );
*/

// For Facebook authentication
//$globals['facebook_key'] = '';
//$globals['facebook_secret'] = '';

// Twitter authentication
//$globals['oauth']['twitter']['consumer_key'] = '';
//$globals['oauth']['twitter']['consumer_secret'] = '';

// The maximun amount of annonymous votes vs user votes in 1/2 hour
// 3 means 3 times annonymous votes as user votes in that period
$anon_to_user_votes = 40;
$site_key = 12345679;

$globals['haanga_cache'] = 'haanga_cache/';

// carisma de usuarios anónimos
$anon_carisma    = 7;

// Don't touch behind this

// Send logs to "log_user", is windows compatible
openlog(false, LOG_ODELAY, LOG_USER);

// Set an utf-8 locale if there is no utf-8 defined
if (!preg_match('/utf-8/i', setlocale(LC_CTYPE, 0)))  {
    setlocale(LC_CTYPE, "en_US.UTF-8");
}

date_default_timezone_set('Europe/Madrid');

include 'local.php';
include mnminclude.'db.php';
include mnminclude.'utils.php';
include mnminclude.'login.php';
require mnminclude.'Haanga.php';

/* Load template engine here */
$hangaa_config = array('debug' => true,
    'template_dir' => mnmpath.'/'.$globals['haanga_templates'],
    'autoload'     => TRUE, /* Don't use Haanga's autoloader */
    'bootstrap'    => 'haanga_bootstrap',
    'compiler' => array( /* opts for the tpl compiler */
        /* Avoid use if empty($var) */
        'if_empty' => FALSE,
        /* we're smart enought to know when escape :-) */
        'autoescape' => FALSE,
        /* let's save bandwidth */
        'strip_whitespace' => TRUE,
        /* call php functions from the template */
        'allow_exec'  => TRUE,
        /* global $global, $current_user for all templates */
        'global' => array('globals', 'current_user'),
    ),
    'use_hash_filename' => FALSE,
    'cache_dir'=> mnmpath.'/'.$globals['cache_dir'].'/Haanga/'.$_SERVER['SERVER_NAME']
);

Haanga::configure($hangaa_config);

/*function haanga_bootstrap()
{
    // bootstrap function, load our custom tags/filter
    require mnminclude.'haanga_jnm.php';
}*/