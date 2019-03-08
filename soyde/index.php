<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// David Martí <neikokz@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

// Cada frase debe comenzar en mayúsculas y no ser finalizada con un punto (será añadido después) --neiKo

$soyde_mensajes = array (
    "Yo no joneo gatos ni pr0n,<br />yo joneo REDES",
    "Voto mafia a cada noticia interesante<br />que es enviada",
    "Exijo mis joneos de gatitos diarios",
    "Me paso media vida en /b/",
    "Cuando nos hacen spam, hay redada",
    "Lo veo todo de color azul"
);

$i = rand(0, count($soyde_mensajes) - 1);
$soy_joneante = $soyde_mensajes[$i];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Soy Joneante</title>
<link rel="shortcut icon" href="http://joneame.net/img/favicons/favicon-jnm.png"> <!-- !!TODO -->
</head>

<style type="text/css">
* {
  margin: 0;
}
body {
  background: #b7d5e7;
  font-size: 13px;
  font-family: Arial,Helvetica,sans-serif;
}
.contenedor-outer {
  margin: 100px auto 0;
  width: 700px;
}
.contenedor-inner {
  padding: 22px 22px 22px 375px;
  height: 330px;
  background: white url('/img/estructura/404.png') left top no-repeat; /* !!TODO */
  -moz-border-radius: 5px;
  -webkit-border-radius: 5px;
  text-align: right;
  position: relative;
}
.foot {
  position: absolute;
  bottom: 22px;
  right: 22px;
  width: 300px;
  font-size: 15px;
}
.contacto {
  font-size: 11px;
}
h1 {
  font-size: 25px;
}
h2 {
  font-size: 15px;
  font-style: italic;
  color: #555;
}
a, a:active, a:visited {
  color: #88bffe;
  text-decoration: none;
}
a:hover {
  color: #6bb0fe;
  text-decoration: underline;
}
</style>

<body>

<div class="contenedor-outer">
  <div class="contenedor-inner">

    <h1>Jonéame</h1>
    <!-- <h2>liada parda</h2> -->

    <div class="foot">
      <?php echo $soy_joneante; ?>.<br/><strong>Soy Joneante.</strong><br/><br/>

      <div class="contacto">
    <a href="javascript:location.reload();">Recargar</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>