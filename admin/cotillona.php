<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jon Arano <arano.jon@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

    // TODO! Una chapuza en toda regla
    // Por Jon Arano <arano.jon@gmail.com>
    // No me peguéis
    include '../config.php';
    include mnminclude.'html1.php';

    if (!$current_user->admin) do_error('no tienes permisos para entrar aquí', '403');


    /* Necesitamos las variables posteriormente */
    if (has_day()) {

        $peticion = explode(",", has_day()) ;

        $dia_peticion = intval($peticion['0']);
        $mes_peticion = intval($peticion['1']);

        $dia_anterior = mktime(0, 0, 0, $mes_peticion, $dia_peticion );
        $dia_siguiente =  mktime(23, 59, 0, $mes_peticion, $dia_peticion);
    }

    if (has_day() && !usa_buscador()) {
        $where = "where chat_room != 'friends' AND chat_time >= $dia_anterior AND chat_time <= $dia_siguiente";
        $order = " ORDER BY  chat_time ASC ";
        $dias = 1;
        do_header('Log cotillona | Jonéame');
        $option = 4;
        print_sneak_tabs($option);
        echo "<a href='/admin/cotillona.php'> Ver todo el log </a>";


    } else if (usa_buscador()) {

        $search_text = clean_input_string(usa_buscador());
        $_GET["order"] = clean_input_string($_GET["order"]);
        $id = $db->get_var('SELECT user_id FROM users WHERE user_login="'.$search_text.'"');
        if (intval($id)){
            do_header('Log de '.$search_text .' | Jonéame');
            $where = "where chat_uid=$id ";
            $option = 1;
            $dias = 4;
            print_sneak_tabs($option);
            if ($dia_anterior) $where .= "chat_room != 'friends' AND and chat_time >= $dia_anterior AND chat_time <= $dia_siguiente" ;
            $order = " ORDER BY  chat_time ".$_GET["order"];
        } else {
            do_header('Log cotillona | Jonéame');
            $option = 1;

            print_sneak_tabs($option);
            print_search_box();
            echo "Usuario no encontrado";
            do_footer();
            die;

        }

    } else if (admin_chat()){

        $where = " where chat_room='admin' ";
        $order = " ORDER BY chat_time desc ";
        $dias = 3;
        do_header('Log cotillona | Jonéame');
        $option = 2;
        print_sneak_tabs($option);


    } else if (log_cotillona()){

        do_header('Log cotillona | Jonéame');
        $option = 3;
        print_sneak_tabs($option);
        $logs = $db->get_results("select * from fisban where log_name in ('cotiban', 'cotiunban', 'cotiban_error', 'cotiunban_error')  order by date asc");


    } else {
        do_header('Log cotillona | Jonéame');
        $option = 1;
        print_sneak_tabs($option);
        $where = "";
        $dias = 1;
        $order = "where chat_room != 'friends' ORDER BY  chat_time DESC ";
    }

    if (!log_cotillona())
    $todos =  $db->get_results("select * from chats_logs $where $order");

    if (!$todos && !log_cotillona() ) {
        echo "No hay actividad";
        die;
    }


    $cantidad = 0;

    print_search_box();

    if ($todos) { // chats de la cotillona

    foreach ($todos as $chat) {

        $hora = date('H', $chat->chat_time);
        $minutos = date('i', $chat->chat_time);
        $dia = date('N', $chat->chat_time);
        $mes = date('n', $chat->chat_time);
        $numero =  date('j', $chat->chat_time);
        /*************************************************************************************/

        $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

        $mes = $mes - 1; //adaptamos al array

        $mes_letra = $meses[$mes];

        $mes = $mes + 1; //lo devolvemos a su número original

        /************************************************************************************/
        $dias_semana = array ("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");

        $dia = $dia - 1; //adaptamos al array

        $dia_letra = $dias_semana[$dia];

        $dia = $dia + 1; //lo devolvemos a su número original
        /************************************************************************************/


        if ($dia_anterior != $numero ) $cantidad = $cantidad + 1;

        if ($dia_anterior != $numero && $cantidad <= $dias) {

            echo get_enlace($numero, $mes, $dia_letra, $numero, $mes_letra);
        }


        if ($cantidad <= $dias) {

            echo '<b>'.$hora. ':'.$minutos. ' ';

            echo $chat->chat_user.':</b> ';

            if ($chat->chat_uid > 0) echo ' <img src="'.get_avatar_url($chat->chat_uid, -1, 20).'"  alt="'.$chat->chat_user.'"/> ';

            $sala= "";

            if ($chat->chat_room== 'admin') $sala= '(Admin) ';
            if ($chat->chat_room== 'friends') $sala =  '(Amigos) ';
            if ($chat->chat_room== 'devel') $sala=  '(Devel) ';


            if(preg_match('/\b'.$current_user->user_login.'\b/i', $chat->chat_text))
                echo '<b>'.$sala.put_smileys(normalize_smileys(text_to_html($chat->chat_text))).'</b>'; //negrita si contiene user_login
            else
                echo $sala.put_smileys(normalize_smileys(text_to_html($chat->chat_text)));

            echo "<br/><br/>";
        }

        else if ($enlaces != 1 || $dia_anterior != $numero) { //si son más viejos, ponemos un enlace para verlos

            echo get_enlace($numero, $mes, $dia_letra, $numero, $mes_letra);

            $enlaces =1; //para que solo ponga 1 enlace, y no $n veces, donde $n es la cantidad de lineas de chat
        }

    $dia_anterior = $numero ;


    } //bucle
    } //log
    else if ($_REQUEST['log'] && $logs){ // tenemos logs
        include_once mnminclude.'coti2.inc.php';

        echo '<center><h2> Historial </h2></center>';

        foreach ($logs as $log) {

            /* from coti2.inc.php */
            $admin = read($log->por);

            $user = read($log->uid);

            // ¿Baneado o desbaneado?
            switch($log->log_name){

                     case 'cotiban':
                       $que= 'Baneado ';
                   $title = $log->razon;
                       break;

                     case 'cotiban_error':
                       $que='Error al banear ';
                   $title = $log->razon;
                       break;

                     case 'cotiunban':
                       $que='Desbaneado ';
                   $title = false;
                       break;

                     case 'cotiunban_error':
                       $que='Error al desbanear ';
                   $title = $log->razon;
                       break;

                        }

            if ($title) echo "<img src='../../img/iconos/error.png' alt='warn' title='".$title."'> ";


            /* Info */
            echo $que. '<img src="'.get_avatar_url($user['id'], -1, 20).'"  alt="'.$user['username'].'"/> <a href="'.get_user_uri($user['username']).'">'. $user['username'].'</a> por <img src="'.get_avatar_url($admin['id'], -1, 20).'"  alt="'.$admin['username'].'"/><a href="'.get_user_uri($admin['username']).'">'. $admin['username'].'</a> a las '.$log->date."\n";
            if (baneatuta($user['id'])) echo ' (<a href="'.$globals['base_url'].'cotillona.php?unban='.$user['id'].'">Desbanear</a>)';
            echo '<br/><br/>';
        }

        /* Some stats */
        echo '<center><h2> Estadísticas </h2></center>';

        echo 'Baneos vigentes: '.$db->get_var("SELECT count(*) from fisban where vigente =1").'<br/><br/>';

        $baneados_id = $db->get_results("select distinct (uid) as id, user_login from fisban,users where users.user_id=fisban.uid  and log_name='cotiban'");

        foreach ($baneados_id as $user) {
            $cantidad = $db->get_var("SELECT count(*) from fisban where uid=$user->id and log_name='cotiban'");

            if ($cantidad == 1) $vez = 'vez';
            else $vez = 'veces';
            echo '<b>'.$user->user_login. '</b> baneado '.$cantidad.' '.$vez.'<br/>';
        }

    }

    do_footer();

