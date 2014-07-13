<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'annotation.php');

$globals['ads'] = true;
$globals['extra_head'] = '
<style type="text/css">
p {
    font-family: Bitstream Vera Sans, Arial, Helvetica, sans-serif;
    font-size: 90%;
}
table {
    font-size: 110%;
    margin: 0px;
    padding: 4px;
}
td {
    margin: 0px;
    padding: 4px;
}
.thead {
    font-size: 115%;
    text-transform: uppercase;
    color: #FFFFFF;
    background-color: #429ee9;
    padding: 6px;
}
.tdata0 {
    background-color: #FFF;
}
.tdata1 {
    background-color: #adcee9;
}
.tnumber0 {
    text-align: center;
}
.tnumber1 {
    text-align: center;
    background-color: #adcee9;
}
</style>
';

do_header('Pronote | Jonéame');

echo '<div id="singlewrap">'."\n";

$annotation = new Annotation('promote');
$annotation->text = $output;
if($annotation->read()) {
    echo $annotation->text;
}

echo '</div>'."\n";

do_footer();