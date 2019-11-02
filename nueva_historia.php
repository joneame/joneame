<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//              http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'ban.php');
include(mnminclude.'link.php');
include(mnminclude.'tags.php');
include(mnminclude.'blog.php');

$globals['ads'] = false;

/* He has a URL, go to 2nd phase directly. Updated for Chrome Joneame Extension (by Urko) -- Jon*/
if (!empty($_GET['url'])) {
            do_header(_("enviar chorrada 2/3"), "post");
                        echo '<div id="singlewrap">' . "\n";
                        do_submit1();
                        echo "</div>\n"; // singlewrap
            do_footer();
            die;
}

if(isset($_POST["phase"])) {
        force_authentication();
        switch ($_POST["phase"]) {
                case 1:
                        do_header(_("enviar chorrada 2/3"), "post");
                        echo '<div id="singlewrap">' . "\n";
                        do_submit1();
                        break;
                case 2:
                        do_header(_("enviar chorrada 3/3"), "post");
                        echo '<div id="singlewrap">' . "\n";
                        do_submit2();
                        break;
                case 3:
                        do_submit3();
                        break;
        }
} else {
        check_already_sent();
        force_authentication();
        do_header(_("enviar chorrada 1/3"), "post");
        echo '<div id="singlewrap">' . "\n";
        do_submit0();
}
echo "</div>\n"; // singlewrap
do_footer();
exit;

    function preload_indicators() {
        global $globals;

        echo '<script>'."\n";
        echo '<!--'."\n";
        echo 'var img_src1=\''.$globals['base_url'].'img/estructura/cargando.gif\''."\n";;
        echo 'var img1= new Image(); '."\n";
        echo 'img1.src = img_src1';
        echo '//-->'."\n";
        echo '</SCRIPT>'."\n";
    }

    function check_already_sent() {

        // Check if the url has been sent already
        if (!empty($_GET['url'])) {
                $linkres = new Link;
                if (($found = $linkres->duplicates($_GET['url']))) {
                       $dupe = Link::from_db($found);
                        if($dupe->read) {
                                header('Location: ' . $dupe->get_permalink());
                                die;
                        }
                }
        }
    }

    function print_empty_submit_form() {
        global $current_user, $site_key;

        preload_indicators();
        if (!empty($_GET['url'])) {
        $url = clean_input_url($_GET['url']);
        }
        echo '<div class="genericform">';
        echo '<h4>dirección de la historia</h4>';
        echo '<form class="fondo-caja" action="nueva_historia.php" method="post" id="thisform" onSubmit="$(\'#working\').html(\''._('espera').'...&nbsp;<img src=\\\'\'+img_src1+\'\\\'/>\'); return true;"><fieldset>';
        echo '<p><label for="url">'._('enlace').':</label><br />';
        echo '<input type="text" name="url" id="url" value="'.htmlspecialchars($url).'" class="form-full" placeholder="http://" /></p>';
        echo '<input type="hidden" name="phase" value="1" />';
        $randkey = rand(10000,10000000);
        echo '<input type="hidden" name="key" value="'.md5($randkey.$current_user->user_id.$current_user->user_email.$site_key.get_server_name()).'" />'."\n";
        echo '<input type="hidden" name="randkey" value="'.$randkey.'" />';
        echo '<input type="hidden" name="id" value="c_1" />';
        echo '<p><input class="button" type="submit" value="'._('continuar »').'" ';
        echo '/>&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span></p>';
        echo '</fieldset></form>';
        echo '</div>';
    }

    function do_submit0() {
        echo '<h2>'._('envío de una nueva historia: paso 1 de 3').'</h2>';
        echo '<div class="faq">';
        echo '<h3>'._('por favor, respeta estas instrucciones para mejorar la poca calidad que tenemos:').'</h3>';
        echo '<ul class="instruction-list">';
        echo '<li><strong>¿'._('has leído las').' <a href="condiciones.php" target="_blank">'._('condiciones de uso').'</a></strong>?</li>';
        echo '<li><strong>'._('contenido interesante').':</strong> '._('en jonéame gusta el porno (por si no lo sabías), ¿crees que interesará tu historia?').'</li>';
        echo '<li><strong>'._('no somos un medio serio').':</strong> '._('jonéame no es un medio serio, tómatelo todo a coña, pero no te privamos de noticias serias, si te gustan, o son de gran relevancia').'</li>';
        echo '<li><strong>'._('busca antes').':</strong> '._('por favor, usa el buscador para así evitar historias duplicadas').'</li>';
        echo '<li><strong>'._('respeta el voto de los demás').':</strong> '._('en jonéame tenemos el voto mafia, al loro con él, si ves que tu historia no gusta, pasa a la siguiente y no te preocupes').'</li>';

        echo '</ul></div><br/>'."\n";
        print_empty_submit_form();
    }

    function do_submit1() {
        global $db, $dblang, $current_user, $globals;

        if ($_POST['url'])
                $url = clean_input_url($_POST['url']);
        else if ($_GET['url'])
        $url = clean_input_url($_GET['url']);

                $url = preg_replace('/#[^\/]*$/', '', $url); // Remove the "#", people just abuse
                $url = preg_replace('/^http:\/\/http:\/\//', 'http://', $url); // Some users forget to delete the http://

                if (! preg_match('/^\w{3,6}:\/\//', $url)) { // http:// forgotten, add it
                      $url = 'http://'.$url;
                }

        echo '<div>'."\n";

        $new_user = false;
        if (!check_link_key()) {
                echo '<p class="error"><strong>'._('clave incorrecta').'</strong></p> ';
                echo '</div>'. "\n";
                return;
        }
        if ($globals['min_karma_for_links'] > 0 && $current_user->user_karma < $globals['min_karma_for_links'] ) {
                echo '<p class="error"><strong>'._('no tienes el mínimo de carisma para enviar una nueva historia').'</strong></p> ';
                echo '</div>'. "\n";
                return;
        }

        $queued_24_hours = (int) $db->get_var("select count(*) from links where link_status!='published' and link_date > date_sub(now(), interval 24 hour) and link_author=$current_user->user_id");

        if ($globals['limit_user_24_hours'] && $queued_24_hours > $globals['limit_user_24_hours']) {
                echo '<p class="error">'._('Debes esperar, tienes demasiadas noticias en cola de las últimas 24 horas'). " ($queued_24_hours), "._('disculpa las molestias'). ' </p>';
                syslog(LOG_NOTICE, "Jonéame, too many queued in 24 hours ($current_user->user_login): $_POST[url]");
                echo '<br style="clear: both;" />' . "\n";
                echo '</div>'. "\n";
                return;
        }

        // check the URL is OK and that it resolves
        $url_components = parse_url($url);
        if (!$url_components || ! $url_components['host'] || gethostbyname($url_components['host']) == $url_components['host']) {
                echo '<p class="error"><strong>'._('URL o nombre de servidor erróneo').'</strong></p> ';
                echo '<p>'._('el nombre del servidor es incorrecto o éste tiene problemas para resolver el nombre'). ' </p>';
                syslog(LOG_NOTICE, "Jonéame, hostname error ($current_user->user_login): $url");
                print_empty_submit_form();
                echo '</div>'. "\n";
                return;
        }

        $enqueued_last_minutes = (int) $db->get_var("select count(*) from links where link_status='queued' and link_date > date_sub(now(), interval 3 minute)");
        if ($current_user->user_karma > 10) $enqueued_limit = $globals['limit_3_minutes'] * 1.5;
        else $enqueued_limit = $globals['limit_3_minutes'];

        if ($enqueued_last_minutes > $enqueued_limit) {
                echo '<p class="error"><strong>'._('Exceso de envíos').':</strong></p>';
                echo '<p>'._('Se han enviado demasiadas noticias en los últimos 3 minutos'). " ($enqueued_last_minutes > $enqueued_limit), "._('disculpa las molestias'). ' </p>';
                syslog(LOG_NOTICE, "Jonéame, too many queued ($current_user->user_login): $_POST[url]");
                echo '</div>'. "\n";
                return;
        }

        // Check the user does not have too many drafts
        $drafts = (int) $db->get_var("select count(*) from links where link_author=$current_user->user_id  and link_date > date_sub(now(), interval 30 minute) and link_sent = 0");

        // Delete dangling drafts
        if ($drafts > 0) {
                $db->query("delete from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 30 minute) and link_date < date_sub(now(), interval 10 minute) and link_sent=0");
        }


        // Check for banned IPs
        if(($ban = check_ban($globals['user_ip'], 'ip', true)) || ($ban = check_ban_proxy())) {
                echo '<p class="error"><strong>'._('Dirección IP no permitida para enviar').':</strong> '.$globals['user_ip'].'</p>';
                echo '<p><strong>'._('Razón').'</strong>: '.$ban['comment'].'</p>';
                if ($ban['expire'] > 0) {
                        echo '<p class="note"><strong>'._('caduca').'</strong>: '.get_date_time($ban['expire']).'</p>';
                }
                syslog(LOG_NOTICE, "Jonéame, banned IP $globals[user_ip] ($current_user->user_login): $url");
                print_empty_submit_form();
                echo '</div>'. "\n";
                return;
        }

        // Number of links sent by the user
        $total_sents = (int) $db->get_var("select count(*) from links where link_author=$current_user->user_id") - $drafts;
        if ($total_sents > 0) {
                $sents = (int) $db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 60 day)") - $drafts;
        } else {
                $new_user = true;
                $sents = 0;
        }

        if ($globals['now'] - $current_user->Date() < 86400*3) {
                $new_user = true;
        }

        // check that a new user also votes, not only sends links
        // it requires $globals['min_user_votes'] votes
        if ($new_user && $globals['min_user_votes'] > 0 && $current_user->user_karma < 6.1) {
                $user_votes_total = (int) $db->get_var("select count(*) from votes where vote_type='links' and vote_user_id=$current_user->user_id");
                $user_votes = (int) $db->get_var("select count(*) from votes where vote_type='links' and vote_date > date_sub(now(), interval 72 hour) and vote_user_id=$current_user->user_id");
                $user_links = 1 + $db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 24 hour) and link_status != 'discard'");
                $total_links = (int) $db->get_var("select count(*) from links where link_date > date_sub(now(), interval 24 hour) and link_status = 'queued'");
                if ($sents == 0) {
                        // If is a new user, requires more votes, to avoid spam
                        $min_votes = $globals['min_user_votes'];
                } else {
                        $min_votes = min(4, intval($total_links/20)) * $user_links;
                }
                if (!$current_user->admin && $user_votes < $min_votes) {
                        $needed = $min_votes - $user_votes;
                        echo '<p class="error">';
                        if ($new_user) {
                                echo '<strong>'._('¿es la primera vez que envías una noticia?').'</strong></p> ';
                                echo '<p class="error-text">'._('necesitas como mínimo'). " <strong>$needed " . _('votos') . '</strong><br/>';
                        } else {
                                echo '<strong>'._('no tienes el mínimo de votos necesarios para enviar una nueva historia').'</strong></p> ';
                                echo '<p class="error-text">'._('necesitas votar como mínimo a'). " <strong>$needed " . _('noticias') . '</strong><br/>';
                        }
                        echo '<strong>'._('no votes de forma apresurada, penaliza el karma').'</strong><br/>';
                        echo '<a href="'.$globals['base_url'].'jonealas.php" target="_blank">'._('haz clic aquí para ir a votar').'</a></p>';
                        echo '<br style="clear: both;" />' . "\n";
                        echo '</div>'. "\n";
                        return;
                }
        }

        $linkres=new Link;
        $linkres->url = $url;

        $edit = false;

        if(report_dupe($url)) return;


        if(!$linkres->check_url($url, true, true) || !$linkres->get($url)) {
                echo '<blockquote>';
                echo '<p class="error"><strong>'._('URL erróneo o no permitido').'</strong>: ';
                if ($linkres->ban && $linkres->ban['match']) {
                        echo $linkres->ban['match'];
                } else {
                        echo $linkres->url;
                }
                echo '</p>';
                echo '<p><strong>'._('Razón').':</strong> '. $linkres->ban['comment'].'</p>';
                if ($linkres->ban['expire'] > 0) {
                        echo '<p class="note"><strong>'._('caduca').'</strong>: '.get_date_time($linkres->ban['expire']).'</p>';
                }
                // If the domain is banned, decrease user's karma
                if ($linkres->banned && $current_user->user_level == 'normal') {
                        $db->query("update users set user_karma = user_karma - 0.05 where user_id = $current_user->user_id");
                }
                echo '</blockquote><br/>';
                print_empty_submit_form();
                echo '</div>'. "\n";
                return;
        }

        // If the URL has changed, check again is not dupe
        if($linkres->url != $url && report_dupe($linkres->url)) return;

        if (!$_POST['randkey']) $_POST['randkey'] = $globals['link_randkey'];

        $linkres->randkey = intval($_POST['randkey']);


        if(!$linkres->valid) {
                echo '<p class="error"><strong>'._('error leyendo el url').':</strong> '.htmlspecialchars($url).'</p>';
                // Dont allow new users with low karma to post wrong URLs
                if ($current_user->user_karma < 8 && $current_user->user_level == 'normal') {
                        echo '<p>'._('URL inválido, incompleto o no permitido. Está fuera de línea, o tiene mecanismos antibots.').'</p>';
                        print_empty_submit_form();
                        return;
                }
                echo '<p>'._('No es válido, está fuera de línea, o tiene mecanismos antibots. <strong>Continúa</strong>, pero asegúrate que sea correcto').'</p>';
        }

        $linkres->status='discard';
        $linkres->author=$current_user->user_id;
        $linkres->sent = 0;

        if (!$linkres->trackback()) {

                $linkres->pingback();
        }
        $trackback=htmlspecialchars($linkres->trackback);
        $linkres->create_blog_entry();
        $blog = new Blog;
        $blog->id = $linkres->blog;
        $blog->read();

        $blog_url_components = parse_url($blog->url);
        $blog_url = $blog_url_components['host'].$blog_url_components['path'];
        // Now we check again against the blog table
        // it's done because there could be banned blogs like http://lacotelera.com/something
        if(($ban = check_ban($blog->url, 'hostname', false, true))) {
                echo '<p class="error"><strong>'._('URL inválido').':</strong> '.htmlspecialchars($url).'</p>';
                echo '<p>'._('El sitio').' '.$ban['match'].' '. _('está deshabilitado'). ' ('. $ban['comment'].') </p>';
                if ($ban['expire'] > 0) {
                        echo '<p class="note"><strong>'._('caduca').'</strong>: '.get_date_time($ban['expire']).'</p>';
                }
                syslog(LOG_NOTICE, "Jonéame, banned site (".$current_user->user_login."): ".$blog->url." <- ".$_POST['url']);
                print_empty_submit_form();
                echo '</div>'. "\n";
                return;
        }

        $same_blog = $db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 60 day) and link_blog=$linkres->blog");

        $check_history =  $sents > 3 && $same_blog > 0 && ($ratio = $same_blog/$sents) > 0.5;

        $ratio = (float) $db->get_var("select count(distinct link_blog)/count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 60 day)");

        if ($check_history) {
                // Calculate ban period according to previous karma
                $avg_karma = (int) $db->get_var("select avg(link_karma) from links where link_blog=$blog->id and link_date > date_sub(now(), interval 30 day)");
                // This is the case of unique/few users sending just their site and take care of choosing goog titles and text
                if ($sents > 4 && $avg_karma < 30) {
                        if ($avg_karma < -40) {
                                $ban_period = 86400*30;
                                $ban_period_txt = _('un mes');
                        } elseif ($avg_karma < -10) {
                                $ban_period = 86400*7;
                                $ban_period_txt = _('una semana');
                        } elseif ($avg_karma < 10) {
                                $ban_period = 86400;
                                $ban_period_txt = _('un día');
                        } else {
                                $ban_period = 7200;
                                $ban_period_txt = _('dos horas');
                        }
               syslog(LOG_NOTICE, "Jonéame, high ratio ($ratio) and low karma ($avg_karma), going to ban $blog->url ($current_user->user_login)");
                }

                if ($ban_period > 0) {
                        echo '<p class="error"><strong>'._('ya has enviado demasiados enlaces a')." $blog->url".'</strong></p> ';
                        echo '<p class="error-text">'._('varía tus fuentes, es para evitar abusos y enfados por votos negativos') . ', ';
                        echo '<a href="'.$globals['base_url'].'condiciones.php">'._('normas de uso de Joneáme').'</a>, ';
                        echo '<a href="'.$globals['base_url'].'faq.php">'._('el FAQ').'</a></p>';

                        if (!empty($blog_url)) {
                                $ban = insert_ban('hostname', $blog_url, _('envíos excesivos de'). " $current_user->user_login", time() + $ban_period);
                                $banned_host = $ban->ban_text;
                                echo '<p class="error-text"><strong>'._('el dominio'). " '$banned_host' ". _('ha sido baneado por')." $ban_period_txt</strong></p> ";
                                syslog(LOG_NOTICE, "Jonéame, banned '$ban_period_txt' due to high ratio ($current_user->user_login): $banned_host  <- $linkres->url");
                        } else {
                                syslog(LOG_NOTICE, "Jonéame, error parsing during ban: $blog->id, $blog->url ($current_user->user_login)");
                        }
                        echo '<br style="clear: both;" />' . "\n";
                        echo '</div>'. "\n";
                        return;
                }
        }


        if(($ban = check_ban($linkres->url, 'punished_hostname', false, true))) {
                echo '<p class="error"><strong>'._('Aviso').' '.$ban['match']. ':</strong> <em>'.$ban['comment'].'</em></p>';
                echo '<p>'._('mejor enviar el enlace a la fuente original, sino será penalizado por los usuarios').'</p>';
        }


        // Now stores new draft
        $linkres->ip = $globals['user_ip'];
        $linkres->sent_date = $linkres->date=time();
        $linkres->store();

        echo '<h2>'._('envío de una nueva chorrada: paso 2 de 3').'</h2>'."\n";

        echo '<div class="genericform">'."\n";

        echo '<form action="nueva_historia.php" method="post" id="thisform" name="thisform">'."\n";

        echo '<fieldset class="fondo-caja redondo inverso"><legend class="mini barra redondo">'._('información del enlace').'</legend>'."\n";
        echo '<input type="hidden" name="url" id="url" value="'.htmlspecialchars($linkres->url).'" />'."\n";
        echo '<input type="hidden" name="phase" value="2" />'."\n";
        echo '<input type="hidden" name="randkey" value="'.intval($_POST['randkey']).'" />'."\n";
        echo '<input type="hidden" name="key" value="'.$_POST['key'].'" />'."\n";
        echo '<input type="hidden" name="id" value="'.$linkres->id.'" />'."\n";
        echo '<p class="genericformtxt"><strong>';
        echo mb_substr($linkres->url_title, 0, 200);
        echo '</strong><br/>';
        echo htmlspecialchars($linkres->url);
        echo '</p> '."\n";
        echo '</fieldset>'."\n";

        echo '<br/>';

        echo '<h4>'._('detalles de la noticia').'</h4><div class="fondo-caja"><fieldset>'."\n";

        echo '<label for="title" accesskey="1">'._('título de la noticia').':</label>'."\n";
        echo '<p><span class="note">'._('título de la noticia. máximo: 120 caracteres').'</span>'."\n";
        // Is it an image or video?
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $linkres->print_content_type_buttons();

        if (!$link_title) {
            $link_title = mb_substr($linkres->url_title, 0, 200);
        }
        echo '<br/><input type="text" id="title" name="title" value="'.$link_title.'" size="80" maxlength="120" />';
        echo '</p>'."\n";

        echo '<label for="tags" accesskey="2">'._('etiquetas').':</label>'."\n";
        echo '<p><span class="note">'._('añade etiquetas para facilitar la posterior búsqueda').'</span>';
        echo '<br/><input placeholder="pornografía, gatos, humor" type="text" id="tags" name="tags" value="'.$link_tags.'" size="70" maxlength="70" /></p>'."\n";

        //botones de formateo
        echo '<div style="float: right;">';
        print_simpleformat_buttons('bodytext');
        echo '</div>';

        echo '<p><label for="bodytext" accesskey="3">'._('descripción de la noticia').':</label>'."\n";
        echo '<br /><span class="note">'._('describe el enlace con tus palabras — este campo es opcional');
        echo '</span>'."\n";
        echo '<br/><textarea name="bodytext" rows="10" cols="60" id="bodytext" onKeyDown="textCounter(document.thisform.bodytext,document.thisform.bodycounter,5000)" onKeyUp="textCounter(document.thisform.bodytext,document.thisform.bodycounter,5000)">'.$link_content.'</textarea>'."\n";
        $body_left = 5000 - mb_strlen(html_entity_decode($link_content, ENT_COMPAT, 'UTF-8'), 'UTF-8');
        echo '<input readonly type="text" name="bodycounter" size="3" maxlength="3" value="'. $body_left . '" /> <span class="note">' . _('caracteres libres') . '</span>';


        echo '</p>'."\n";
        echo '<br /></p>'."\n";

        print_categories_form();

        echo '<br/>';
        echo '<input class="button" type="button" onclick="window.history.go(-1)" value="'._('« retroceder').'" />&nbsp;&nbsp;'."\n";
        echo '<input class="button" type="submit" value="'._('continuar »').'" />'."\n";
        echo '</fieldset></div>'."\n";
        echo '</form>'."\n";
        echo '</div>'."\n";
        echo '</div>'."\n";
    }


    function do_submit2() {
        global $db, $dblang, $globals, $current_user;


        $link_id = intval($_POST['id']);
        $linkres = Link::from_db($link_id);

        $linkres->read_content_type_buttons($_POST['type']);

        // Check if the title contains [IMG], [IMGs], (IMG)... and mark it as image
        if (preg_match('/[\(\[](IMG|PICT*)s*[\)\]]/i', $_POST['title'])) {
                $_POST['title'] = preg_replace('/[\(\[](IMG|PICT*)s*[\)\]]/i', ' ', $_POST['title']);
                $linkres->content_type = 'image';
        } elseif (preg_match('/[\(\[](VID|VIDEO|Vídeo*)s*[\)\]]/i', $_POST['title'])) {
                $_POST['title'] = preg_replace('/[\(\[](VID|VIDEO|Vídeo*)s*[\)\]]/i', ' ', $_POST['title']);
                $linkres->content_type = 'video';
        }

        $linkres->category=intval($_POST['category']);

        if ($linkres->category == 207 || $linkres->category == 37) echo '<div class="form-error-submit">&nbsp;&nbsp;'._("Estás enviando una historia en una categoría pornografía. Acuérdate de NSFW o +18 si procede").'</div><br/>'."\n";

        $enviadas = (int) $db->get_var("select count(*) from links where link_status!='published' and link_date > date_sub(now(), interval 24 hour) and link_author=$current_user->user_id");

            $restantes = $globals['limit_user_24_hours'] - $enviadas;

        if ($restantes < $globals['limit_user_24_hours'] && $restantes < 25) {

                echo '<div class="warn"><strong>'._('Aviso').'</strong>: ';
            echo _('Dispones de ').$restantes.' historias restantes en las próximas 24 horas</a>';
            echo "</div>\n";

        }

        // Metemos el titulo original en una variable y segun el NSFW y +18 cambiamos el titulo o no.
        if ($_POST['sec']) {
            $zer = $_POST['sec'];
            if ($zer['0']) // nsfw
            $gehitu = " [NSFW]";
            if ($zer['1'])
            $gehitu .= " [+18]";
        }

        $titulu_originala = clean_text(preg_replace('/(\w) *[;.,] *$/', "$1", $_POST['title']), 40);
        $linkres->title = $titulu_originala.$gehitu;

        $linkres->tags = tags_normalize_string($_POST['tags']);

        if (!empty($_POST['bodytext']))
            $linkres->content = clean_text($_POST['bodytext']);

        if (link_errors($linkres)) {
                echo '<form class="genericform">'."\n";
                echo '<p><input class="button" type=button onclick="window.history.go(-1)" value="'._('« retroceder').'"/></p>'."\n";
                echo '</form>'."\n";
                echo '</div>'."\n"; // opened in print_form_submit_error
                return;
        }

        /* Insert tags */
        tags_insert_string($linkres->id, $dblang, $linkres->tags);

        $linkres->store();
        $linkres = Link::from_db($linkres->id);
        $edit = true;
        $link_title = $linkres->title;
        $link_content = $linkres->content;
        preload_indicators();
        echo '<div class="genericform">'."\n";

        echo '<h2>'._('envío de una nueva noticia: paso 3 de 3').'</h2>'."\n";

        echo '<form action="nueva_historia.php" method="post" class="genericform">'."\n";
        echo '<fieldset class="redondo"><legend class="mini barra redondo"><span class="sign">'._('detalles de la noticia').'</span></legend>'."\n";

        echo '<div class="genericformtxt"><label>'._('ATENCIÓN: ¡esto es sólo una muestra!').'</label>&nbsp;&nbsp;<br/>'._('Ahora puedes 1) ').'<label>'._('retroceder').'</label>'._(' o 2)  ').'<label>'._('enviar a la cola y finalizar').'.</label> '._('¡Deja que la mafia decida!').'</div>';

        echo '<div class="formnotice">'."\n";
        $linkres->print_summary('preview');

        echo '</div>'."\n";

        echo '<input type="hidden" name="phase" value="3" />'."\n";
        echo '<input type="hidden" name="randkey" value="'.intval($_POST['randkey']).'" />'."\n";
        echo '<input type="hidden" name="key" value="'.$_POST['key'].'" />'."\n";
        echo '<input type="hidden" name="id" value="'.$linkres->id.'" />'."\n";
        echo '<input type="hidden" name="trackback" value="'.htmlspecialchars(trim($_POST['trackback'])).'" />'."\n";
        echo '<input type="hidden" name="aleatorio" value="'.$_POST['aleatorio'].'" />'."\n";

        echo '<br style="clear: both;" /><br style="clear: both;" />'."\n";
        echo '<input class="button" type="button" onclick="window.history.go(-1)" value="'._('« retroceder').'"/>&nbsp;&nbsp;'."\n";
        echo '<input class="button" type="submit" value="'._('enviar a la cola y finalizar »').'" ';
        echo '/>&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span>';
        echo '</fieldset>'."\n";
        echo '</form>'."\n";
        echo '</div>'."\n";
    }

    function do_submit3() {
        global $db, $current_user, $globals;

        $link_id = intval($_POST['id']);
        $linkres = Link::from_db($link_id);

        if(!check_link_key() || !$linkres->read) die;

        // Check it is not in the queue already
        if($linkres->votes == 0 && $linkres->status != 'queued') {
                $linkres->status='queued';
                $linkres->sent_date = $linkres->date=time();
                $linkres->get_uri();
            $linkres->sent = 1;
                $linkres->store();
            $linkres->insert_user_click();
                if ($_POST['aleatorio'] && $current_user->user_karma > 7)
                $linkres->insert_aleatorio = true;

                else  $linkres->insert_aleatorio = false;

            $linkres->insert_vote($current_user->user_karma);

                // Add the new link log/event
                require_once(mnminclude.'log.php');
                log_conditional_insert('link_new', $linkres->id, $linkres->author);
        }

        header('Location: '. $linkres->get_permalink());
        die;

    }

    function check_link_key() {
        global $site_key, $current_user, $globals;

        /* If the user came from Jnm extension, it cames directly, without randkey. Create it */
        if (!$_POST['randkey']) {
            $randkey = rand(10000,10000000);
            $globals['link_randkey'] = $randkey;

        }
        else $randkey = $_POST['randkey'];

        /* Same with the key */
        if (!$_POST['key']) $key = md5($randkey.$current_user->user_id.$current_user->user_email.$site_key.get_server_name());
        else $key = $_POST['key'];


        return $key == md5($randkey.$current_user->user_id.$current_user->user_email.$site_key.get_server_name());
    }

    function kendu_beharrezkoak($titulu) {
        // Tituluan NSFW edo +18 aurkitu badira, kendu.
        // Si se encuentran NSFW o +18 en el titulo, quitarlos
        $titulu = str_replace("[NSFW]", "", $titulu);
        $titulu = str_replace("[+18]", "", $titulu);
        return $titulu;
    }

    function link_errors($linkres) {
        global $globals;
        $error = false;

        // Errors
        if($linkres->status != 'discard') {
                print_form_submit_error(_("La historia ya está en cola").": $linkres->status");
                $error = true;
        }
        if((strlen(addslashes($linkres->content))) > 5000) {
                        print_form_submit_error(_("Has sobrepasado el límite de caracteres para la descripción"));
                $error = true;
        }
        if ( strlen(addslashes($linkres->title)) < 4 ) {
                        print_form_submit_error(_("Título incompleto"));
                $error = true;
        }

        if ( strlen(addslashes($linkres->content) ) < 6 && !$globals['permitir_sin_entradilla'] ){
            print_form_submit_error(_("Entradilla demasiado corta"));
                $error = true;
        }

        if(get_uppercase_ratio(kendu_beharrezkoak($linkres->title)) > 0.25  || get_uppercase_ratio($linkres->content) > 0.25 ) {
                print_form_submit_error(_("Demasiadas mayúsculas en el título o texto, ¡ASEGÚRATE de que es correcto!"));
                // $error = true;
        }
        if(mb_strlen(html_entity_decode($linkres->title, ENT_COMPAT, 'UTF-8'), 'UTF-8') > 120  || mb_strlen(html_entity_decode($linkres->content, ENT_COMPAT, 'UTF-8'), 'UTF-8') > 5000 ) {
                print_form_submit_error(_("Título o texto demasiado largos"));
                $error = true;
        }
        if(strlen(addslashes($linkres->tags)) < 3 ) {
                print_form_submit_error(_("No has puesto etiquetas"));
                $error = true;
        }

        if(preg_match('/.*http:\//', $linkres->title)) {
                print_form_submit_error(_("Por favor, no pongas URLs en el título, no ofrece información"));
                $error = true;
        }
        if(!$linkres->category > 0) {
                print_form_submit_error(_("Categoría no seleccionada"));
                $error = true;
        }
        if (!existe_categoria($linkres->category)){
                print_form_submit_error(_("La categoría no existe"));
                $error = true;
        }
        return $error;
    }

    function existe_categoria($selected_cat) {
        global $db;
        //buscamos los ID de todas las categorias
        $ids = $db->get_col("SELECT category_id as id FROM categories");

        foreach ($ids as $category) {
                //en el momento que coincide, es decir, la categoria existe, devolvemos true
                if ($category==$selected_cat) {
                        return true;
                       break;
                }
        }
        return false;

    }

    function print_form_submit_error($mess) {
        static $previous_error=false;

        if (!$previous_error) {
                // ex container-wide
            echo '<div class="genericform">'."\n"; // this div MUST be closed after function call!
                echo '<h2>'._('¡Vaya! :-(').'</h2>'."\n";
                $previous_error = true;
        }
        echo '<div class="form-error-submit">&nbsp;&nbsp;'._($mess).'</div><br/>'."\n";
    }

    function report_dupe($url) {

        $link = new Link;
        if(($found = $link->duplicates($url))) {
                $dupe = Link::from_db($found);
                echo '<p class="error"><strong>'._('noticia repetida!').'</strong></p> ';
                echo '<p class="error-text">'._('lo sentimos').'</p>';
                $dupe->print_summary();
                echo '<br style="clear: both;" /><br/>' . "\n";
                echo '<form class="genericform" action="">';
                echo '<input class="button" type="button" onclick="window.history.go(-1)" value="'._('« retroceder').'" />';
                echo '</form>'. "\n";
                echo '</div>'. "\n";
                return true;
        }
        return false;
    }