<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

require_once 'user.php';

class Corto {
var $id;
var $texto;
var $por;
var $carisma;
var $votos;
var $aux;
var $avatar;

    // Get random short message
    function get_random() {
        global $db;

        $cortos_especiales = $this->special_message_active();
        if ($this->id)
            $eskaria = $db->get_row("SELECT * FROM cortos WHERE id = $this->id LIMIT 1");
        elseif ($cortos_especiales)
            $eskaria = $db->get_row("SELECT * FROM cortos WHERE por = ".$cortos_especiales->value1." AND activado = '1' ORDER BY RAND() LIMIT 1");
        else {
            $max = $db->get_var("SELECT SQL_NO_CACHE MAX(cortos.id) FROM cortos");
            $guessed_id = rand(1, $max);
            $eskaria = $db->get_row("SELECT cortos.*, users.user_avatar, users.user_login  FROM cortos, users WHERE activado = '1' AND user_id=por AND user_level != 'disabled' AND cortos.id >= $guessed_id LIMIT 1");
        }

        $this->texto = $eskaria->texto;

        $this->carisma = $eskaria->carisma;
        $this->votos = $eskaria->votos;
        $this->id = $eskaria->id;
        $this->ediciones = $eskaria->ediciones;
        $this->id_autor = $eskaria->por;
        $this->por = $eskaria->user_login;
        if ($eskaria->user_avatar != 0) $this->avatar = $eskaria->user_avatar; else $this->avatar = 0;

        $this->aux = $cortos_especiales;


        if ($eskaria)
            return true;
        else
            return false;
    }

    // Get single short message
    function get_single() {
        global $db;

        $eskaria = $db->get_row("SELECT * FROM cortos WHERE id = $this->id LIMIT 1");

        if (!$eskaria)
            return false;

        $this->texto = $eskaria->texto;
        $this->carisma = $eskaria->carisma;
        $this->votos = $eskaria->votos;
        $this->id = $eskaria->id;
        $this->ediciones = $eskaria->ediciones;
        $this->id_autor = $eskaria->por;
        $this->activado = $eskaria->activado;


        $erab = new User();
        $erab->id = $this->id_autor;

        if ($erab->read()) {
            $this->por = $erab->username;
            if ($erab->avatar != 0) $this->avatar = $erab->avatar; else $this->avatar = 0;
        } else {
            $this->por = "Desconocido";
            $this->avatar = 0;
        }

        return true;
    }

    function save_new_corto() {
        global $db;

        $this->testu = $db->escape($this->testu);
        $this->user_id = $db->escape($this->user_id);
        $this->activado = 0;
        $this->votos = 0;
        $this->carisma = 0;
        $this->ediciones = 0;

        if ($db->query("INSERT INTO cortos VALUES (NULL, '".$this->testu."', '$this->user_id', '$this->activado', '$this->votos', '$this->carisma','$this->ediciones')")) {
            $this->id = $db->insert_id;
            return true;

        }

        return false;

    }

    function special_message_active() {
        global $db;

        $jaso = $db->get_row("SELECT * FROM gconfig WHERE status = '1' LIMIT 1");

        if ($jaso) {

        return $jaso;

        }


        return false;
    }

    function change_special_message($status, $uid, $texto) {
        global $db;

        $texto = $db->escape($texto);

        if (is_int($status) && is_int($uid)) {
            if ($status) // activate
                 $db->query("INSERT INTO gconfig (`id`, `value1`, `value2`, `status`) VALUES (NULL, '".$db->escape($uid)."', '$texto', '1');");

            else
                 $db->query("UPDATE gconfig SET status = 0 WHERE status = 1");

            return true;

        }

        return false;
    }


