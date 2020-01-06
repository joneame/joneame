<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

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

echo "<br/><br/>";

echo '<span align="center">';
echo '<a href="https://maps.google.com/maps?q='.$info[latitude].',+'.$info[longitude].'+(La+casa+del+troll)&iwloc=A&hl=es" target="_blank">Ver en Google Maps</a>';
echo '</span>';
