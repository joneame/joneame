// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function post_load_form(id, container) {
	var url = base_url + 'backend/post_edit.php?id='+id;
	$.get(url, function (html) {
			if (html.length > 0) {
				if (html.match(/^ERROR:/i)) {
					alert(html);
				} else {
					$('#'+container).html(html);
				}
				reportAjaxStats('html', 'post_edit');
			}
		});
}


function nueva() {
	//get_votes('post_edit.php','','addpost',0 , 0);
	post_load_form(0, 'addpost');
}

function respuesta(post_id,reference) {
	
	var id=0;
	var url = base_url + 'backend/post_edit.php?id=0&reference='+reference;
		
	$.get(url, function (html) {
			if (html.length > 0) {
				if (html.match(/^ERROR:/i)) {
					alert(html);
				} else {
					
					$('#respuesta-'+post_id).html(html);
					
					
				}
				reportAjaxStats('html', 'post_edit');
			}
		});
}


function editar(id) {
	//get_votes('post_edit.php', 'edit_post', 'pcontainer-'+id, 0, id);
	post_load_form(id, 'pcontainer-'+id);
}

function responder(id, user) {
	ref = '@' + user + ',' + id + ' ';
	textarea = $('#post');
	if (textarea.length == 0) {
		nueva();
	}
	post_add_form_text(ref, 1);
}

function post_add_form_text(text, tries) {
	if (! tries) tries = 1;
	textarea = $('#post');
	if (tries < 20 && textarea.length == 0) {
			tries++;
			setTimeout('post_add_form_text("'+text+'", '+tries+')', 50);
			return false;
	}
	if (textarea.length == 0 ) return false;
	var re = new RegExp(text);
	var oldtext = textarea.val();
	if (oldtext.match(re)) return false;
	if (oldtext.length > 0 && oldtext.charAt(oldtext.length-1) != ' ') oldtext = oldtext + ' ';
	textarea.val(oldtext + text);	
}

function hide_answers(id){

div = document.getElementById("respuestas-"+id);

div.style.display= 'none';

$('#show-hide-'+id).html('<a align="right" href="javascript:show_answers('+id+')"> Mostrar</a><br/>');

}

function show_answers(id){

div = document.getElementById("respuestas-"+id);

div.style.display= '';

$('#show-hide-'+id).html('<a align="right" href="javascript:hide_answers('+id+')"> Ocultar</a><br/>');

}

