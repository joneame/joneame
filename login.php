<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'ts.php');
include(mnminclude.'log.php');

$globals['ads'] = true;

if ($current_user->user_id > 0 && !$current_user->admin) {

    header("Location: " . get_user_uri($current_user->user_login));
}
// We use the original IP to avoid cheating by httheaders
$globals['original_user_ip_int'] = sprintf("%u", ip2long($_SERVER["REMOTE_ADDR"]));
// Clean return variable
if(!empty($_REQUEST['return']))
    $_REQUEST['return'] = clean_input_string($_REQUEST['return']);

if($_GET["op"] == 'logout') {
       // check the user is really authenticated (to avoid bucles due to bad caching)
       if ($current_user->user_id > 0) {
               $current_user->Logout($_REQUEST['return']);
        } else {
                header("Location: http://".get_server_name().$globals['base_url']);
                die;
        }
}
// We need it because we modify headers
ob_start();

/* neiko: evitar redirección estúpida */
$globals['return'] = $_GET['return'];

do_header("Login | Jonéame");
echo '<div id="singlewrap">' . "\n";
echo '<div class="genericform">'."\n";
if($_GET["op"] === 'recover' || !empty($_POST['recover'])) {
    do_recover();
} else {
    do_login();
}
echo '</div>'."\n";
echo '</div>'."\n"; // singlewrap
do_footer();

function do_login() {
    global $current_user, $globals;
    $previous_login_failed =  log_get_date('login_failed', $globals['original_user_ip_int'], 0, 90);
    if($previous_login_failed < 3 && empty($_POST["processlogin"])) {
        echo '<div class="faq" style="float:left; width:65%; margin-top: 10px;">'."\n";
        // Only prints if the user was redirected from submit.php
        if (!empty($_REQUEST['return']) && preg_match('/nueva_historia\.php/', $_REQUEST['return'])) {
            echo '<p style="border:1px solid #adcee9; font-size:1.3em; background:#FEFBEA; font-weight:bold; padding:0.5em 1em;">Para enviar una historia debes ser un usuario registrado</p>'."\n";
        }
        echo '<h3> ¿Qué es Jonéame?</h3>'."\n";
        echo '<p>Es una red social, donde compartir enlaces, conocer gente, chatear, y perder el tiempo, desarrollado por <a href="credits.php"> los propios usuarios </a>, partiendo de la base de Menéame. Ten paciencia si algo no te funciona. Puedes contactar con nosotros para reportar los errores que veas.</p>'."\n";
        echo '<h3>¿Cómo surge Jonéame?</h3>'."\n";
        echo '<p>Jonéame comienza en el cachondeo, y acaba en el cachondeo. Nos gusta la pornografía, fotos, noticias, videos graciosos, noticias manipuladas, humor, viñetas, curiosidades, etc...Y se permite el microblogging! <a href="condiciones.php">Léete las condiciones de uso</a> antes de enviar nada.</p>'."\n";
        echo '</li>'."\n";
        echo '</ul>'."\n";
        echo '<h3>¿Todavía no eres usuario de Jonéame?</h3>'."\n";
        echo '<p>Como usuario registrado podrás, entre otras cosas:</p>'."\n";
        echo '<ul>'."\n";
        echo '<li>'."\n";
        echo '<strong>Enviar historias</strong><br />'."\n";
        echo '<p>Una vez registrado puedes enviar las historias que consideres curiosas/cachondas/interesantes para la comunidad. Si tienes algún tipo de duda sobre que tipo de historias puedes enviar revisa nuestras <a href="faq-es.php" title="Acerca de Jonéame">preguntas frecuentes sobre Jonéame.</a></p>'."\n";
        echo '</li>'."\n";
        echo '<li>'."\n";
        echo '<strong>Escribir comentarios</strong><br />'."\n";
        echo '<p>Puedes escribir tu opinión sobre las historias enviadas a Jonéame mediante comentarios de texto. También puedes votar positivamente aquellos comentarios ingeniosos, divertidos o interesantes y negativamente aquellos que consideres inoportunos.</p>'."\n";
        echo '</li>'."\n";
        echo '<li>'."\n";
        echo '<strong>Chatear en tiempo real desde la queer chat</strong><br />'."\n";
        echo '<p>Gracias a la <a href="cotillona.php" title="queer chat">queer chat</a> puedes ver en tiempo real toda la actividad de Jonéame. Además como usuario registrado podrás chatear con mucha más gente de la comunidad mafiosa. Puedes usarla para ponerte en contacto con algún administrador también si lo deseas.</p>'."\n";
        echo '</li>'."\n";
        echo '<li>'."\n";
        echo '<strong>Enviar cortos</strong><br />'."\n";
        echo '<p>Una vez registrado puedes <a href="cortos.php" title="cortos">enviar cortos</a>. Los cortos son unos textos que hablen de lo que quieras. Lo que se te ocurra. Estos apareceran en la parte superior de toda la web, seleccionados aleatoriamente. ¿A qué esperas para ver el tuyo?</p>'."\n";
        echo '</li>'."\n";
        echo '<li>'."\n";
        echo '<strong>Enviar mensajes privados a otros usuarios</strong><br />'."\n";
        echo '<p>Exclusivamente en Jonéame puedes enviar mensajes privados a otros usuarios registrados. Para ello solo tienes que ir al perfil de dicho usuario y hacer click en "privados". No, si al final acabas ligando y todo.</p>'."\n";
        echo '</li>'."\n";
                echo '<li>'."\n";
        echo '<strong>Hacer encuestas</strong><br />'."\n";
        echo '<p>También puedes enviar encuestas. Añade las opciones que desees y los usuarios podrán responderla, eligiendo entre esas opciones.</p>'."\n";
        echo '</li>'."\n";
        echo '</ul>'."\n";
        echo '<center><h3 class="boton" style="width: 150px; padding: 3px 10px 10px;"><a href="register.php">Regístrate ahora</a></h3></center>'."\n";
        echo '</div>'."\n";
        echo '<div class="genericform" style="float:right; width:30%; margin: 0"><h4>login</h4>'."\n";
    } else {
        echo '<div class="genericform" style="float:auto;"><h4>login</h4>'."\n";
    }
    echo '<form action="login.php" id="thisform" method="post" class="fondo-caja">'."\n";
    echo '<fieldset>'."\n";

    if($_POST["processlogin"] == 1) {
        $username = clean_input_string(trim($_POST['username']));
        $password = trim($_POST['password']);
        $persistent = $_POST['persistent'];
        if ($previous_login_failed > 3  && !ts_is_human()) {
            log_insert('login_failed', $globals['original_user_ip_int'], 0);
            recover_error(_('¡El código de seguridad no es correcto, ponte las gafas!'));
        } elseif ($current_user->Authenticate($username, md5($password), $persistent) == false) {
            log_insert('login_failed', $globals['original_user_ip_int'], 0);
            recover_error(_('Usuario/email o contraseña errónea'));
            $previous_login_failed++;
        } else {
              // User authenticated, store clones
              foreach ($current_user->GetClones() as $id) {
                insert_clon($current_user->user_id, $id, 'COOK:'.$globals['user_ip']);
            }
            if(!empty($_REQUEST['return'])) {
                header('Location: '.$_REQUEST['return']);
            } else {
                header('Location: ./');
            }
            die;
        }
    }
    echo '<p><label for="name">'._('nombre de usuario o email').':</label><br />'."\n";
    echo '<input type="text" name="username" size="25" tabindex="1" id="name" value="'.htmlentities($username).'" /></p>'."\n";
    echo '<p><label for="password">'._('contraseña').':</label><br />'."\n";
    echo '<input type="password" name="password" id="password" size="25" tabindex="2"/></p>'."\n";
    echo '<p><input type="checkbox" name="persistent" id="remember" tabindex="3"/><label for="remember">'._('&nbsp;recordarme en este equipo').'</label></p>'."\n";
    if ($previous_login_failed > 2) {
        ts_print_form();
    }
    echo '<p><input type="submit" value="iniciar sesión" class="button" tabindex="4" />'."\n";

    echo '<input type="hidden" name="processlogin" value="1"/></p>'."\n";
    echo '<input type="hidden" name="return" value="'.htmlspecialchars($_REQUEST['return']).'"/>'."\n";
    echo '</fieldset>'. "\n";
    echo '</form>'."\n";
    echo '<div class="recoverpass" align="center"><h4 class="boton"><a href="login.php?op=recover">'._('¿Has olvidado la contraseña?').'</a></h4></div>'."\n";
    echo '</div>'."\n";
    echo '<br clear="all"/>&nbsp;';
}

