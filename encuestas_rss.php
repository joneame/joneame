<?
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jon Arano (arano.jon@gmail.com)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'encuestas.php');
include(mnminclude.'user.php');

if(!empty($_REQUEST['rows'])) {
    $rows = intval($_REQUEST['rows']);
    if ($rows > 300) $rows = 100; //avoid abuses
} else $rows = 100;

// Bug in FeedBurner, it needs all items
if (preg_match('/feedburner/i', $_SERVER['HTTP_USER_AGENT'])) {
    $if_modified = 0;
} else {
    $if_modified = get_if_modified();
}

$individual_user = false;
if(!empty($_GET['id'])) {
    //
    // Pool
    //
    $id = intval($_GET['id']);
    if ($if_modified) {
        $extra_sql = "AND encuesta_start > $if_modified ";
    }

    $sql = "SELECT encuesta_id FROM encuestas WHERE encuesta_id=$id $extra_sql ORDER BY encuesta_start DESC LIMIT $rows";
    $last_modified = $db->get_var("SELECT encuesta_start FROM encuesta_start WHERE encuesta_id=$id ORDER BY encuesta_start DESC LIMIT 1");
    $title = _('Jonéame: encuesta') . " $id";
    $globals['redirect_feedburner'] = false;
} elseif(!empty($_GET['user_id'])) {
    //
    // Users pools
    //
    $individual_user = true;
    $id = guess_user_id($_GET['user_id']);
    $username = $db->get_var("select user_login from users where user_id=$id");
    if ($if_modified > 0)
        $from_time = "AND encuesta_start > $if_modified";
    $sql = "SELECT encuesta_id FROM encuestas WHERE encuesta_user_id=$id $from_time ORDER BY encuesta_id DESC LIMIT $rows";
    $last_modified = $db->get_var("SELECT encuesta_start FROM encuestas WHERE encuesta_id=$id ORDER BY encuesta_start DESC LIMIT 1");
    $title = _('Jonéame: encuestas de ') . $username;
    $globals['redirect_feedburner'] = false;
} /*elseif() {

}*/ else {
    //
    // All pools
    //
    $id = 0;
    if ($if_modified > 0)
        $from_time = "WHERE encuesta_start > $if_modified";
    $sql = "SELECT encuesta_id FROM encuestas $from_time ORDER BY encuesta_start DESC LIMIT $rows";
    $last_modified = $db->get_var("SELECT encuesta_start FROM encuestas ORDER BY encuesta_start DESC LIMIT 1");
    $title = _('Jonéame: todas las encuestas');
    $globals['redirect_feedburner'] = false;
}





$encuesta = new Encuesta;

$encuestas = $db->get_col($sql);

if ( !$encuestas && $if_modified) {
    header('HTTP/1.1 304 Not Modified');
    exit();
}
do_header($title);

if ($encuestas) {
    foreach($encuestas as $encuesta_id) {
        $encuesta->id=$encuesta_id;
        $encuesta->read();
                $user = new User;
                $user->id= $encuesta->autor;
        $user->read();
                $opciones = $db->get_results("SELECT info from encuestas_opts WHERE encid=$encuesta_id");
                $numero=1;
        echo "    <item>\n";

        // Title must not carry htmlentities
        echo "        <title>".htmlentities2unicodeentities($encuesta->titulo)."</title>\n";
        echo "        <link>http://".get_server_name()."/encuestas.php?id=".$encuesta->id."</link>\n";
        echo "        <pubDate>".$encuesta->comienzo."</pubDate>\n";
        echo "        <dc:creator>$user->username</dc:creator>\n";
        echo "        <guid>http://".get_server_name()."/encuestas.php?id=".$encuesta->id."</guid>\n";
        echo "        <description><![CDATA[<p>$encuesta->contenido";
        echo '</p><p></p>';
                foreach ($opciones as $opcion) {
                echo '<p>Opción '.$numero .' : '. $opcion->info.'</p>';
                $numero ++;
                }
                echo " <p></p>";
                echo '<p>»&nbsp;'._('pregunta').': <strong>'.$user->username.'</strong></p>';
        echo "]]></description>\n";
        echo "    </item>\n\n";
    }
}

do_footer();

function do_header($title) {
    global $if_modified, $last_modified, $dblang, $globals;

    if (!$last_modified > 0) {
        if ($if_modified > 0)
            $last_modified = $if_modified;
        else
            $last_modified = time();
    }
    header('X-If-Modified: '. gmdate('D, d M Y H:i:s',$if_modified));
    header('X-Last-Modified: '. gmdate('D, d M Y H:i:s',$last_modified));
    if ($last_modified <= $if_modified) {
        header('HTTP/1.1 304 Not Modified');
        exit();
    }
    header('Last-Modified: ' .  gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
    header('Content-type: text/xml; charset=UTF-8', true);
    echo '<?xml version="1.0" encoding="UTF-8"?'.'>' . "\n";
    echo '<rss version="2.0" '."\n";
    echo '     xmlns:content="http://purl.org/rss/1.0/modules/content/"'."\n";
    echo '     xmlns:wfw="http://wellformedweb.org/CommentAPI/"'."\n";
    echo '     xmlns:dc="http://purl.org/dc/elements/1.1/"'."\n";
    echo ' >'. "\n";
    echo '<channel>'."\n";
    echo'    <title>'.$title.'</title>'."\n";
    echo'    <link>http://'.get_server_name().'</link>'."\n";
    echo"    <image><title>".get_server_name()."</title><link>http://".get_server_name()."</link><url>http://".get_server_name().$globals['base_url']."img/mnm/eli-rss.png</url></image>\n";
    echo'    <description>'._('Sitio colaborativo de noticias nada serias').'</description>'."\n";
    echo'    <pubDate>'.date("r", $last_modified).'</pubDate>'."\n";
    echo'    <generator>http://blog.joneame.net/</generator>'."\n";
    echo'    <language>'.$dblang.'</language>'."\n";
}

function do_footer() {
    echo "</channel>\n</rss>\n";
}


?>
