<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// David Martí <neikokz@gmail.com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');

do_header(_('Navegadores soportados | Jonéame') . '');
$ua = browser_info();

$navegador = "fail";

if ($ua['firefox'])
    $navegador = "Firefox ".$ua['firefox'];
if ($ua['shiretoko'])
    $navegador = "Shiretoko ".$ua['shiretoko'];
if ($ua['iceweasel'])
    $navegador = "Iceweasel ".$ua['iceweasel'];
if ($ua['msie'])
    $navegador = "Internet Explorer ".$ua['msie'];
if ($ua['opera'])
    $navegador = "Opera ".$ua['opera'];
if ($ua['chrome'])
    $navegador = "Chrome ".$ua['chrome'];
if ($ua['safari'])
    $navegador = "Safari ".$ua['safari'];
if ($ua['konqueror'])
        $navegador = "Konqueror ".$ua['konqueror'];
if ($navegador == "fail")
    $navegador = "un navegador desconocido :(";

echo '<div id="singlewrap">';

echo '<div class="genericform"><div class="genericform">';
echo '<h4>Navegadores soportados</h4>';

echo '<fieldset class="fondo-caja espaciador">';
echo 'Estás usando <strong>'.$navegador.'</strong>. ';

if ($ua['firefox'] && $ua['firefox'] < 3)
    navegador_incorrecto();
if ($ua['shiretoko'] && $ua['shiretoko'] < 3)
    navegador_incorrecto();
if ($ua['iceweasel'] && $ua['iceweasel'] < 3)
    navegador_incorrecto();
if ($ua['msie'] && $ua['msie'] < 7)
    navegador_incorrecto();
if ($ua['opera'] && $ua['opera'] < 9)
    navegador_incorrecto();
if ($ua['safari'] && $ua['safari'] < 3)
    navegador_incorrecto();
if ($ua['konqueror'] && $ua['konqueror'] < 4)
        navegador_incorrecto();


echo '</div></div></div></div>';


function navegador_incorrecto () {
  echo 'Ese navegador <strong>no está soportado</strong> por Jonéame, probablemente debido a su antigüedad o ';
  echo 'a su incumplimiento con los estándares. Si estás sufriendo problemas de funcionalidad, o algunos elementos de la página ';
  echo 'aparecen "descuadrados", deberías probar a actualizar tu navegador.<br/><br/>';

  if (strpos($_SERVER['HTTP_USER_AGENT'], "Windows")) {
    echo 'Parece que usas <strong>Windows</strong>. Estos son los navegadores recomendados:';
    echo '<ul style="margin-left: 20px;">';
    echo '<li><a href="http://www.mozilla-europe.org/es/firefox/">Mozilla Firefox 3.6</a></li>';
    echo '<li><a href="http://www.opera.com/download/">Opera 10</a></li>';
    echo '<li><a href="http://www.google.es/chrome/">Google Chrome 5</a></li>';
    echo '<li><a href="http://www.apple.com/es/safari/download/">Safari 4</a></li>';
    echo '</ul>';
  } else if (strpos($_SERVER['HTTP_USER_AGENT'], "Linux")) {
    echo 'Parece que usas <strong>Linux</strong>. Estos son los navegadores recomendados (ofrecemos enlaces a las webs de descarga, pero te ';
    echo 'recomendamos instalarlo usando el sistema de paquetería de tu distro):';
    echo '<ul style="margin-left: 20px;">';
    echo '<li><a href="http://www.mozilla-europe.org/es/firefox/">Mozilla Firefox 3.6</a></li>';
    echo '<li><a href="http://www.opera.com/download/">Opera 10</a></li>';
    echo '<li><a href="http://www.google.es/chrome/">Google Chrome 5</a></li>';
    echo '<li><a href="http://konqueror.kde.org/">Konqueror 4</a></li>';
    echo '</ul>';
  } else if (strpos($_SERVER['HTTP_USER_AGENT'], "Mac")) {
    echo 'Parece que usas un <strong>Mac</strong>. Estos son los navegadores recomendados:';
    echo '<ul style="margin-left: 20px;">';
    echo '<li><a href="http://www.apple.com/es/safari/download/">Safari 4</a></li>';
    echo '<li><a href="http://www.mozilla-europe.org/es/firefox/">Mozilla Firefox 3.6</a></li>';
    echo '<li><a href="http://www.google.es/chrome/">Google Chrome 5</a></li>';
    echo '<li><a href="http://www.opera.com/download/">Opera 10</a></li>';
    echo '</ul>';
  } else
    echo 'No logramos detectar tu sistema operativo :(<br/>Prueba a obtener una versión más actual del navegador que usas habitualmente.';

    echo '<br/>Si una vez has actualizado tu navegador sigues teniendo problemas, contacta con nosotros.';
}

do_footer();