// La cosa esa de las barras, para facilitar al usuario
function print_sneak_tabs($option) {
    global $current_user, $dia_peticion;
    $active = array();
    $active[$option] = ' class="current"';
    echo '<ul class="tabmain">' . "\n";

    echo '<li'.$active[1].'><a href="'.$globals['base_url'].'/admin/cotillona.php">'._('completo').'</a></li>' . "\n";
    echo '<li'.$active[2].'><a href="'.$globals['base_url'].'/admin/cotillona.php?admin=1">'._('sala admin').'</a></li>' . "\n";
    echo '<li'.$active[3].'><a href="'.$globals['base_url'].'/admin/cotillona.php?log=1">'._('cotibans').'</a></li>' . "\n";

    if ($_REQUEST['dia']) echo '<li'.$active[4].'><a href="'.$globals['base_url'].'/admin/cotillona.php?logs=1">'._('día ').$dia_peticion.'</a></li>' . "\n";
    echo '</ul>' . "\n";
}

//barra de búsqueda

function print_search_box() {
    global $current_user, $globals, $site_key;

    if ($_REQUEST['admin']) $admin = '1';
    else $admin = 0;

    $key = md5($globals['user_ip'].$current_user->user_id.$site_key);

    echo '<div style="float:right;">'."\n";
    echo '<form method="get" action="'.$globals['base_url'].'admin/cotillona.php">';

    if ($_REQUEST["dia"])
    echo '<input type="hidden" name="dia" value="'.$_REQUEST["dia"].'" />';
    echo '<input type="text" name="s" ';

    if ($_REQUEST["s"]) {
        $_REQUEST["s"] = clean_input_string($_REQUEST["s"]);
        echo ' value="'.$_REQUEST["s"].'" ';
    } else {
        echo ' value="'._('usuario...').'" ';
    }

    echo 'onblur="if(this.value==\'\') this.value=\''._('usuario...').'\';" onfocus="if(this.value==\''._('usuario...').'\') this.value=\'\';" />';

    /* Ordenar por */
    echo '&nbsp;&nbsp;<select name="order">';
    echo '<option value="desc">'._('más nuevos primero').'</option>';
    echo '<option value="asc">'._('más viejos primero').'</option>';
    echo '</select>';

    /* Lupa */
    echo '&nbsp;<input style="padding: 3px;" type="image" align="top" value="buscar" alt="buscar" src="'.$globals['base_url'].'img/iconos/magglass.png" />';


    echo '</form>';
    echo '</div>';

}

function has_day() {

    return $_REQUEST['dia'];

    }

function admin_chat() {

    return intval($_REQUEST['admin']) == 1;

    }

function usa_buscador() {

    if (isset($_REQUEST['s'])) {
        return clean_input_string($_REQUEST['s']);
    }
    return false;
}
function log_cotillona() {

    return intval($_REQUEST['log']) == 1;

    }

function get_enlace($numero, $mes, $dia_letra, $numero, $mes_letra) {

    $enlace = '<a href="/admin/cotillona.php?dia='.$numero.','.$mes;
    /* Completa el enlace */
    if (usa_buscador()) {
        $enlace .= '&s='.usa_buscador();
        if ($_GET["order"]) $enlace .= '&order='.$_GET["order"].'">';
        else  $enlace .= '">';
    } else $enlace .= '">';

    $enlace .= '<h2><div align="center" ><u>'.$dia_letra .', '.$numero.' de '.$mes_letra.'</u></div></h2></a><br><br>';

    return $enlace;
    }
