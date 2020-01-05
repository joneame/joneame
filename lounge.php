<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Jonéame liada parda | Lounge</title>
        <link rel="shortcut icon" href="/img/favicons/favicon-jnm.png">
    </head>
    <style type="text/css">
        * {
            margin: 0;
        }
        body {
            background: #b7d5e7;
            font-size: 13px;
            font-family: Arial, Helvetica, sans-serif;
        }
        .contenedor-outer {
            margin: 100px auto 0;
            width: 700px;
        }
        .contenedor-inner {
            padding: 22px 22px 22px 375px;
            height: 330px;
            background: white url('/lounge-fondo.<?php echo rand (1, 2); ?>.png') left top no-repeat;
            -moz-border-radius: 5px;
            -webkit-border-radius: 5px;
            text-align: right;
            position: relative;
        }
        .foot {
            position: absolute;
            bottom: 22px;
            right: 22px;
            width: 290px;
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
                <h2>liada parda</h2>
                <div class="foot">
                    <?php echo $globals[ 'lounge_mezua']; ?>
                    <br/>
                    <br/>
                    <div class="contacto">
                        Administración de Jonéame<br/>
                        <strong>Contacto</strong>: admin <em>arroba</em> joneame <em>puntito</em> net
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>