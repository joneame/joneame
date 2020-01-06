<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Jon Arano <arano.jon@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
include(mnminclude.'opinion.php');
include(mnminclude.'user.php');
include(mnminclude.'encuestas.php');

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
if(!empty($_GET['user_id'])) {
    //
    // Users pools
    //
    $individual_user = true;
    $id = intval($_GET['user_id']);
    $username = $db->get_var("select user_login from users where user_id=$id");

    $sql = "SELECT id FROM pools_comments WHERE autor=$id ORDER BY fecha DESC LIMIT $rows";
    $title = _('Jonéame: opiniones en encuestas de ') . $username;
    $globals['redirect_feedburner'] = false;
} /*elseif() {

}*/ else {
    //
    // All comments
    //
    $id = 0;
    if ($if_modified > 0)
        $from_time = "WHERE encuesta_start > $if_modified";
    $sql = "SELECT id FROM pools_comments ORDER BY fecha DESC LIMIT $rows";
    $title = _('Jonéame: todas las encuestas');
    $globals['redirect_feedburner'] = false;
}





$opinion = new Opinion;

$opiniones = $db->get_col($sql);


do_header($title);

if ($opiniones) {
    foreach($opiniones as $encuesta_id) {
        $opinion->id=$encuesta_id;
        $opinion->read();
                $user = new User;
                $user->id= $opinion->por;
        $user->read();
        $encuesta = new Encuesta;
        $encuesta->id = $opinion->encuesta_id;
        $encuesta->read();

        echo "    <item>\n";

        // Title must not carry htmlentities
        echo "        <title>".$encuesta->titulo."</title>\n";
        echo "        <link>http://".get_server_name()."/opinion/".$opinion->id."</link>\n";
        echo "        <pubDate>".$opinion->fecha."</pubDate>\n";
        echo "        <dc:creator>$user->username</dc:creator>\n";
        echo "        <guid>http://".get_server_name()."/opiniones_rss.php?id=".$opinion->id."</guid>\n";
        echo "        <description><![CDATA[<p>".put_smileys($opinion->contenido);
        echo '</p><p></p>';

                echo " <p></p>";
                echo '<p>»&nbsp;'._('autor').': <strong>'.$user->username.'</strong></p>';
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
