<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'user.php');

// We need it because we modify headers
ob_start();

$user_levels = array ('disabled', 'normal', 'special', 'devel', 'admin', 'god');
$user_sexs = array ('A ti que te importa','Hetero','Gay','Lesbi','Bisepsuá');

// User recovering her password
if (!empty($_GET['login']) && !empty($_GET['t']) && !empty($_GET['k'])) {
    $time = intval($_GET['t']);
    $key = $_GET['k'];
    $user=new User();
    $user->username=clean_input_string($_GET['login']);
    if($user->read()) {
        $now = time();
        $key2 = md5($user->id.$user->pass.$time.$site_key.get_server_name());
        if ($time > $now - 7200 && $time < $now && $key == $key2) {
            $db->query("update users set user_validated_date = now() where user_id = $user->id and user_validated_date is null");
            $current_user->Authenticate($user->username, $user->pass, false);
            header('Location: http://'.get_server_name().$globals['base_url'].'profile.php');
            die;
        }
    }
}
//// End recovery
if ($current_user->user_id > 0 && $current_user->authenticated && empty($_REQUEST['login'])) {
        $login=$current_user->user_login;
} elseif (!empty($_REQUEST['login']) && $current_user->user_level == 'god') {
    $login=$db->escape($_REQUEST['login']);
    $admin_mode = true;
} else {
    header("Location: ./login.php");
    die;
}
$user=new User();
$user->username = $login;
if(!$user->read()) {
    do_error('No se ha encontrado', 404);
}
$globals['ads'] = true;
$save_messages = save_profile();
do_header(_('edición del perfil del usuario'). ': ' . $user->username);
echo $save_messages; // We do it later because teh profile could change header's info
show_profile();
do_footer();
function show_profile() {
    global $user, $admin_mode, $user_levels, $globals, $site_key, $current_user, $user_sexs, $db;
    echo '<div class="genericform" style="margin: 0 50px">';
    echo '<form enctype="multipart/form-data" action="profile.php" method="post" id="thisform" AUTOCOMPLETE="off">';
    echo '<h4>'._('opciones del usuario') . " <a href='".get_user_uri($user->username)."'>$user->username</a>: $user->level</h4>";
    echo '<div class="fondo-caja">';
    echo '<fieldset>';
    echo '<div class="profile-sidebar">';
    echo '<img class="espaciador" style="padding: 0;" src="'.get_avatar_url($user->id, $user->avatar, 80).'" width="80" height="80" alt="'.$user->username.'" title="avatar" /><br/>';
    echo '<div class="help-topic">'._('Puedes poner tu dirección de <b>Jabber</b> o <b>Google Talk</b> si quieres conectarte a la cotillona o mandar notitas desde allí. Los contactos que debes agregar son:<br/><b>cotillona@joneame.net</b><br/><b>notitas@joneame.net</b>').'</div>';

    include_once(mnminclude.'avatars.php');
        if (is_avatars_enabled()) echo '<div class="help-topic">'._('Los avatares no deben ser mayores de 100KB, y sólo se aceptan formatos jpg, gif o png, sin transparencias.').'</div>';
    echo '</div>';
    echo '<input type="hidden" name="process" value="1" />';
    echo '<input type="hidden" name="user_id" value="'.$user->id.'" />';
    echo '<input type="hidden" name="form_hash" value="'. md5($site_key.$user->id.$globals['user_ip']) .'" />';
    if ($admin_mode)
        echo '<input type="hidden" name="login" value="'.$user->username.'" />';
    echo '<ul class="edicion-perfil">';
    echo '<li>';
    echo '<dt>'._('usuario').'</dt>';
    echo '<dd><input type="text" autocomplete="off" name="username" id="username" value="'.$user->username.'" onkeyup="enablebutton(this.form.checkbutton1, null, this)" />';
    echo '&nbsp;&nbsp;<span id="checkit"><input type="button" class="button" id="checkbutton1" disabled="disabled" value="'._('verificar').'" onclick="checkfield(\'username\', this.form, this.form.username)"/></span>';
    echo '&nbsp;<span id="usernamecheckitvalue"></span>';
    echo '</dd>';
        echo '</li>';
    if($current_user->user_level == 'god')
        echo '<span class="note">'._('eres god. esto te da derecho a ponerte o poner a alguien un nombre de menos de 3 caracteres, o con caracteres especiales. ten en cuenta que dando mal uso a esto último, podrías conseguir que la persona a la que editas el perfil no pueda volver a iniciar sesión o cosas mucho peores, ').'<span style="color: red;">'._('¡ten muchísimo cuidado: la puedes cagar pero bien!').'</span></span><br/>';

    echo '<li>';
    echo '<dt>'._('nombre real').'</dt>';
    echo '<dd><input type="text" autocomplete="off" name="names" id="names" value="'.$user->names.'" /></dd>';
    echo '</li>';
    if ($user->id == $current_user->user_id) {
        echo '<li>';
        echo '<dt>'._('estado').'</dt>';
        echo '<dd><input maxlength="60" type="text" autocomplete="off" name="estado" id="estado" value="'.$user->estado.'" /></dd>';
        echo '</li>';
    }


    echo '<li>';

    echo '<dt>'._('correo electrónico').'</dt>';
    echo '<dd><input type="text" autocomplete="off" name="email" id="email" value="'.$user->email.'" onkeyup="enablebutton(this.form.checkbutton2, null, this)"/>';
    echo '&nbsp;&nbsp;<input type="button" class="button" id="checkbutton2" disabled="disabled" value="'._('verificar').'" onclick="checkfield(\'email\', this.form, this.form.email)"/>';
    echo '&nbsp;<span id="emailcheckitvalue"></span></dd>';
    echo '</li>';
    echo '<li>';
    echo '<dt>'._('página web').'</dt>';
    echo '<dd><input type="text" autocomplete="off" name="url" id="url" value="'.$user->url.'" /></dd>';
    echo '</li>';


    echo '<li>';
    echo '<dt>'._('jabber/gtalk para la coti').'</dt>';
    echo '<dd><input type="text" autocomplete="off" name="public_info" id="public_info" value="'.$user->public_info.'" /></dd>';
    echo '</li>';


    /*if ($user->id  == $current_user->user_id || $current_user->admin) {
        echo '<li>';
        echo '<dt>' . _("elige tu sexualidad") . ' </dt>' . "\n";
        echo '<dd><select name="user_sex">';
        foreach ($user_sexs as $sex) {
            echo '<option value="'.$sex.'"';
            if ($user->user_sex == $sex) echo ' selected="selected"';
            echo '>'.$sex.'</option>';
        }
        echo '</select></dd>';
        echo '</li>';
    }
    if ($current_user->admin) {
        echo '<li>';
        echo '<dt>' . _("cumpleaños") . ' </dt>' . "\n";
        echo '<dd><select name="dia">';
        $partes = explode (',', $user->birth);
        $cuenta = 1;

        while ( $cuenta <= 31) {
            echo '<option value="'.$cuenta.'"';
            if ($partes[0] == $cuenta) echo ' selected="selected"';
            echo '>'.$cuenta.'</option>';
            $cuenta ++;
        }

        echo '</select> de ';
        echo '<select name="mes">';
        $cuenta = 1;
        while ( $cuenta <= 12) {
            echo '<option value="'.$cuenta.'"';
            if ($partes[1] == $cuenta) echo ' selected="selected"';
            echo '>'.get_month($cuenta).'</option>';
            $cuenta ++;
        }

        echo '</select></dd>';
        echo '</li>';
    }*/
    if (is_avatars_enabled()) {
        echo '<li>';
        echo '<input type="hidden" name="MAX_FILE_SIZE" value="300000" />';
        echo '<dt>'._('elige tu avatar').'</dt>';
        echo '<dd><input type="file" class="button" autocomplete="off" name="image" /></dd>';
        echo '</li>';
    }


    echo '<span class="note">'._('introduce aquí una nueva contraseña para cambiarla -no se cambiará si la dejas en blanco-:').'</span><br/>';


    echo '<li>';

    echo '<dt>' . _("clave") . '<dt/>';
    echo '<dd><input type="password" autocomplete="off" id="password" name="password" size="25" onkeyup="return securePasswordCheck(this.form.password);"/></p></dd>';
    echo '</li>';


    echo '<li>';
    echo '<dt>' . _("repite la clave") . '<dt/>';

echo '<dd><input type="password" autocomplete="off" id="verify" name="password2" size="25" onkeyup="checkEqualFields(this.form.password2, this.form.password)"/></p></dd>';
    echo '</li>';

          if ($user->thumb == 1) $checked = 'checked=checked';
          else $checked = '';

           echo '<li>';
        print_checkbox('thumbs', $user->thumb);
    echo '&nbsp;<span>mostrar thumbnails (imágenes que acompañan a los joneos)</span>';
    echo '</li>';


    if ($admin_mode) {
        echo '&nbsp;<br/><span class="note" style="margin-top: 10px;">'._('opciones para administradores (¡cuidado!):').'</span><br/>';

        echo '<li><dt>' . _("estado") . '</dt>' . "\n";
        echo '<dd><select name="user_level">';
        foreach ($user_levels as $level) {
            echo '<option value="'.$level.'"';
            if ($user->level == $level) echo ' selected="selected"';
            echo '>'.$level.'</option>';
        }
        echo '</select></dd></li>';
        /* Little info */
        echo '&nbsp;<br/>'._('carisma mínimo para enviar notas: ').$globals['min_karma_for_posts'].'';
        echo '&nbsp;<br/>'._('carisma mínimo para votar comentarios: ').$globals['min_karma_for_comment_votes'].'';
        echo '&nbsp;<br/>'._('carisma mínimo para votar negativo: ').$globals['min_karma_for_negatives'].'';
        echo '&nbsp;<br/>'._('carisma mínimo para escribir comentarios: ').$globals['min_karma_for_comments'].'';
        echo '&nbsp;<br/>'._('carisma mínimo para escribir en la cotillona: ').$globals['min_karma_for_sneaker'].'<br/><br/>';
        /* Change user carisma manually */
        echo '<li><dt>'._('carisma').'</dt>';
        echo '<dd><input type="text" autocomplete="off" name="karma" id="karma" value="'.$user->karma.'" /></dd>';
        echo '</li>';
    }

    $save_text = ($user->id == $current_user->user_id) ? _('actualizar mis datos') : _('actualizar sus datos');
    echo '<li><input type="submit" name="save_profile" value="'.$save_text.'" class="button" /></li>';

    echo '</fieldset></div>';


    // Disable the account
    if ($user->id == $current_user->user_id) {
        echo '<br/><div class="fondo-caja"><h4>'._('darse de baja') . '</h4><fieldset>';
        echo '<ul class="edicion-perfil">';
        echo '<li><span class="note" style="font-size: 110%;">'._('¡atención! tu cuenta será dada de baja.').'</span></li>';
        echo '<li><span class="note">'._('se eliminarán automáticamente todos tus datos personales. tus notitas, envíos y comentarios NO se borrarán. no podrás volver a iniciar sesión con esta cuenta.').'</span></li>';
        echo '<li style="margin-top: 10px"><input name="disable" type="checkbox" value="1"/>&nbsp;'._('lo he entendido y quiero darme de baja').'</li>';
        echo '<li><input type="submit" name="disabledme" value="'._('adiós, mundo cruel').'" class="button" /></li>';
        echo '</fieldset></div>';
    }
    echo "</ul></form></div>\n";
}


