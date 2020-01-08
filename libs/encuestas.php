<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

class Encuesta {
    // encuesta
    var $id;
    var $comienzo;
    var $finish;
    var $autor;
    var $ip;
    var $titulo;
    var $contenido;
    var $votos = 0;
    var $multiple;
    // user
    var $nick;
    var $avatar;
    var $level;
    var $carisma;
    // otro
    var $opciones;
    var $read = false;



    /* lectura */
    function read() {
        global $db;

        // curamos en salud..
        $this->id = intval($this->id);

        // En caso de que encuentre dicha encuesta..

        if ($encuesta = $db->get_row("SELECT e.*, u.user_id, u.user_login, u.user_avatar FROM encuestas e INNER JOIN users u ON (e.encuesta_user_id = u.user_id) WHERE e.encuesta_id=".$this->id)) {
            // variables de encuesta
            $this->id=$encuesta->encuesta_id;
            $this->comienzo=$encuesta->encuesta_start;
            $this->finish=$encuesta->encuesta_finish;
            $this->autor=$encuesta->encuesta_user_id;
            $this->ip=$encuesta->encuesta_ip;
            $this->titulo=$encuesta->encuesta_title;
            $this->contenido=$encuesta->encuesta_content;
            $this->finished = (strtotime($encuesta->encuesta_finish) < time()) ? 1 : 0;
            $this->multiple=$encuesta->encuesta_multiple;

            $this->votos_totales=$encuesta->encuesta_total_votes;
            $this->cant_votos_total();

            if (!($this->opciones = $this->getPollOptions())) $this->opciones = 0;

            $this->votos = $this->opciones['votes'];
            $this->comentarios = $encuesta->comentarios;

            // variables de user
            $this->user_id=$encuesta->user_id;
            $this->nick=$encuesta->user_login;
            $this->avatar=$encuesta->user_avatar;
            $this->read= true;
            return true;
        }

        return false;
    }

    /* lectura básica */
    function read_basic() {
        global $db;

        // curamos en salud..
        $this->id = intval($this->id);

        if ($encuesta = $db->get_row("SELECT user_avatar as avatar, user_login as nick, user_id as autor, encuesta_total_votes as votos_totales, encuesta_content as contenido FROM encuestas e INNER JOIN users u ON (e.encuesta_user_id = u.user_id) WHERE e.encuesta_id=".$this->id)) {

            foreach(get_object_vars($encuesta) as $var => $value) $this->$var = $value;

            $this->read= true;
            return true;
        }

        return false;
    }

    /* almacena los datos en la DB */

    function almacenar() {
        global $db, $globals;

        //check
        $fin = $db->escape($this->finish);
        $comienzo = time();
        $autor = intval($this->autor);
        $votos = $this->votos_totales;
        $ip = $db->escape($this->ip);
        $this->titulo = $db->escape(clear_whitespace(clean_text($this->titulo)));
        $titulo = $this->titulo;
        $contenido = $db->escape(clear_whitespace(clean_text($this->contenido)));
        $multiple = intval($this->multiple);

        if(!$this->id) { //nueva inserción
            $db->query("INSERT INTO encuestas (encuesta_start, encuesta_finish,    encuesta_user_id, encuesta_ip, encuesta_title, encuesta_content, encuesta_total_votes, encuesta_multiple) VALUES (FROM_UNIXTIME($comienzo), FROM_UNIXTIME($fin), '$autor', '$ip','$titulo','$contenido', 0, '$multiple')");
            $this->id = $db->insert_id;

            // inserción de opciones
            for ($j = 0; $j < $this->opciones['count']; $j++) {
                if($this->id)
                    $db->query("INSERT HIGH_PRIORITY INTO encuestas_opts VALUES (NULL, ".$this->id.", '".$this->opciones[$j]."')");
            }

        } else
           $db->query("UPDATE encuestas SET encuesta_title='$titulo', encuesta_content='$contenido', encuesta_total_votes='$votos' WHERE encuesta_id=$this->id");
    }


    /* Imprime la encuesta seleccionada */

    function print_encuesta() {
        global $current_user, $page, $globals;

        echo '<h4><a href="'.get_encuesta_uri($this->id).'"><img class="icon permalink img-flotante" alt="permalink" src="'.get_cover_pixel().'"/></a>&nbsp;&nbsp;'.$this->titulo.'</h4>';
        echo '<fieldset class="encuesta" id="encuesta'.$this->id.'">';
        echo '<div class="news-submitted encuesta-userinfo">';
        echo '<a href="'.get_user_uri($this->nick).'"><span><img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$this->user_id.');" onmouseout="tooltip.clear(event);" class="avatar" src="'.get_avatar_url($this->autor, $this->avatar, 80).'" width="40" height="40" alt="'.$this->nick.'"/></span></a>';
        echo 'por <strong><a href="'.get_user_uri($this->nick).'">'.$this->nick.'</a></strong><br/>';
        echo 'empezó el <strong>'.$this->comienzo.'</strong><br/>';

