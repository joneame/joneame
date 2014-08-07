<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jon Arano (arano.jon@gmail.com)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'user.php');
include(mnminclude.'historial.class.php');

if (!$current_user->admin) do_error("No tienes permiso para entrar aquí", 405);

// We need it because we modify headers
ob_start();

if (!empty($_REQUEST['login'])) {
    $login=$db->escape($_REQUEST['login']);

} else {
    header("Location: ./");
    die;
}
$user=new User();
$user->username = $login;

if(!$user->read()) {
    do_error('No se ha encontrado', 404);
}

if ($user->id == $current_user->user_id) do_error("No puedes hacerte una BSC a ti mismo", 405);

do_header(_('BSC al usuario'). ': ' . $user->username);

if ($_POST['process'] == 1) save_profile();

else show_profile();;



do_footer();

function show_profile() {
    global $user,  $globals, $db;

    echo '<div class="genericform" style="margin: 0 50px">';
    echo '<form enctype="multipart/form-data" action="bsc.php" method="post" id="thisform" AUTOCOMPLETE="off">';
    echo '<h4>'._('BSC al usuario') . " <a href='".get_user_uri($user->username)."'>$user->username</a></h4>";
    echo '<div class="fondo-caja">';
    echo '<fieldset>';

    echo '<div class="profile-sidebar">';
    echo '<img class="espaciador" style="padding: 0;" src="'.get_avatar_url($user->id, $user->avatar, 80).'" width="80" height="80" alt="'.$user->username.'" title="avatar" /><br/>';
    echo '</div>';

    echo '<input type="hidden" name="process" value="1" />';
    echo '<input type="hidden" name="user_id" value="'.$user->id.'" />';
    echo '<input type="hidden" name="login" value="'.$user->username.'" />';
    echo '<ul class="edicion-perfil">';


    /* Little info */
    echo '&nbsp;<br/>'._('carisma mínimo para enviar notas: ').$globals['min_karma_for_posts'].'';
    echo '&nbsp;<br/>'._('carisma mínimo para votar comentarios: ').$globals['min_karma_for_comment_votes'].'';
    echo '&nbsp;<br/>'._('carisma mínimo para votar negativo: ').$globals['min_karma_for_negatives'].'';
    echo '&nbsp;<br/>'._('carisma mínimo para escribir comentarios: ').$globals['min_karma_for_comments'].'';
    echo '&nbsp;<br/>'._('carisma mínimo para escribir en la queer chat: ').$globals['min_karma_for_sneaker'].'<br/><br/>';

    /* Change user carisma manually */
    echo _('razón de la BSC');

    echo ': <input type="text" autocomplete="off" name="texto" id="texto" /> <br/><br/>';

    /* Change user carisma manually */
    echo _('carisma');

    echo ': <input type="text" autocomplete="off" name="carisma" id="carisma" value="'.$user->karma.'" />';


    echo '<li><input type="submit" name="save_profile" value="Bajada súbita de carisma" class="button" /></li>';


    echo '</fieldset></div>';

    echo "</ul></form></div>\n";

}



function save_profile() {
    global $db, $user, $current_user, $globals;


if (!empty($_POST['carisma']) && is_numeric($_POST['carisma']) && $_POST['carisma'] > 4 && $_POST['carisma'] <= 30) {


    $new_carisma = $_POST['carisma'];

    /* Es BSC, no para dar premios */
    if ($new_carisma >= $user->karma) {
     echo '<p class="form-error-submit-perfil">'._('El nuevo carisma no es menor que el antiguo.').'</p>';
     die;
    }

    /* Es BSC, no para dar premios */
    if ($user->level == 'god') {
     echo '<p class="form-error-submit-perfil">'._('No puedes hacer una BSC a un god.').'</p>';
     die;
    }

    $diferencia = $user->karma - $new_carisma;
    $texto = $db->escape($_POST['texto']);

    if (strlen($texto) < 10 || empty($texto)) {
     echo '<p class="form-error-submit-perfil">'._('Texto demasiado corto.').'</p>';
     die;
    }

    $user->previous_carisma = $user->karma;
    $user->karma=$new_carisma;
    $historial = new Historial;
    $historial->who = intval($user->id);
    $historial->texto = 'Bajada Subita de Carisma al usuario de '.$diferencia. ' por '.$texto;

    /* Save historial and user*/

    if ($historial->insert() && $user->store())
    echo '<p class="form-error-submit-perfil">'._('BSC realizada correctamente').'</p>';

    /* Insert log*/
    include(mnminclude.'log.php');
    log_insert('bsc_new', $historial->id, $current_user->user_id);
}


}