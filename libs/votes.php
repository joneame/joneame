<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Vote {
    var $type='links';
    var $user=-1;
    var $value=1;
    var $link;
    var $ip='';
    var $aleatorio = false;

    function Vote() {
        return;
    }

    function get_where($value='> 0') {
        global $globals;
        // Begin check user and ip
        $where = "vote_type='$this->type' AND vote_link_id=$this->link";
        if (empty($this->ip)) $this->ip=$globals['user_ip_int'];
        if($this->user > 0) {
            $where .= " AND (vote_user_id=$this->user OR vote_ip_int=$this->ip)";
        } elseif ($this->user == 0 ) {
            $where .= " AND vote_ip_int=$this->ip";
        }
        if (!empty($value)) $where .= " AND vote_value $value ";
        // End check user and ip
        return $where;
    }

    function exists() {
        global $db;
        $where = $this->get_where('');

        if ($valor = $db->get_row("SELECT SQL_NO_CACHE vote_value FROM votes WHERE $where LIMIT 1")){
            return $valor->vote_value; // get_row para que devuelva los votos con valor 0
        }

        return false;
    }

    function count($value="> 0") {
        global $db;
        $where = $this->get_where($value);
        $count=$db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes WHERE $where");
        return $count;
    }

    function insert() {
        global $db, $globals;

        if (empty($this->ip)) $this->ip=$globals['user_ip_int'];

        $this->value=round($this->value);

        if ($this->aleatorio == true) $tipo = 'aleatorio';
        else $tipo = 'normal';

        // HIGH_PRIORITY to avoid duplicates votes from people clicking very fast on purpose
        $sql="INSERT HIGH_PRIORITY INTO votes (vote_type, vote_user_id, vote_link_id, vote_value, vote_ip_int, vote_aleatorio) VALUES ('$this->type', $this->user, $this->link, $this->value, $this->ip, '$tipo' )";
        return $db->query($sql);
    }

    function get_aleatorio_value(){
        global $db, $current_user;

        $voto = rand(8, 20); // un número del 8 al 20

        // algoritmo para aplicar si negativo o positivo
        // cuento el numero de votos aleatorios positivos y negativos para una noticia
        $positivos = $db->query("SELECT COUNT(v.vote_id) FROM votes v INNER JOIN links l ON (v.vote_link_id = l.link_id)
        WHERE ((vote_value > 0) AND (vote_aleatorio = 'aleatorio') AND (l.link_id = {$this->link}))");

        $negativos = $db->query("SELECT COUNT(v.vote_id) FROM votes v INNER JOIN link l ON (v.vote_link_id = l.link_id)
        WHERE ((vote_value < 0) AND (vote_aleatorio = 'aleatorio') AND (l.link_id = {$this->link}))");

        // a partir de tres votos aleatorios >> límite de negativización en 0.5 : votos positivos todos los que caigan
        if ($positivos + $negativos > 2) {
            if (($positivos/$negativos) < 0.5)
                    $karma_value = $current_user->user_karma + $voto;
            else if (($positivos/$negativos) > 0.5)
                $karma_value = $voto * -1;
        } else {
            $numero = rand (1,6);
            if ($numero > 3)
                $karma_value = $current_user->user_karma + $voto; // premio! voto positivo
            else
                $karma_value = $voto * -1; // voto negativo
        }

        return $karma_value;
    }

}

