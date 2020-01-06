<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('../config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');

if ($current_user->admin) {
    do_header(_('Administración de Jonéame - Cómo mola ser capo de la mafia'));
    echo "<br/><br/>";
    echo '<div class="genericform" style="text-align: center;"><h4>Il capos administration: todo lo que necesitáis para imponer la justicia</h4><fieldset class="fondo-caja"><a href="cortos.php"><img src="../img/panel/cortos.png" border=0 /></a> <a href="bans.php"><img src="../img/panel/ban.png" border=0 /></a> <br/><a href="http://devel.joneame.net"><img src="../img/panel/devel.png" border=0 /></a> <a href="historial.php"><img src="../img/panel/historial.png" border=0 /></a></fieldset></div>';

    echo "<br/><br/><br/>";
} else {
     do_error(_('Esta página es sólo para administradores, sal de aquí, cojones ya.'), 403);
}
do_footer();