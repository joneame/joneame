<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Dupe {

    function get() {
        global $db;

        if ($link = $db->get_row("SELECT * FROM duplicates WHERE link_id=$this->id")){

            $this->duplicate = $link->duplicate;
            $this->dupe = true;

            return true;

            }
        return false;
    }

    function link(){

        if ($this->dupe) return $this->duplicate;
        
        return false;
    }

    function save(){
        global $db, $current_user;

        if (!$current_user->admin) return false;

        require_once(mnminclude.'link.php');
        $link = new Link;
        $link->id = $this->id;
        if (!$link->read_basic()) return false;

        $this->url = $db->escape($this->url);

        if ($db->query("INSERT INTO duplicates (link_id=$this->id, duplicate=$this->url)"))
        return true;

        return false;
    
    
    }

    function insert_duplicated_url(){
		global $db;

		if ($db->query("INSERT into duplicates (link_id, duplicate) VALUES ($this->id, '$this->duplicated')")) return true;
		
		return false;

    }

    function edit_link(){
	global $db;

	if ($db->query("UPDATE duplicates SET duplicate='$this->duplicated' WHERE link_id=$this->id")) return true;
		
		return false;

	}

}

