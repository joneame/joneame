<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jon Arano (arano.jon@gmail.com)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'palabra.php');
$page_size = 40;
do_header(_('Diccionario | Jonéame'));

/*** SIDEBAR ****/
//echo '<div id="sidebar">';
//do_banner_right();
//do_best_stories();
//do_best_comments();
//echo '</div>';
/*** END SIDEBAR ***/

echo '<div id="newswrap">';
echo '<h1><a href="diccionario.php" class="titular">Diccionario Jonéame</a></h1>';

// Print answers to the comment
$sql = "SELECT id FROM dictionary ORDER BY fecha LIMIT $page_size";
$definitions = $db->get_results($sql);
if ($definitions) {

    echo '<div style="padding-left: 40px; padding-top: 10px">';
  //  echo '<ol class="comments-list">';
    foreach ($definitions as $definition) {
        $palabra = Palabra::from_db($definition->id);
       // echo '<li>';
        $palabra->print_short_info();
      //  echo '</li>';
    }
    //echo "</ol>\n";
    echo '</div>';
}

echo '<br/><div class="barra redondo">';
if ($current_user->authenticated) {
        echo '<a href="javascript:anadir_definicion()">Añade tu palabra a definir</a>';
} else {

        echo '<a href="'.$globals['base_url'].'login.php?return='.$_SERVER['REQUEST_URI'].'">'._('Entra con tu cuenta de usuario').'</a> '._('si deseas añadir palabras').'. '._('O crea tu cuenta haciendo clic'). ' <a href="'.$globals['base_url'].'register.php">aquí</a>';

}
echo '</div>';
echo '</div>';

do_footer();

/*
function print_comment_form() {
    global $link, $current_user;

    if (!$link->sent) return;

    echo '<div class="commentform">';
    echo '<form action="" method="post">';
    echo '<h4>'._('escribe un comentario').'</h4><fieldset class="fondo-caja">';
    echo '<div style="float: right;">';
    print_simpleformat_buttons('comment');
    echo '</div><span class="note"><strong>'._('¡eh tío!').':</strong> '._('comentarios serios, constructivos, xenófobos, racistas o difamatorios causarán el baneo de la cuenta de usuario y expulsión de la mafia gay').'</span></label>';
    echo '<div style="margin-top: 10px;"><textarea name="comment_content" id="comment" cols="75" rows="12"></textarea></div>';
    echo '<input class="button" type="submit" name="submit" value="'._('enviar comentario').'" />';

    // Allow gods to put "admin" comments which does not allow votes
    if ($current_user->admin ) {

     echo '&nbsp;&nbsp;&nbsp;&nbsp;<input name="type" type="checkbox" value="admin" id="comentario-admin"/>&nbsp;<label for="type">'._('comentario admin').'</strong></label>';
     echo '&nbsp;&nbsp;&nbsp;&nbsp;<input name="especial" type="checkbox" value="1" id="comentario-especial"/>&nbsp;<label for="type">'._('no mostrar mi nick').'</strong></label>';

    }


    echo '<input type="hidden" name="process" value="newcomment" />';
    echo '<input type="hidden" name="randkey" value="'.rand(1000000,100000000).'" />';
    echo '<input type="hidden" name="link_id" value="'.$link->id.'" />';
    echo '<input type="hidden" name="user_id" value="'.$current_user->user_id.'" />';
    echo '</fieldset>';
    echo '</form>';
    echo "</div>\n";

}
*/