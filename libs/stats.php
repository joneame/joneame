<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function do_admins() {
        global $db, $current_user;

        if (! $current_user->admin) return false;

        foreach (array('god', 'admin', 'devel') as $level) {
                $res = $db->get_results("select user_login, user_login_register from users where user_level = '$level' order by user_login asc");
                if ($res) {
                        $comment .= "<strong>$level</strong>: ";
                        $users = array();
                        foreach ($res as $user) {
                                if ($user->user_login == $user->user_login_register) {
                                        $users[] = $user->user_login;
                                } else {
                                        $users[] = sprintf('%s (<em>%s</em>)', $user->user_login, $user->user_login_register);
                                }
                        }
                        $comment .= implode(', ', $users);
                }
                $comment .= "<br>";
        }
        return $comment;
}

function do_last() {
        global $db, $current_user;

        if (! $current_user->admin) return false;
        $list = '<strong>Últimos registrados</strong><br/>';
        $res = $db->get_results("select user_login, user_avatar, user_date, user_email, user_ip from users where user_level != 'disabled' AND user_validated_date IS NOT NULL order by user_id desc limit 20");
        
        if ($res) {
            $list .= '<table>';
            $list .= '<tr><th>nick</th><th>registrado</th><th>correo</th><th>dirección ip</th></tr>';
            foreach ($res as $user) {
                $list .= sprintf('<tr><td><img src="%s" /> <a href="%s">%s</a></td><td>%s</td><td>%s</td><td>%s</td></tr>',
                    get_avatar_url($user->user_id, $user->user_avatar, 20),
                    get_user_uri($user->user_login),
                    $user->user_login,
                    $user->user_date,
                    $user->user_email,
                    $user->user_ip);
            }
            $list .= '</table>';
        }

        return $list;
}


function do_stats1() {
	global $db;
	$comment = '<strong>'._('Estadísticas globales'). '</strong>. ';
	$comment .= _('usuarios activos') . ':&nbsp;' . $db->get_var("select count(*) from users where user_level != 'disabled'") . ', ';
	$votes = (int) $db->get_var('select count(*) from votes') + (int) $db->get_var('select sum(votes_count) from votes_summary');
	$comment .= _('votos') . ':&nbsp;' . $votes . ', ';
	$comment .= _('artículos') . ':&nbsp;' . $db->get_var('select count(*) from links') . ', ';
	$comment .= _('publicados') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="published"') . ', ';
	$comment .= _('pendientes') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="queued"') . ', ';
	$comment .= _('descartados') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="discard"') . ', ';
	$comment .= _('comentarios') . ':&nbsp;' . $db->get_var('select count(*) from comments');
	return $comment;
}

function do_stats2($hours) {
	global $db;
		
	if (!$hours) $hours = 24;

	$comment = '<strong>'._('Estadísticas')." $hours ";
	if ($hours > 1) $comment .= _('horas');
	else $comment .= _('hora');
	$comment .= '</strong>. ';

	$comment .= _('votos') . ':&nbsp;' . $db->get_var("select count(*) from votes where vote_type='links' and vote_date > date_sub(now(), interval $hours hour)") . ', ';
	$comment .= _('votos comentarios') . ':&nbsp;' . $db->get_var("select count(*) from votes where vote_type='comments' and vote_date > date_sub(now(), interval $hours hour)") . ', ';
	$comment .= _('votos notas') . ':&nbsp;' . $db->get_var("select count(*) from votes where vote_type='posts' and vote_date > date_sub(now(), interval $hours hour)") . ', ';
	$comment .= _('artículos') . ':&nbsp;' . $db->get_var("select count(*) from links where link_date > date_sub(now(), interval $hours hour)") . ', ';
	$comment .= _('publicados') . ':&nbsp;' . $db->get_var("select count(*) from links where link_status='published' and link_date > date_sub(now(), interval $hours hour)") . ', ';
	$comment .= _('descartados') . ':&nbsp;' . $db->get_var("select count(*) from links where link_status='discard' and link_date > date_sub(now(), interval $hours hour)") . ', ';
	$comment .= _('comentarios') . ':&nbsp;' . $db->get_var("select count(*) from logs where log_type = 'comment_new' and log_date > date_sub(now(), interval $hours hour)")  . ', ';
	$comment .= _('notas') . ':&nbsp;' . $db->get_var("select count(*) from logs where log_type = 'post_new' and log_date > date_sub(now(), interval $hours hour)")  . ', ';
	$comment .= _('usuarios nuevos') . ':&nbsp;' . $db->get_var("select count(*) from logs, users where log_type = 'user_new' and log_date > date_sub(now(), interval $hours hour) and user_id = log_ref_id and user_validated_date is not null");
	return $comment;
}


function do_statsu() {
	global $db, $current_user;
	require_once(mnminclude.'user.php');

	
	$user_id = $current_user->user_id;
	$user_login = $current_user->user_login;
	
	$user = new User();
	$user->id = $user_id;
	$user->read();
	$user->all_stats();
	
	$comment = '<strong>'._('Estadísticas de'). ' ' . $user_login. '</strong>. ';
	$comment .= _('carisma') . ':&nbsp;' . $user->karma . ', ';

	if ($user->total_links > 1) {
		$comment .= _('entropía') . ':&nbsp;' . intval(($user->blogs() - 1) / ($user->total_links - 1) * 100) . '%, ';
	}
	$comment .= _('votos') . ':&nbsp;' . $user->total_votes . ', ';
	$comment .= _('artículos') . ':&nbsp;' . $user->total_links . ', ';
	$comment .= _('publicados') . ':&nbsp;' . $user->published_links . ', ';
	$comment .= _('pendientes') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="queued" and link_author='.$user_id) . ', ';
	$comment .= _('descartados') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="discard" and link_author='.$user_id) . ', ';
	$comment .= _('comentarios') . ':&nbsp;' . $user->total_comments;
	return $comment;
}