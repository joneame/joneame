<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');

if ($current_user->admin) {
    do_header(_('Administración de Jonéame - Cómo mola ser capo de la mafia'));
    echo "<br/><br/>";
    echo '<div class="genericform" style="text-align: center;"><h4>Il capos administration: todo lo que necesitáis para imponer la justicia</h4><fieldset class="fondo-caja"><a href="cortos.php"><img src="../img/panel/cortos.png" border=0 /></a> <a href="bans.php"><img src="../img/panel/ban.png" border=0 /></a> <br/><a href="http://devel.joneame.net"><img src="../img/panel/devel.png" border=0 /></a> <a href="historial.php"><img src="../img/panel/historial.png" border=0 /></a></div>';

    echo "<br/><br/><br/>";
} else {
     do_error(_('Esta página es sólo para administradores, sal de aquí, cojones ya. <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>Le llamaremos a Chuck Norris para que te de una patada giratoria. O sino a Jack Bauer, que últimamente Chuck anda muy ocupado resolviendo conflictos con los chinos.'), 403);
}
echo "</div>";
echo "</div>"; // singlewrap
do_footer();