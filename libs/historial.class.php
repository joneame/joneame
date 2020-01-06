<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Aritz <aritz@itxaropena.org>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

class Historial {
var $error = 1;
var $data;


    // Read user data
    function read($uid) {
        global $db;

        if ($info = $db->get_results("SELECT * FROM historial WHERE uid = ".intval($uid)))
        {
            $this->error = 0; // aunque esta inicializado a 0.
            $this->data = $info;

        } else $this->error = 1;


    }

        // Read historial data
    function read_id() {
        global $db;

        $historial = $db->get_row("SELECT texto, fecha,uid FROM historial WHERE id = ".intval($this->id)." LIMIT 1");
        if ($historial)
        {
            $this->error = 0;
            $this->historial_texto = $historial->texto;
            $this->historial_fecha = $historial->fecha;
            $this->quien = $db->get_var("SELECT user_login FROM users WHERE user_id = ".intval($historial->uid));
            return true;

        } else $this->error = 1;

        return false;

    }

    function insert() {
        global $db, $current_user;

        $texto = $db->escape(str_replace("\n", "<br />", $this->texto));
        $user_id = $current_user->user_id;
        $who = intval($this->who);


        if ($db->query("INSERT INTO historial VALUES(NULL, '".$texto."', '".$user_id."', '".$who."', FROM_UNIXTIME(".time()."))")) {
        $this->id = $db->insert_id;
        return true;
        }

        return false;

    }
}