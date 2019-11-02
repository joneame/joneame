<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
include(mnminclude.'tags.php');

force_authentication();

do_header(_("editar historia"), "post");


echo '<div id="singlewrap">'."\n";
echo '<div class="genericform">'."\n";

if (!empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $link_id = intval($_REQUEST['id']);
    $linkres = Link::from_db($link_id);
    if (!$linkres->is_editable() || intval($_GET['user'] != $current_user->user_id)) {
        echo '<div class="form-error-submit">&nbsp;&nbsp;'._("historia no modificable").'</div>'."\n";
        return;
    }
    if ($_POST['phase'] == "1") {
        do_save();
        fork("backend/send_pingbacks.php?id=$linkres->id");
    } else {
        do_edit();
    }
} else {
    echo '<div class="form-error-submit">&nbsp;&nbsp;'._("¿duh?").'</div>';
}

echo "</div>";
echo "</div>"."\n";

do_footer();

function do_edit() {
    global $linkres, $current_user;

    $link_title = trim($linkres->title);
    $link_content = trim($linkres->content);
    $link_tags = htmlspecialchars(trim($linkres->tags));
    $link_url = $linkres->url;

    echo '<div class="genericform">'."\n";


    echo '<h4>'._('editar historia').'</h4>'."\n";
    echo '<form class="fondo-caja" action="editlink.php?user='.$current_user->user_id.'" method="post" id="thisform" name="thisform">'."\n";
    echo '<fieldset>';

    $now = time();
    echo '<input type="hidden" name="key" value="'.md5($now.$linkres->randkey).'" />'."\n";
    echo '<input type="hidden" name="timestamp" value="'.$now.'" />'."\n";
    echo '<input type="hidden" name="phase" value="1" />'."\n";
    echo '<input type="hidden" name="id" value="'.$linkres->id.'" />'."\n";

    echo "\n";


    if($current_user->admin) {
        echo '<p><label for="url">'._('url de la noticia').':</label>'."\n";
        echo '<br/><input type="url" id="url" name="url" value="'.htmlspecialchars($link_url).'" size="80" />';
        echo '</p>'."\n";
    }

    echo '<label for="title" accesskey="2">'._('título de la historia').':</label>'."\n";
    echo '<span class="note">máximo de 120 caracteres</span>';

    // Is it an image or video?
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    $linkres->print_content_type_buttons($link_title);

    echo '<br/><input type="text" id="title" name="title" value="'.$link_title.'" size="80" maxlength="120" />';

    // Allow to change the status
    if (($linkres->status != 'published' || ($current_user->admin)) &&
            (( !$linkres->is_discarded() && $current_user->user_id == $linkres->author)
                    || $current_user->admin)) {
        echo '&nbsp;';
        echo '<select name="status" id="status">';

        // Current status
        echo '<option value="'.$linkres->status.'" selected="selected">';
        echo $linkres->get_status_text().'</option>';

        // Status options
        if ($linkres->status == 'queued' && !$current_user->admin) { //another if for admin
        echo '<option value="autodiscard">'.$linkres->get_status_text('autodiscard').'</option>';
        if ($linkres->status != 'duplicated')
        echo '<option value="duplicated">'.$linkres->get_status_text('duplicated').'</option>';

        } elseif ($current_user->admin) {

        echo '<option value="abuse">'.$linkres->get_status_text('abuse').'</option>';
        echo '<option value="autodiscard">'.$linkres->get_status_text('autodiscard').'</option>';
        echo '<option value="queued">'.$linkres->get_status_text('queued').'</option>';
        if ($linkres->status != 'published')
        echo '<option value="published">'.$linkres->get_status_text('published').'</option>';
        if ($linkres->status != 'duplicated')
        echo '<option value="duplicated">'.$linkres->get_status_text('duplicated').'</option>';

        }

        echo '</select>';
    }

    if ($current_user->admin || $current_user->user_id == $linkres->author) {

        if ($linkres->status != 'duplicated') $disabled = 'disabled="true"';
        else $disabled = '';

        echo '<br/><script>';
        echo '$(document).ready(function() {';
        echo '    $("#status").change(function() {';
        echo '        status = $("#status").val();';
        echo '        if (status == "duplicated") { $("#duplicada").attr("disabled", false);  }';
        echo '        else { $("#duplicada").attr("disabled", true);}';
        echo '    });';
        echo '});';
        echo '</script>';

    }

    echo '</p>'."\n";

    echo '<label for="tags" accesskey="3">'._('etiquetas').':</label>'."\n";
    echo '<p><span class="note">'._('añade etiquetas para facilitar la posterior búsqueda').'</span>'."\n";
    echo '<br/><input type="text" id="tags" name="tags" value="'.$link_tags.'" size="70" maxlength="70" /></p>'."\n";

    echo '<div style="float: right;">';
    print_simpleformat_buttons('bodytext');
    echo '</div>';

    echo '<p><label for="bodytext" accesskey="4">'._('descripción de la historia').':</label>'."\n";
    echo '<br /><span class="note">'._('describe el enlace con tus palabras — este campo es opcional').'</span>'."\n";

    echo '</span>'."\n";
    echo '<br/><textarea name="bodytext" rows="10" cols="60" id="bodytext" onKeyDown="textCounter(document.thisform.bodytext,document.thisform.bodycounter,5000)" onKeyUp="textCounter(document.thisform.bodytext,document.thisform.bodycounter,5000)">'.$link_content.'</textarea>'."\n";
    $body_left = 5000 - mb_strlen(html_entity_decode($link_content, ENT_COMPAT, 'UTF-8'), 'UTF-8');
    echo '<input readonly type="text" name="bodycounter" size="3" maxlength="3" value="'. $body_left . '" /> <span class="note">' . _('caracteres libres') . '</span>';
    echo '</p>'."\n";


    //bloquear comentarios y votos
    if($current_user->admin) {
    echo '<fieldset class="redondo">'."\n";
    if ($linkres->votos_permitidos)
        echo '<input type="checkbox" checked="checked" name="votes" value="1" style="margin: 0" id="votospermitidos"/>';
    else
        echo '<input type="checkbox" name="votes" value="1" style="margin: 0" id="votospermitidos"/>';
    echo '<label for="votospermitidos">&nbsp;'._('votos permitidos').'</label>';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;';
    if ($linkres->comentarios_permitidos)
        echo '<input type="checkbox" checked="checked" name="comentarios" value="1" style="margin: 0" id="comentariospermitidos"/>';
    else
        echo '<input type="checkbox" name="comentarios" value="1" id="comentariospermitidos"/>';
    echo '<label for="comentariospermitidos">&nbsp;'._('comentarios permitidos').'</label>'."\n";
    echo '</fieldset>';
    }

  echo '<br/>';

  print_categories_form($linkres->category);

  if ($current_user->admin) {
    echo '<br/>';

   if ($linkres->has_thumb()) {
            echo '<input type="checkbox" name="thumb_delete" value="1" id="thumb_delete"/><label for="thumb_delete">'._('eliminar imagen').'</label><br/>';
   } else {
            echo '<input type="checkbox" name="thumb_get" value="1" id="thumb_get"/><label for="thumb_get">'._('obtener imagen (puede tardar varios segundos)').'</label><br/>';
    }

   if ($linkres->broken_link > 0) {
            echo '<input type="checkbox" name="no_hay_alternativas" value="1" id="tno_hay_alternativas"/><label for="no_hay_alternativas">'._('no hay enlace alternativo').'</label><br/>';
   }

    }





    echo '<br/><input class="button" type="submit" value="'._('editar »').'" />'."\n";
    echo '</fieldset>'."\n";
    echo '</form>'."\n";
    echo '</div>'."\n";
}

