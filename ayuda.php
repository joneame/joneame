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
    echo '<h2>Lista de emoticonos</h2>';
    // !!TODO(neiko 11/01/2020): esto hay que generarlo mediante código
    echo '<table>';
    echo '<tr><th>Emoticono</th><th class="vertical">Resultado</th><th>Emoticono</th><th class="vertical">Resultado</th><th>Emoticono</th><th>Resultado</th></tr>';
    echo '<tr><td>:) :-)</td><td class="vertical">'.put_smileys('{smiley}').'</td><td>;) ;-)</td><td class="vertical">'.put_smileys('{wink}').'</td><td>:> :-></td><td>'.put_smileys('{wink}').'</td></tr>';
    echo '<tr><td>:D :-D :grin:</td><td class="vertical">'.put_smileys('{grin}').'</td><td>&lt;:( &lt;:-( :oops:</td><td class="vertical">'.put_smileys('{oops}').'</td><td>:O :-O</td><td>'.put_smileys('{shocked}').'</td></tr>';
    echo '<tr><td>&gt;&#58;(</td><td class="vertical">'.put_smileys('{angry}').'</td><td>?(</td><td class="vertical">'.put_smileys('{huh}').'</td><td>:-S :S</td><td>'.put_smileys('{confused}').'</td></tr>';
    echo '<tr><td>8) 8-) 8D 8-D</td><td class="vertical">'.put_smileys('{cool}').'</td><td>:roll:</td><td class="vertical">'.put_smileys('{roll}').'</td><td>:\'( :\'-( :cry:</td><td>'.put_smileys('{cry}').'</td></tr>';
    echo '<tr><td>:x :-x</td><td class="vertical">'.put_smileys('{lipssealed}').'</td><td>:/ :-/</td><td class="vertical">'.put_smileys('{undecided}').'</td><td>:* :-*</td><td>'.put_smileys('{kiss}').'</td></tr>';
    echo '<tr><td>xD :lol:</td><td class="vertical">'.put_smileys('{lol}').'</td><td>:| :-|</td><td class="vertical">'.put_smileys('{blank}').'</td><td>:ffu:</td><td>'.put_smileys('{ffu}').'</td></tr>';
    echo '<tr><td>:8: (8)</td><td class="vertical">'.put_smileys('{music}').'</td><td>:roto2:</td><td class="vertical">'.put_smileys('{roto}').'</td><td>:gaydude:</td><td>'.put_smileys('{gaydude}').'</td></tr>';
    echo '<tr><td>:palm:</td><td class="vertical">'.put_smileys('{palm}').'</td><td>:goat: :goatse:</td><td class="vertical">'.put_smileys('{goatse}').'</td><td>o_o :wow:</td><td>'.put_smileys('{wow}').'</td></tr>';
    echo '<tr><td>¬¬ :shame:</td><td class="vertical">'.put_smileys('{shame}').'</td><td>:sisi1:</td><td class="vertical">'.put_smileys('{sisi}').'</td><td>:nusenuse:</td><td>'.put_smileys('{nuse}').'</td></tr>';
    echo '<tr><td>:P :-P</td><td class="vertical">'.put_smileys('{tongue}').'</td><td>:awesome:</td><td class="vertical">'.put_smileys('{awesome}').'</td><td>:alone:</td><td>'.put_smileys('{alone}').'</td></tr>';
    echo '<tr><td>:trollface:</td><td class="vertical">'.put_smileys('{trollface}').'</td><td>:troll:</td><td class="vertical">'.put_smileys('{troll}').'</td><td>:yeah: :fuckyeah:</td><td>'.put_smileys('{yeah}').'</td></tr>';
    echo '<tr><td>:clint:</td><td class="vertical">'.put_smileys('{clint}').'</td><td>:yaoface:</td><td class="vertical">'.put_smileys('{yaoface}').'</td><td>:longcat:</td><td>'.put_smileys('{longcat}').'</td></tr>';
    echo '<tr><td>:cejas:</td><td class="vertical">'.put_smileys('{cejas}').'</td><td>:sisi3:</td><td class="vertical">'.put_smileys('{sisitres}').'</td></tr>';
    echo '</table>';
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
    echo '<ul class="tabhoriz">' . "\n";

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
            echo '<li'.$active.'><a href="'.$globals['base_url'].'ayuda.php?id='.$tab.'" title="'.$reload_text.'">'._($name).'</a></li>' . "\n";
        } else {
            echo '<li><a href="'.$globals['base_url'].'ayuda.php?id='.$tab.'">'._($name).'</a></li>' . "\n";
        }
    }
    echo '</ul>' . "\n";
}
