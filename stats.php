<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jon Arano <arano.jon@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'stats.php');

do_header(_('Estadísticas | Jonéame'));

echo '<strong>Fecha:</strong> '.date(" d-m-Y H:i:s T").'<br/><br/>';
echo '<strong>Promote:</strong> '.text_to_html('https://' . get_server_name().$globals['base_url']. 'promote.php').'<br/><br/>';
echo do_stats2().'<br/><br/>';
echo do_stats2('1').'<br/><br/>';
echo do_stats1().'<br/><br/>';
echo do_admins().'<br/><br/>';
echo do_last();

do_footer();