    function vote_exists() {
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


       function numero_ediciones() {
        global $db;

        $ediciones = $db->get_var("SELECT ediciones FROM cortos WHERE id=$this->id ");
        return $ediciones;
    }

    function do_corto() {
        global $globals, $current_user;

        if (!$this->activado) {
        echo '<div class="faq">';
        echo '<h3>'._('El corto está sin aprobar').'</h3>';
        echo '</div>'."\n";
        }

        echo '<div class="visor-cortos">';
        echo '<div class="texto-corto fondo-caja">';


        echo clean_text($this->texto); // no pone smileys por motivos obvios -.-
        echo '</div>';

        echo '<div class="corner">-</div>';

        if ($this->avatar)
            echo '<div class="avatar-usuario"><a href="'.get_user_uri($this->por).'"><img src="'.get_avatar_url($this->id_autor, $this->avatar, 80).'"/></a></div>';
        else
            echo '<div class="avatar-usuario"><a href="'.get_user_uri($this->por).'"><img src="'.$globals['base_url'].'img/v2/no-avatar-80.png"/></a></div>';

        echo '<ul class="barra redondo herramientas">';
        echo '<li><a href="'.$globals['base_url'].'corto.php" class="icon reload">'._('otro corto al azar'). '</a></li>';
        echo '<li><a href="'.$globals['base_url'].'nuevo_corto.php" class="icon post-new">'._('enviar nuevo corto'). '</a></li>';
        echo '<li><a href="'.get_corto_uri($this->id).'" class="icon permalink">'._('permalink'). '</a></li>';
        echo '</ul>';
        echo '<div class="nick-usuario">';
        echo '<div class="meta">';

        if ($current_user->user_id > 0 && $this->id_autor != $current_user->user_id)
            $this->iconos_votos();

        echo ' '._('votos').': <span id="vc-'.$this->id.'">'.$this->votos.'</span>, carisma: <span id="vk-'.$this->id.'">'.$this->carisma.'</span>&nbsp;&nbsp;';

        if ($this->votos > 0)
            $this->info_votos();

        echo '</div>';
        echo '<a href="'.get_user_uri($this->por, 'cortos').'">'.$this->por.'</a></div>';

        echo '</div>';
    }

    function get_relative_individual_permalink() {
        // Permalink of the "comment page"
        global $globals;
        if ($globals['base_corto_url']) {
        return $globals['base_url'] . $globals['base_corto_url'] . $this->id;
        } else {
        return $globals['base_url'] . 'corto.php?id=' . $this->id;
        }
    }

    function existe_pendiente_de_edicion() {
        global $db;

        $corto = $db->get_var("SELECT id_corto FROM edicion_corto where id_corto=$this->id");

        if ($corto)
        return true;
        else return false;
    }

    function print_short_info() {
        global $globals, $current_user, $site_key;

        $key = md5($globals['user_ip'].$current_user->user_id.$site_key);
        echo ' <a href="'.get_corto_uri($this->id).'"><img src="'.get_cover_pixel().'" class="icon permalink-mini" alt="permalink" title="'._('permalink').'"/></a> ';

        if ($this->activado){
            echo '</dt>';
            echo '<dd>';
        }

        echo clean_text($this->texto).' ';
        echo '<span class="info-cortos">'._('votos').': <span id="vc-'.$this->id.'">'.$this->votos.'</span>, carisma: <span id="vk-'.$this->id.'">'.$this->carisma.'</span>';
        if ($this->votos > 0) {
            $this->info_votos();
        }

        echo '</span>';

        //lapiz a gods y autores (si no han sobrepasado el limite de ediciones)
        if (($current_user->user_level == 'god') || ($this->id_autor ==$current_user->user_id && $this->ediciones <= $globals['ediciones_max_cortos'] && $this->activado))
        echo '<a href="'.$globals['base_url'].'editar_corto.php?id='.$this->id.'&editar=1" title="'._('Editar corto').'"><img class="icon edit img-flotante" src="'.get_cover_pixel().'" alt="'.('Editar corto').'"/></a>&nbsp;';


        //papelera solo para gods
        if ($current_user->user_level == 'god')
            echo '<a href="'.$globals['base_url'].'admin/cortos.php?admin=pendientes&amp;ezabatu='.$this->id.'&amp;key='.$key.'" title="'._('Eliminar').'"><img class="icon delete img-flotante" src="'.get_cover_pixel().'" alt="denegar" title="denegar el corto"/></a>';

        if ($this->activado){
            echo '</dd>';
            echo '</dl>';
        }


    }

}
