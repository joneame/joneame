<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../libs/country.class.php');
$ip = $_GET['ip'];
if (empty($ip)) return "Bye";
$ip = addslashes($ip);

header('Content-Type: text/html; charset=latin1');
//echo '<html xml:lang="es" lang="es">';

echo "\n".'<script>$("#modalTitle").html("** Localizador IP Jonéame **")</script>'."\n";

/* Get all IP Info */
$info = geoip_record_by_name($ip);
/* Country flag here */
$a = new ip2country("../countries.txt","../flags/"); 
$arr=$a->parseIP($ip); 
echo "País localizado: ".$info[country_name]." <img src=\"".$arr[2]."\" title=\"".$arr[1]."\"><br />"; 

echo "Provincia/Continente: ".$info[continent_code];
echo "<br/>";
echo "Ciudad: ".$info['city']."<br/>";

$isp = geoip_isp_by_name($ip);

if ($isp) {
    echo 'ISP identificado como: ' . $isp."<br/>";
} else echo "No se ha podido identificar el ISP<br/>";
//echo $info[latitude].$info[latitude]; 

echo "<br/><br/>"; 

/*
echo '<iframe width="200" height="150" frameborder="0" scrolling="no" 
marginheight="0" marginwidth="0" src="http://maps.google.com/maps?q='.$info[latitude].',+'.$info[latitude].'+(Localizado+justo+aqu%C3%AD)&amp;hl=es&amp;ie=UTF8&amp;z=14&amp;iwloc=A&amp;ll='.$info[latitude].','.$info[latitude].'&amp;output=embed"></iframe><br 
/><small><a 
href="http://maps.google.com/maps?q='.$info[latitude].',+'.$info[latitude].'+(Localizado+justo+aqu%C3%AD)&amp;hl=es&amp;ie=UTF8&amp;z=14&amp;iwloc=A&amp;ll='.$info[latitude].','.$info[latitude].'&amp;source=embed" 
style="color:#0000FF;text-align:left">Ver mapa más grande</a></small>';
*/

/*
echo '<iframe width="425" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=es&amp;geocode=&amp;q='.$info[latitude].',+'.$info[latitude].'&amp;ie=UTF8&amp;z=14&amp;output=embed"></iframe><br/>';

echo '<iframe width="425" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?q='.$info[latitude].',+'.$info[longitude].'+(La+casa+del+troll)&amp;hl=es&amp;ie=UTF8&amp;z=14&amp;iwloc=A&amp;ll='.$info[latitude].','.$info[longitude].'&amp;output=embed"></iframe>';
*/
echo '<span align="center">';
echo '<a href="http://maps.google.com/maps?q='.$info[latitude].',+'.$info[longitude].'+(La+casa+del+troll)&iwloc=A&hl=es" target="_blank">Ver en Google Maps</a>';
echo '</span>';