function do_save() {
    global $linkres, $dblang, $current_user, $globals, $db;

    $linkres->read_content_type_buttons($_POST['type']);

    $linkres->category=intval($_POST['category']);

    if ($current_user->admin) {

         if (!empty($_POST['url'])){
            if ($_POST['url'] != $linkres->url || $_POST['no_hay_alternativas'] == 1) $linkres->broken_link = 0; // Un admin corrije el enlace
            $linkres->url = clean_input_url($_POST['url']);
         }

         if ($_POST['thumb_delete']) {
            $linkres->thumb = '';
            $linkres->thumb_status = 'deleted';
            $linkres->thumb_x = 0;
            $linkres->thumb_y = 0;
            $linkres->store_thumb();
        }

             if ($_POST['thumb_get']) $linkres->get_thumb();

         $linkres->votos_permitidos = $db->escape($_POST['votes']);
         $linkres->comentarios_permitidos = $db->escape($_POST['comentarios']);

     }

     $titulua = clean_text($_POST['title'], 40);

     // Metemos el titulo original en una variable y segun el NSFW y +18 cambiamos el titulo o no.
     $quitar = 0;

     if ($_POST['sec']) $zer = $_POST['sec'];

     if ($zer['0'] && ( stripos($titulua, '[NSFW]') == FALSE)){
        $gehitu .= " [NSFW]";
        $quitar = 1;
     }

     if ($zer['1'] && ( stripos($titulua, '[+18]') == FALSE)) {
        $gehitu .= " [+18]";
        $quitar = 1;
     }

     if ($quitar == 1) $linkres->title = $titulua.$gehitu;
     if (!$zer['0']) $_POST['title'] = Str_Replace("[NSFW]", "", $_POST['title']);
     if (!$zer['1']) $_POST['title'] = Str_Replace("[+18]", "", $_POST['title']);
     if ($quitar != 1) $linkres->title  = $_POST['title'];

     $linkres->content = clean_text($_POST['bodytext']);

     $linkres->tags = tags_normalize_string($_POST['tags']);

     $new_status = $_POST['status'];

     // change the status
     if ($current_user->admin && $new_status == 'published' && $linkres->date == $linkres->sent_date )                   $insert_publish_log = true;


     if (status_change_allowed($new_status)) {

        if (!$linkres->is_discarded() && ($new_status == 'discard' || $new_status == 'abuse' || $new_status == 'autodiscard' || $new_status == 'duplicated')) {
            // Insert a log entry if the link has been manually discarded
            $insert_discard_log = true;
        }
        $linkres->status = $new_status;
     }

    if ($_POST['duplicated'] && $linkres->status == 'duplicated' && $_POST['duplicated'] != 'http://joneame.net/historia/blablabla'){
          $url = clean_input_url($_POST['duplicated']);
      $url = preg_replace('/#[^\/]*$/', '', $url); // Remove the "#", people just abuse
          $url = preg_replace('/^http:\/\/http:\/\//', 'http://', $url); // Some users forget to delete the http://

          if (! preg_match('/^\w{3,6}:\/\//', $url)) { // http:// forgotten, add it
                      $url = 'http://'.$url;
          }

    require_once(mnminclude.'dupe.class.php');
    $dupe = new Dupe;
    $dupe->id = $linkres->id;
    $dupe->duplicated = $url;

    if (!$dupe->get()) $dupe->insert_duplicated_url();
    else          $dupe->edit_link();


     }

    if ($insert_publish_log) $linkres->date = $globals['now'];

    if (!link_edit_errors($linkres)) {

        if (empty($linkres->uri)) $linkres->get_uri();

    /* Está enviada */
    $linkres->sent = 1;

        $linkres->store();
        tags_insert_string($linkres->id, $dblang, $linkres->tags, $linkres->date);

        // Insert edit log
        require_once(mnminclude.'log.php');

        if ($insert_discard_log) {

            log_insert('link_discard', $linkres->id, $current_user->user_id);

            if ($linkres->author == $current_user->user_id && !$insert_publish_log)
                log_insert('link_edit', $linkres->id, $linkres->author);

        } else if ($linkres->sent && !$insert_publish_log) {
            log_conditional_insert('link_edit', $linkres->id, $current_user->user_id, 60);
        } else if ($insert_publish_log)   {
     log_insert('link_publish', $linkres->id, $linkres->author); //insertar log de publicacion manual

    }
        echo '<div class="form-error-submit">&nbsp;&nbsp;'._("historia actualizada").'</div>'."\n";
    }

    $linkres = Link::from_db($linkres->id);

    echo '<div class="news-body formnotice">'."\n";
    $linkres->print_summary('preview');
    echo '</div>'."\n";

    echo '<form class="note" method="GET" action="historia.php" >';
    echo '<input type="hidden" name="id" value="'.$linkres->id.'" />'."\n";
    echo '<input class="button" type="button" onclick="window.history.go(-1)" value="'._('« modificar').'">&nbsp;&nbsp;'."\n";;
    echo '<input class="button" type="submit" value="'._('ir a la historia').'" />'."\n";
    echo '</form>'. "\n";
}