        if (!$this->finished)
            echo 'finalizará el <strong>'.$this->finish.'</strong>';
                else
            echo 'finalizó el <strong>'.$this->finish.'</strong>';

        echo '<br/><br/>usuarios que han votado: <strong><span id="usuarios-totales-'.$this->id.'">'.$this->votos_totales.'</span></strong>';
        echo '<br/>opiniones: <strong><span id="opiniones-'.$this->id.'">'.$this->comentarios.'</span></strong>';
        echo '</div><strong>Descripción: </strong>';

        echo text_to_html($this->contenido);

        echo '<br/><br/>';
        echo 'Votos: <strong><span id="votos-e-'.$this->id.'">'.$this->votos.'</span></strong><br/>';

        echo '<br>';

        echo '<input type="hidden" name="encid" value="'.$this->id.'" />'."\n";

        echo '<div id="pollvotes'.$this->id.'">';

        if (!$this->userVoted() && !$this->finished)
            $this->print_options();
        else
            $this->print_stats();

        echo '</div>'."\n";

        echo '</fieldset>'."\n";
    }

    /*
    * Funciones de estadisticas, opciones y votos
    * Aritz <aritz@itxaropena.org>
    */

    function print_stats() {
        global $current_user;

        /* Imprime resultados */
        for ($i=0; $i < $this->opciones['count'];$i++) {
            echo text_to_html(put_smileys($this->opciones[$i]->poll->info));
            echo ' <span class="smaller">(<strong>' . $this->opciones[$i]->votes.'</strong> votos / <strong>' . $this->opciones[$i]->percent . '</strong>%)</span><br/>';
            echo '<div class="barra-encuesta-outer"><div class="barra-carisma-inner" style="width: '.$this->opciones[$i]->percent.'%;"></div></div><br/><br/>'."\n";
        }

        $this->print_editbox();

    }

    private function print_editbox(){
        global $current_user;

        /* Edición para gods */
        if ($current_user->user_level == 'god') {
            echo '<div id="editbox-'.$this->id.'">';
            echo '<input type="hidden" id="process-'.$this->id.'" name="process" value="show_box">';
            echo '<input type="hidden" id="cuenta'.$this->id.'" name="cuenta" value="'. $this->opciones['count'].'">';
            echo '<a href="#" onclick="edit_poll('.$this->id.')">Editar</a> - ';
            echo '<a href="#" onclick="delete_poll('.$this->id.')">Eliminar</a>';
            echo '</div>';
        }
    }

    // imprime las opciones
    private function print_options() {
        global $current_user;
        echo '<dl>';

        echo '<input type="hidden" id="cuenta_'.$this->id.'" name="cuenta" value="'. $this->opciones['count'].'">';

        for ($i=0; $i < $this->opciones['count'];$i++) {
            echo '<dt>';

            if (!$this->multiple)
                echo '<input type="radio" id="opcion_'.$this->id.'['.$i.']" name="opcion" value="'.$this->opciones[$i]->poll->id.'">';
            else
                echo '<input type="checkbox" id="opcion_'.$this->id.'['.$i.']" name="opcion['.$i.']" value="'.$this->opciones[$i]->poll->id.'">';

            echo '</dt><dd><label id="opcion['.$i.']" for="opcion['.$i.']">';
            echo text_to_html(put_smileys($this->opciones[$i]->poll->info));
            echo ' <span>(<strong>' . $this->opciones[$i]->votes.'</strong> votos / <strong>' . $this->opciones[$i]->percent . '</strong>%)</span></label></dd>';
        }

        echo '</dl>';

        echo '<input type="submit" onClick="ajax_poll_vote('.$this->id.'); return false;" id="but_enc_"'.$this->id.'" name="but_enc'.$this->id.'" value="¡Votar!">';

        $this->print_editbox();

    }

    // Inserta un nuevo voto
    function doVote($optid) {
        global $db, $globals, $current_user;

        /* El usuario ya ha votado o el periodo de voto ha terminado */
        if ($this->optionVoted($optid) || $this->finished) return false;

        if ($db->query("INSERT INTO encuestas_votes VALUES (NULL, ".$current_user->user_id.", ".$optid.", ".$this->id.", FROM_UNIXTIME(".time()."), '".$globals['user_ip']."')")) return true;

        return false;

    }

    // En el caso de que el usuario haya votado.. responde con true
    function optionVoted($opt) {
        global $db, $current_user;

        if ($current_user->user_id == 0) return true; // Usuario anónimo

        if ($db->get_var("SELECT count(*) FROM encuestas_votes WHERE uid = '".intval($current_user->user_id)."' AND optid = '".$opt."'") > 0) return true;
        return false;
    }

    // En el caso de que el usuario haya votado.. responde con true
    function userVoted() {
        global $db, $current_user;

        $uid = $current_user->user_id;

        if ($uid <= 0) return true; // Usuario anónimo

        if ($db->get_var("SELECT count(*) FROM encuestas_votes WHERE uid = '".intval($uid)."' AND pollid = '".$this->id."'") > 0)             return true;

        return false;
    }

    /*
    * Funciones privadas para la gestión de votos y opciones.
    * Aritz <aritz@itxaropena.org>
    */

    private function getPollOptions() {
        global $db;

        $pollid = intval($this->id);

        if ($opciones = $db->get_results("SELECT info, id FROM encuestas_opts WHERE encid=".$pollid)) {
            /* Almacenaremos los datos en una estructura igual que esta:
                (votes) numero de votos totales
                (count) numero de opciones
                (id)
                "poll" => datos sobre la opcion
                "votes" => cantidad de votos
                "votes_info" => informacion completa de votos
                "percent" => porcentaje del total
            */
            $count = 0;
            $votes_tot = 0;
            $return = array();
            foreach ($opciones as $aukera) {
                // recibimos datos sobre los votos
                $votes = $this->getOptionVotes($aukera->id);
                $votes_tot += $votes->total;
                $return[$count]->poll = $aukera;
                $return[$count]->votes = $votes->total;
                $return[$count]->percent = $votes->percent;
                $count++;
            }

            $return['count'] = $count;
            $return['votes'] = $votes_tot;
            return $return;
        }

        return false; // error

    } // getpolloptions end

    private function cant_votos_total(){
        global $db;

    $this->cant_votos_total = $db->get_var("SELECT count(*) FROM encuestas_votes WHERE pollid=".$this->id);

    }

    private function getOptionVotes($optid) {
        global $db;

        $optid = intval($optid);

        $votos = $db->get_var("SELECT count(*) FROM encuestas_votes WHERE optid=".$optid);

        if ($votos > 0) {

            // sacamos porcentaje
            $porcentaje = round((($votos/$this->cant_votos_total)*100),2);

            // almacenar datos y devolver
            $return->percent = $porcentaje;
            $return->total = $votos;
            return $return;
        }

        // no se ha encontrado ningun voto.
        $return->total = 0;
        $return->percent = 0;

        return $return;

    } // getOptionVotes

    // destruye lo mas importante
    function destroyData() {
        $this->opciones = 0;
        $this->id = 0;
    }

    function get_relative_individual_permalink() {
        global $globals;
        if ($globals['base_encuesta_url']) {
        return $globals['base_url'] . $globals['base_encuesta_url'] . $this->id;
        } else {
        return $globals['base_url'] . 'encuesta.php?id=' . $this->id;
        }
    }

    function insert_promotion_post(){
        global $current_user, $globals;

        include_once('posts.php');
        $post = new Post;
        $post->date = $globals['now'];
        $post->author = $current_user->user_id;
        $post->src = 'web';
        $post->karma = 0;
        $post->randkey = rand(1000000,100000000);
        $post->tipo = 'encuesta';
        $post->content = $this->titulo.' https://'.get_server_name().$this->get_relative_individual_permalink();
        $post->store();
    }

    /* Actualiza las opciones y el título */
    /* JA <arano.jon@gmail.com> */
    function update_option(){
        global $db, $current_user;

        if ($current_user->user_level == 'god'){

            $db->query("UPDATE encuestas_opts SET info='".$this->info."' WHERE id=$this->option_id");

        }

    }

    function update_info(){
        global $db, $current_user;

        if ($current_user->user_level == 'god'){

            $db->query("UPDATE encuestas SET encuesta_title='".$this->new_titulo."', encuesta_content = '".$this->new_description."' WHERE encuesta_id=$this->id");

        }

    }

    function delete_poll(){
        global $db, $current_user;

        if ($current_user->user_level == 'god'){

            $db->query("DELETE FROM encuestas WHERE encuesta_id=$this->id");
            $db->query("DELETE FROM encuestas_opts WHERE encid=$this->id");

        }
    }

} // class encuesta end