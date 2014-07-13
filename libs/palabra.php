<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jon <arano.jon@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once 'user.php';

class Palabra {
var $id;
var $texto;
var $por;
var $carisma;
var $votos;
var $aux;
var $avatar;

        // sql fields to build an object from mysql
        const SQL = " id, palabra, definicion, fecha, diccionario, activado, user_avatar as avatar, user_login as username, user_level as level FROM dictionary  INNER JOIN users on (por = user_id)";

    static function from_db($id) {
            global $db;

        if (is_numeric($id) && $id > 0) $selector = " id = $id ";

        if(($object = $db->get_object("SELECT".Palabra::SQL." WHERE $selector", 'Palabra'))) {
            $object->read = true;
            return $object;
        }
        return false;
        }

    /*function vote_exists() {
        global $current_user;
        require_once(mnminclude.'votes.php');
        $vote = new Vote;
        $vote->user=$current_user->user_id;
        $vote->type='cortos';
        $vote->link=$this->id;
        $this->voted = $vote->exists();
        return $this->voted;
    }

    function iconos_votos() {
        global $globals, $current_user;

                echo '<span class="buttons">';

        $this->voted = $this->vote_exists();

        if ($current_user->user_karma > $globals['min_karma_for_comment_votes'] && !$this->voted) {
            echo '<span id="c-votes-'.$this->id.'">';
            echo '<a href="javascript:votar_corto('."$current_user->user_id,$this->id,-1".')" title="'._('voto negativo').'"><img src="'.get_cover_pixel().'" alt="'._('voto negativo').'" class="icon vote-down"/></a>';
            echo '<a href="javascript:votar_corto('."$current_user->user_id,$this->id,1".')" title="'._('voto positivo').'"><img src="'.get_cover_pixel().'" alt="'._('voto positivo').'" class="icon vote-up"/></a>';
            echo '</span>';
        } else {
            if ($this->voted > 0)
                echo '<img src="'.get_cover_pixel().'" title="'._('votado positivo').'" class="icon voted-up"/>';
            else if ($this->voted < 0)
                echo '<img src="'.get_cover_pixel().'" title="'._('votado negativo').'" class="icon voted-down"/>';
        }
        echo '</span>';
    }

    function info_votos() {
        global $globals;
        echo ' <a class="fancybox" href="'.$globals['base_url'].'backend/mostrar_votos_cortos.php?id='.$this->id.'" >';
        echo '<img src="'.get_cover_pixel().'" title="'._('¿quién ha votado?').'" class="icon info-mini"/>';
        echo '</a>';
    }


    function get_relative_individual_permalink() {
        // Permalink of the "comment page"
        global $globals;
        if ($globals['base_corto_url']) {
        return $globals['base_url'] . $globals['base_corto_url'] . $this->id;
        } else {
        return $globals['base_url'] . 'corto.php?id=' . $this->id;
        }
    }*/


    function print_short_info() {
        global $globals, $current_user;


        echo '<a href=""><img src="'.get_cover_pixel().'" class="icon permalink-mini" title="'._('permalink').'"/></a> ';



        echo '<strong>'.clean_text($this->palabra).':</strong> '.clean_text($this->definicion).' <br/><br/>';
        //echo '<span class="info-cortos">'._('votos').': <span id="vc-'.$this->id.'">'.$this->votos.'</span>, carisma: <span id="vk-'.$this->id.'">'.$this->carisma.'</span>';
        //if ($this->votos > 0) {
        //    $this->info_votos();
        //}

        //echo '</span>';

        //lapiz a gods y autores (si no han sobrepasado el limite de ediciones)
        //if (($current_user->user_level == 'god') || ($this->id_autor ==$current_user->user_id && $this->ediciones <= $globals['ediciones_max_cortos'] && $this->activado))
        //echo '<a href="'.$globals['base_url'].'editar_corto.php?id='.$this->id.'&editar=1" title="'._('Editar corto').'"><img class="icon edit img-flotante" src="'.get_cover_pixel().'" alt="'.('Editar corto').'"/></a>&nbsp;';


        //papelera solo para gods
        //if ($current_user->user_level == 'god')
        //    echo '<a href="'.$globals['base_url'].'admin/cortos.php?admin=pendientes&amp;ezabatu='.$this->id.'&amp;key='.$key.'" title="'._('Eliminar').'"><img class="icon delete img-flotante" src="'.get_cover_pixel().'" alt="denegar" title="denegar el corto"/></a>';




    }
}