function do_recover() {
    global $globals;


    // warn warn warn
    // dont do stats of password recovering pages - Jon
    $globals['recovery'] = true;

    echo '<div class="genericform">'."\n";
    echo '<h4>'._("recuperación de contraseñas").'</h4>'."\n";
    echo '<form class="fondo-caja" action="login.php" id="thisform-recover" method="post">'."\n";
    echo '<fieldset>'."\n";
    $username = clean_input_string(trim($_POST['username']));
    if(!empty($_POST['recover'])) {
        if (!ts_is_human()) {
            recover_error(_('¡El código de seguridad no es correcto!'));
        } else {
            require_once(mnminclude.'user.php');
            $user=new User();
            if (preg_match('/.+@.+/', $username)) {
                // It's an email address
                $user->email= $username;
            } else {
                $user->username= $username;
            }
            if(!$user->read()) {
                recover_error(_('El usuario o email no existe...'));
                return false;
            }
            if($user->level == 'disabled') {
                recover_error(_('Cuenta deshabilitada...'));
                return false;
        }
            require_once(mnminclude.'mail.php');
            $sent = send_recover_mail($user);
        }
    }
    if (!$sent) {
        echo '<label for="name">'._('introduce tu nombre de usuario o email').':</label><br />'."\n";
        echo '<input type="text" name="username" size="25" tabindex="1" id="name" value="'.$username.'" />'."\n";
        echo '<p>'._('(recibirás un email para cambiar la contraseña)').'</p>';
        echo '<input type="hidden" name="recover" value="1"/>'."\n";
        echo '<input type="hidden" name="return" value="'.htmlspecialchars($_REQUEST['return']).'"/>'."\n";
        ts_print_form();
        echo '<br /><input type="submit" value="'._('recibir e-mail').'" class="button" />'."\n";
        echo '</fieldset>'."\n";
        echo '</form>'."\n";
    }
    echo '</div>'."\n";
}


function recover_error($message) {
    echo '<div class="form-error">';
    echo '<p>'.$message.'</p>';
    echo '</div>';
}

