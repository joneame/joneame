<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

class Annotation {
    var $key = false;
    var $time;
    var $text = '';

    function Annotation($key = false) {
        if ($key) $this->key = $key;
        return;
    }

    function store() {
        global $db;

        if (empty($this->key)) return false;

        $key = $db->escape($this->key);
        $text = $db->escape($this->text);
        $db->query("REPLACE INTO annotations (annotation_key, annotation_text) VALUES ('$key', '$text')");
    }

    function read($key = false) {
        global $db;

        if ($key) $this->key = $key;
        if (empty($this->key)) return false;

        $key =  $db->escape($this->key);
        if(($record = $db->get_row("SELECT UNIX_TIMESTAMP(annotation_time) as time, annotation_text as text FROM annotations WHERE annotation_key = '$key'"))) {
            $this->time = $record->time;
            $this->text = $record->text;
            return true;
        }
        return false;
    }

    function append($text) {
        if ($text) {
            $this->read();
            $this->text .= $text;
            $this->store();
        }
    }

    function optimize() {
        global $db;

        $db->query("OPTIMIZE TABLE annotations");
    }
}
