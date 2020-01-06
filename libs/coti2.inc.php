<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

// Funciones generales para cotillona v1.2

// Bana jaso bada prozesatu.
function ezabatu_uid_tablan($id, $tabla) {
    global $db;
    $sqldel = "DELETE FROM $tabla WHERE uid='".$db->escape($id)."'";
    if($db->query($sqldel)) return true;
    return false;
}

 // Baneatuta dago X erabiltzailea? true->bai false-> ez
 // Está baneado el usuario X? true->si false-> no
function baneatuta($id) {
    global $db;

    if ($db->query("SELECT * FROM fisban WHERE uid = '".$db->escape($id)."' and vigente=1")) return true;
    else return false;
}

/* Devuelve la razón del ban de un usuario */
function razon_ban($id){
    global $db;
    return clean_text($db->get_var("SELECT razon FROM fisban WHERE uid = '".$db->escape($id)."' and vigente=1"));
}

// Existitzen al da arrayan X erabiltzailea?
// Existe el usuario X en el array?
function existitzen_da_arrayan($array, $nor) {

    foreach ($array as $id => $zer)
    if ($zer == $nor) return true;

    return false;
}

/* Lee información básica de un usuario */
function read($id){
    global $db;

    if($result = $db->get_row("SELECT user_id as id, user_level as level, user_login as username FROM users WHERE user_id=$id LIMIT 1")) {

            foreach(get_object_vars($result) as $var => $value) $array[$var] = $value;

            if ($array['level'] == 'admin' || $array['level'] == 'god')
                $array['admin'] = true;
            else
                $array['admin'] = false;


            //$array['read'] = true;
            return $array;
        }
        return false;
}

/* Unban de la cotillona */
function unban($id) {
    global $db;

    if ($db->query("UPDATE fisban SET vigente=0 WHERE uid=".$db->escape($id))) return true;
    else return false;
}

/* Formulario para introducir el ban */
function print_razon_edit($id) {
    global $current_user, $site_key;

    $razon_baneo = "Has sido baneado de la cotillona de Jonéame. Si piensas que hubo algún error, comunícanoslo a través de un email a admin@joneame.net";

    echo '<div class="redondo atencion">Acompañado de la razón exacta, es un posible comienzo para la razón: <b>'. $razon_baneo. '</b></div><br/>';
        echo '<div class="genericform"><div class="genericform">'."\n";
    echo '<span style="color: red;">'._('mínimo 15 caracteres').'</span><br/><br/>';
        echo '<div class="commentform">'."\n";
        echo '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="post">'."\n";
        echo '<h4>'._('inserta la razón del ban').'</h4>'."\n";
        echo '<fieldset class="fondo-caja">';
        echo '<div class="fondo-caja"><textarea name="razon_ban" id="insertar" rows="3" style="width: 99%;"></textarea></div>'."\n";
        echo '<input class="button" type="submit" name="submit" value="'._('banear').'" />'."\n";
        echo '<input type="hidden" name="user_id" value="'.$id.'" />'."\n";
        echo '</fieldset>'."\n";
        echo '</form>'."\n";
        echo "</div></div></div>\n";

}

/* Insertar cotiban con su log */
function cotiban_log_insert($log_name, $razon, $ban, $ref_id) {
    global $db, $globals, $current_user;

    $ip = $globals['user_ip'];

    $user_id = $current_user->user_id;

    /* ¿Está activo el ban? */
    if ($ban > 0) $ban = 1;
    else $ban = 0;

    intval($ban);

    return $db->query("insert into fisban (log_name, razon, por, uid, vigente, date, ip) values ('$log_name', '$razon', $user_id, $ref_id, $ban, now(), '$ip')");
}