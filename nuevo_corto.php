<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Aritz <aritz@itxaropena.org>, Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

// Atencion al consumidor: Sí, es el login.php modificado a nuestras necesidades.

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'ts.php');
include(mnminclude.'log.php');

// Warning, it redirects to the content of the variable
if (!empty($globals['lounge_cortos'])) {
    header('Location: https://'.get_server_name().$globals['base_url'].$globals['lounge_cortos']);
    die;
}

if (!$globals['cortos_activados'])
    do_error('Cortos desactivados', 403, false);

$globals['original_user_ip_int'] = sprintf("%u", ip2long($_SERVER["REMOTE_ADDR"]));

// We need it because we modify headers
ob_start();

do_header(_('Nuevo corto | Jonéame'));
echo '<div id="singlewrap">';

echo '<div class="genericform">';
    force_authentication();

if(!empty($_POST['testua'])) {

    echo '<h2>'._('Propuesta de un nuevo corto: etapa 2 de 2').'</h2>';
    if (!ts_is_human())
        akatsa("Código de validación incorrecto: póngase las gafas.");
    else envia_corto($_POST['testua']);
} else carga_submit_cortos();


echo '</div>';
echo '</div>'; // singlewrap

do_footer();

// Testua bidaliko dugu.

function envia_corto($testu) {
    global $current_user, $corto;

    $corto = new Corto;
    $corto->user_id = $current_user->user_id;

    echo '<div class="faq">';

    // Testuari formatua emango diogu akatsak ekiditzeko.
    $corto->testu = str_replace("'", "\'", $testu);
    $corto->testu = str_replace("--", "\-", $corto->testu);


    if ($corto->save_new_corto()) {
        echo '<h3>'._('Se ha enviado tu propuesta, ¡gracias!').'</h3>';
        echo '<h3>'._('Queda pendiente de aprobación por parte de la administración').'</h3><br/>';
        $corto->get_single();
        $corto->print_short_info();
    } else
    echo '<h3>'._('Perdone las molestias: ha habido un error, inténtelo más tarde').'</h3>';
    echo '</div>';

}
function akatsa($message) {
    echo '<div class="form-error">';
    echo "<p>$message</p>";
    echo "</div>\n";
}

function carga_submit_cortos() {
    echo '<h2>'._('Propuesta de un nuevo corto: etapa 1 de 2').'</h2>';
    echo '<div class="faq">';
    echo '<h3>'._('Te comentamos de qué va, y cómo tiene que ser:').'</h3>';
    echo '<ul class="instruction-list">';
    echo '<li><strong>'._('algo corto').':</strong> '._('no es un libro, con una frase basta (o dos-tres como mucho)').'</li>';
    echo '<li><strong>'._('no seas cutre').':</strong> '._('gracias, pero todos conocemos el c&amp;p').'</li>';
    echo '<li><strong>'._('no insultes a otros users').':</strong> '._('¡eh tío!').'</li>';
    echo '<li><strong>'._('no nos interesa lo que haga tu vecino').':</strong> '._('si no es algo disparatado, claro está').'</li>';
    echo '<br/><li>Una vez dicho eso, ¡escríbenos tu propuesta!</li></ul></div>';

    echo '<div class="genericform">';
    echo '<h4>mándanos tu corto ;-)</h4><form class="fondo-caja" action="nuevo_corto.php" method="post" id="bidali" name="bidali">';
    echo '<fieldset>';
    echo '<p><label for="testua">'._('texto').':</label><br />';
    echo '<input type="text" name="testua" id="testua" class="form-full" /></p>';
    ts_print_form();
    echo '<input type="hidden" name="phase" value="1" />';
    echo '<input type="hidden" name="id" value="c_1" />';
    echo '<p><input class="button" type="submit" value="'._('enviar corto »').'" ';
    echo '/>&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span></p>';
    echo '</fieldset></form>';
    echo '</div>';
}