function status_change_allowed($new_status) {
    global $current_user, $linkres;

    $allowed = false;

    switch ($new_status) {
        case ('abuse'):
            if ($current_user->admin)
            $allowed =   true;
        case ('discard'):
            if ($current_user->admin)
            $allowed =   true;
        case ('queued'):
            if ($current_user->admin)
            $allowed =   true;
        case ('published'):
            if ($current_user->admin)
            $allowed =   true;
        case ('duplicated'):
            if ($current_user->admin)
            $allowed =   true;
        case ('autodiscard'):
            if (($linkres->author == $current_user->user_id && !$linkres->is_discarded()) || $current_user->admin)
            $allowed = true;

    }

    return $allowed;

}

function link_edit_errors($linkres) {
    global $current_user, $globals;

    $error = false;

    // only checks if the user is not special or god
    if(!$linkres->check_url($linkres->url, false) && !$current_user->admin) {
        echo '<div class="form-error-submit">&nbsp;&nbsp;'._('url incorrecto').'</div>';
        $error = true;
    }

    if($_POST['key'] !== md5($_POST['timestamp'].$linkres->randkey)) {
        echo '<div class="form-error-submit">&nbsp;&nbsp;'._('Clave incorrecta').'</div>';
        $error = true;
    }

    if(time() - $_POST['timestamp'] > 900) {
        echo '<div class="form-error-submit">&nbsp;&nbsp;'._('Tiempo excedido').'</div>';
        $error = true;
    }

    if(strlen($linkres->title) < 4) {
        echo '<div class="form-error-submit">&nbsp;&nbsp;'._("Título incompleto").'</div>';
    $error = true;
    }

    if(strlen($linkres->content) < 6 && !$globals['permitir_sin_entradilla']) {
        echo '<div class="form-error-submit">&nbsp;&nbsp;'._("Entradilla incompleta").'</div>';
    $error = true;
    }

    if(mb_strlen(html_entity_decode($linkres->title, ENT_COMPAT, 'UTF-8'), 'UTF-8') > 120  || mb_strlen(html_entity_decode($linkres->content, ENT_COMPAT, 'UTF-8'), 'UTF-8') > 5000 ) {
        echo '<div class="form-error-submit">&nbsp;&nbsp;'._("Título o entradilla demasiado largos").'</div>';
        $error = true;
    }

    if(strlen($linkres->tags) < 3 ) {
        echo '<div class="form-error-submit">&nbsp;&nbsp;'._("No has puesto etiquetas").'</div>';
        $error = true;
    }

    if(preg_match('/.*http:\//', $linkres->title)) {
        echo '<div class="form-error-submit">&nbsp;&nbsp;'._("Por favor, no pongas URLs en el título, no ofrece información").'</div>';
        $error = true;
    }

    if(!$linkres->category > 0) {
        echo '<div class="form-error-submit">&nbsp;&nbsp;'._("Categoría no seleccionada").'</div>';
        $error = true;
    }

    return $error;
}