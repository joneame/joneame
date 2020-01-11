<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// David Martí <neikokz at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

// Sistema de ayuda de Jonéame. La idea chachipiruli sería leer los textos de ayuda de
// un txt. Pero de todos modos esto tampoco tiene mucho sentido, ya que subir un php y
// un txt supone prácticamente el mismo esfuerzo :-)
// Todo esto está aquí como reemplazo de la abandonada wiki.

include('config.php');
include(mnminclude.'html1.php');

do_header(_('Ayuda | Jonéame'));

ayuda_tabs();

echo '<div class="ayuda-contenido">';

if ($_REQUEST['id'] == 'faq') {
    Haanga::Load('help_faq.html');
} elseif ($_REQUEST['id'] == 'emoticonos') {
    $smileys_text = [':) :-)', ';) ;-)', ':> :->', ':D :-D :grin:', '<:( <:-( :oops:', ':O :-O',
                     '>:(', '?( ?:( ?:-(', ':S :-S', '8) 8-) 8D 8-D :cool:', ':roll:', ":'( :'-( :cry:",
                     ':x :-x', ':/ :-/', ':* :-*', 'xD x-D :lol:', ':| :-|', ':ffu:',
                     ':8: (8)', ':roto2:', ':gaydude:', ':palm:', ':goat: :goatse:', 'o_o :wow:',
                     '¬¬ :shame:', ':sisi1:', ':nusenuse:', ':P :-P', ':awesome:', ':alone:',
                     ':trollface:', ':troll:', ':yeah: :fuckyeah:', ':clint:', ':yaoface:',
                     ':longcat:', ':cejas:', ':sisi3:',];
    $smileys = [];
    foreach ($smileys_text as $smiley_text) {
        $smileys += [$smiley_text => put_smileys(normalize_smileys(explode(' ', $smiley_text)[0]))];
    }
    $vars = compact('smileys');
    Haanga::Load('help_smileys.html', $vars);
} elseif ($_REQUEST['id'] == 'legal') {
    Haanga::Load('help_legal.html');
} elseif ($_REQUEST['id'] == 'uso') {
    Haanga::Load('help_tos.html');
} else if ($_REQUEST['id'] == 'ignores') {
    Haanga::Load('help_ignore.html');
} else if ($_REQUEST['id'] == 'cotillona') {
    Haanga::Load('help_sneak.html');
} else if ($_REQUEST['id'] == 'privados') {
    Haanga::Load('help_pm.html');
} else {
    Haanga::Load('help_misc.html');
}

echo '</div>';

do_footer();

function ayuda_tabs($tab_selected = false) {
    global $globals;
    $active = ' class="current"';
    echo '<ul class="tabhoriz">';

    if (!empty($_SERVER['QUERY_STRING']))
        $query = "?".htmlentities($_SERVER['QUERY_STRING']);

    $tabs = array(
                '¿Qué es Jonéame?' => 'joneame',
                'FAQ' => 'faq',
                // 'Ignores' => 'ignores',
                'Emoticonos' => 'emoticonos',
                // 'Login' => 'login',
                'Cotillona' => 'cotillona',
                'Mensajes privados' => 'privados',
                'Condiciones legales' => 'legal',
                'Condiciones de uso' => 'uso',
            );

    foreach ($tabs as $name => $tab) {
        if ($tab_selected == $tab) {
            echo '<li'.$active.'><a href="'.$globals['base_url'].'ayuda.php?id='.$tab.'" title="'.$reload_text.'">'._($name).'</a></li>';
        } else {
            echo '<li><a href="'.$globals['base_url'].'ayuda.php?id='.$tab.'">'._($name).'</a></li>';
        }
    }
    echo '</ul>';
}