function save_profile() {
    global $db, $user, $current_user, $globals, $admin_mode, $site_key;

    $errors = 0;
    $pass_changed=false;
    $messages = '';
    $form_hash = md5($site_key.$user->id.$globals['user_ip']);
    if(isset($_POST['disabledme']) && intval($_POST['disable']) == 1 && $_POST['form_hash'] == $form_hash && $_POST['user_id'] == $current_user->user_id ) {


        $old_user_login = $user->username;
        $old_user_id = $user->id;
        $user->disable();
        require_once(mnminclude.'log.php');
        log_insert('user_delete', $old_user_id, $old_user_id );
        syslog(LOG_NOTICE, "Joneame, disabling $old_user_id ($old_user_login) by $current_user->user_login -> $user->username ");

        $current_user->Logout(get_user_uri($user->username));
        die;
    }
    if(!isset($_POST['save_profile']) || !isset($_POST['process']) ||
        ($_POST['user_id'] != $current_user->user_id && !$admin_mode) ) return;
    if ( empty($_POST['form_hash']) || $_POST['form_hash'] != $form_hash ) {
        $messages .= '<p class="form-error-submit-perfil">'._('Falta la clave de control').'</p>';
        $errors++;
    }
    if(!empty($_POST['username']) && trim($_POST['username']) != $user->username) {


        if (((strlen(trim($_POST['username']))<3 && $current_user->user_level != 'god')) || (strlen(trim($_POST['username']))<1 && $current_user->user_level == 'god') ) {
            $messages .= '<p class="form-error-submit-perfil">'._('nombre demasiado corto').'</p>';
            $errors++;
        }
        if(!check_username($_POST['username'])) {
            $messages .= '<p class="form-error-submit-perfil">'._('nombre de usuario erróneo, caracteres no admitidos').'</p>';
            $errors++;

        } elseif (user_exists(trim($_POST['username'])) ) {
            $messages .= '<p class="form-error-submit-perfil">'._('el usuario ya existe').'</p>';
            $errors++;
        } else {
            $user->username=trim($_POST['username']);
        }

    }
    if ($_POST['thumbs'] == 1 ) $user->thumb= 1;
    else $user->thumb= 0;

    if($user->email != trim($_POST['email']) && !check_email(trim($_POST['email']))) {
        $messages .= '<p class="form-error-submit-perfil">'._('el correo electrónico no es correcto').'</p>';
        $errors++;
    } elseif (!$admin_mode && trim($_POST['email']) != $current_user->user_email && email_exists(trim($_POST['email']))) {
        $messages .= '<p class="form-error-submit-perfil">'. _('ya existe otro usuario con esa dirección de correo'). '</p>';
        $errors++;
    } else {
        $user->email=trim($_POST['email']);
    }
    $user->url=htmlspecialchars(clean_input_url($_POST['url']));

       /* if ($_POST['user_sex'])
    $user->sex = $_POST['user_sex'];*/
    // Check IM address
    if (!empty($_POST['public_info'])) {
        $_POST['public_info']  = htmlspecialchars(clean_input_url($_POST['public_info']));
        $public = $db->escape($_POST['public_info']);
        $im_count = intval($db->get_var("select count(*) from users where user_id != $user->id and user_level != 'disabled' and user_public_info='$public'"));


        if ($im_count > 0) {
            $messages .= '<p class="form-error-submit-perfil">'. _('ya hay otro usuario con la misma dirección de MI, no se ha grabado'). '</p>';
            $_POST['public_info'] = '';
            $errors++;
        }
    }
        $user->phone = $_POST['phone'];
        $user->public_info=htmlspecialchars(clean_input_url($_POST['public_info']));
    // End check IM address
    if ($user->id  == $current_user->user_id) {
        // Check phone number
        if (!empty($_POST['phone'])) {
            if ( !preg_match('/^\+[0-9]{9,16}$/', $_POST['phone'])) {
                $messages .= '<p class="form-error-submit-perfil">'. _('número telefónico erróneo, no se ha grabado'). '</p>';
                $_POST['phone'] = '';
                $errors++;
            } else {
                $phone = $db->escape($_POST['phone']);
                $phone_count = intval($db->get_var("select count(*) from users where user_id != $user->id and user_level != 'disabled' and user_phone='$phone'"));
                if ($phone_count > 0) {
                    $messages .= '<p class="form-error-submit-perfil">'. _('ya hay otro usuario con el mismo número, no se ha grabado'). '</p>';
                    $_POST['phone'] = '';
                    $errors++;
                }
            }
        }


        $user->phone = $_POST['phone'];
// End check phone number
    }


    $user->names=clean_text($_POST['names']);
    if ($_POST['estado'] != $user->estado){
        $user->estado =clean_text($_POST['estado']);
        $existe = $db->get_var("SELECT user_id FROM user_new_status WHERE user_id=$user->id");
        if ($existe)
            $db->query("UPDATE user_new_status SET user_new_status_date=now() WHERE user_id=$user->id");
        else $db->query("INSERT INTO user_new_status (user_id) VALUES ($user->id)");
    }
    if(!empty($_POST['password']) || !empty($_POST['password2'])) {
        if(! check_password($_POST["password"]) ) {
            $messages .= '<p class="form-error-submit-perfil">'._('Clave demasiado corta, debe ser de 6 o más caracteres e incluir mayúsculas, minúsculas y números').'</p>';
            $errors=1;
        } else if(trim($_POST['password']) !== trim($_POST['password2'])) {
            $messages .= '<p class="form-error-submit-perfil">'._('las claves no son iguales, no se ha modificado').'</p>';
            $errors = 1;
        } else {
            $user->pass=md5(trim($_POST['password']));
            $messages .= '<p  class="form-error-submit-perfil">'._('La clave se ha cambiado').'</p>';
            $pass_changed = true;
        }
    }
    if ($admin_mode && !empty($_POST['user_level']))
        $user->level=$db->escape($_POST['user_level']);
    if ($admin_mode && !empty($_POST['karma']) && is_numeric($_POST['karma']) && $_POST['karma'] > 4 && $_POST['karma'] <= 30) {
        $user->previous_carisma = $user->karma;
        $user->karma=$_POST['karma'];
}
   // $user->birth = $birth = intval($_POST['dia']).','.intval($_POST['mes']);
    // Manage avatars upload
    include_once(mnminclude.'avatars.php');
    if (!empty($_FILES['image']['tmp_name']) ) {
        if(avatars_check_upload_size('image')) {
            $avatar_mtime = avatars_manage_upload($user->id, 'image');
            if (!$avatar_mtime) {
                $messages .= '<p class="form-error-submit-perfil">'._('error guardando la imagen').'</p>';
                $errors = 1;
                $user->avatar = 0;
           } else {
                $user->avatar = $avatar_mtime;
            }
        } else {
            $messages .= '<p class="form-error-submit-perfil">'._('el tamaño de la imagen excede el límite').'</p>';
            $errors = 1;
            $user->avatar = 0;
        }
    }
    if (!$errors) {
        if (empty($user->ip)) {
            $user->ip=$globals['user_ip'];
        }
       $user->store();
        $user->read();
       if (!$admin_mode && ($current_user->user_login != $user->username ||
                    $current_user->user_email != $user->email || $pass_changed)) {
            $current_user->Authenticate($user->username, $user->pass);
        }
        $messages .= '<p class="form-error-submit-perfil">'._('datos actualizados').'</p>';
    }
    return $messages;
}
function print_checkbox($name, $current_value) {
    echo '<input  name="'.$name.'" type="checkbox" value="1"';
    if ($current_value > 0) echo '  checked="true"';
   echo '/>';
}
function get_month($month){
    switch($month){
    case '1': return _('enero');
    case '2': return _('febrero');
    case '3': return _('marzo');
    case '4': return _('abril');
    case '5': return _('mayo');
    case '6': return _('junio');
    case '7': return _('julio');
    case '8': return _('agosto');
    case '9': return _('septiembre');
    case '10': return _('octubre');
    case '11': return _('noviembre');
    case '12': return _('diciembre');
    default: return false;
    }
}