// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function jonea(user, id, htmlid, md5) {
	var url = base_url + "backend/jonea.php";
	var content = "id=" + id + "&user=" + user + "&md5=" + md5;
	url = url + "?" + content;
	disable_vote_link(id, "espera...", '#adcee9');
	$.getJSON(url,  
		 function(data) {
				parseLinkAnswer(htmlid, data);
		}
	);
}

function joneo_aleatorio(user, id, htmlid, md5) {
	var url = base_url + "backend/aleatorio.php";
	var content = "id=" + id + "&user=" + user + "&md5=" + md5;
	url = url + "?" + content;
	disable_vote_link(id, "lloneandoo!", '#adcee9');
	$.getJSON(url,  
		 function(data) {
				parseLinkAnswer(htmlid, data);
				parsealeatorioLinkAnswer(htmlid,data); // cambia contadores de votos aleatorios
		}
	);
}
    
function karga_mezuak(userid, md5) {
	var url = base_url + "backend/mezuak.php";
	var content = "id=" + userid + "&md5=" +md5;
	url = url + "?" + content;
	
	$.getJSON(url,  
		 function(data) {
				ErantzunaProzesatu(userid, data);
		}
	);
	setTimeout('karga_mezuak("'+userid+'", "'+md5+'")', 40000);
}

function idatzi_mezuak(zer) {
	$('#mezuak').html('<span>'+zer+'</span>');
}

function ErantzunaProzesatu (id, link) {
	if (link.error || id != link.id) {
		idatzi_mezuak(".. error al actualizar los datos ..");
		alert("Error: "+link.error);
		return false;
	}
	idatzi_mezuak(link.mezuberri);
	return false;
}

/************************
 * Leer los mensajes
 ************************/
 
function irakurri_mezua(mezuid, userid, md5, mota, eg) {
	var url = base_url + "backend/mezuak_ikusi.php";
	var content = "id=" + userid + "&md5=" +md5 + "&mid=" +mezuid+"&mota=" +mota+"&eg=" +eg;
	url = url + "?" + content;

	
	$.ajax({
		url: url,
		dataType: "html",
		success: function(html) {
			idatzi_mezuguztia(html, mezuid);
		}
	});

}

 
function ezabatu_mezua(mezuid, userid, md5, mota) {
	var url = base_url + "backend/ezabatu_mezua.php";
	var content = "id=" + userid + "&md5=" +md5 + "&mid=" +mezuid+"&mota=" +mota;
	url = url + "?" + content;
	
	// Ocultamos
	document.getElementById('mezuak'+mezuid).style.display = 'none';
	
	$.ajax({
		url: url,
		dataType: "html",
		success: function(html) {
			$('#mezuak'+mezuid).html('no data');
		}
	});


}

function clk(f, id) {
	f.href=base_url + 'backend/go.php?id=' + id;
	return true;
}


function idatzi_mezuguztia(zer, id) {
	$('#mezuak'+id).html(zer);
}

function ProzesatuMezua(id, link, mezuid)
{
	if (link.error || id != link.id) {
		idatzi_mezuguztia(".. error al actualizar los datos ..", mezuid);
		alert("Error: "+link.error);
		return false;
	}
	idatzi_mezuguztia(link.mezuberri, mezuid);
	return false;
}

function votar_comentario(user, id, value) {
	var url = base_url + "backend/comentario_voto.php";
	var content = "id=" + id + "&user=" + user + "&value=" + value;
	var myid = 'comment-'+id;
	url = url + "?" + content;
	$.getJSON(url, 
		 function(data) {
			if (data.error) {
				alert("Error: "+data.error);
				return false;
			} else {
				$('#vc-'+id).html(data.votes+"");
				$('#vk-'+id).html(data.karma+"");
				if (data.image.length > 0) {
					$('#c-votes-'+id).html('<img style="background: url(\'/img/iconos/sprite.png\') '+data.image+' no-repeat;" src="/img/estructura/pixel.gif" width="12" height="8"/>');
				}
			}
		}
	);
}

function poll_comment_vote(user, id, value) {
	var url = base_url + "backend/poll_comment_vote.php";
	var content = "id=" + id + "&user=" + user + "&value=" + value;
	var myid = 'comment-'+id;
	url = url + "?" + content;
	$.getJSON(url, 
		 function(data) {
			if (data.error) {
				alert("Error: "+data.error);
				return false;
			} else {
				$('#vc-'+id).html(data.votes+"");
				$('#vk-'+id).html(data.karma+"");
				if (data.image.length > 0) {
					$('#c-votes-'+id).html('<img style="background: url(\'/img/iconos/sprite.png\') '+data.image+' no-repeat;" src="/img/estructura/pixel.gif" width="12" height="8"/>');
				}
			}
		}
	);
}

function votar_corto(user, id, value) {
	var url = base_url + "backend/corto_voto.php";
	var content = "id=" + id + "&user=" + user + "&value=" + value;
	var myid = 'comment-'+id;
	url = url + "?" + content;
	$.getJSON(url, 
		 function(data) {
			if (data.error) {
				alert("Error: "+data.error);
				return false;
			} else {
				$('#vc-'+id).html(data.votes+"");
				$('#vk-'+id).html(data.karma+"");
				if (data.image.length > 0) {
					$('#c-votes-'+id).html('<img style="background: url(\'/img/iconos/sprite.png\') '+data.image+' no-repeat;" src="/img/estructura/pixel.gif" width="12" height="8"/>');
				}
				
			}
		}
	);
}

function votar_notita(user, id, value) {
	var url = base_url + "backend/notita_voto.php";
	var content = "id=" + id + "&user=" + user + "&value=" + value;
	var myid = 'comment-'+id;
	url = url + "?" + content;
	$.getJSON(url,
		 function(data) {
			if (data.error) {
				alert("Error: "+data.error);
				return false;
			} else {
				$('#vc-'+id).html(data.votes+"");
				$('#vk-'+id).html(data.karma+"");
				if (data.image.length > 0) {
					$('#c-votes-'+id).html('<img style="background: url(\'/img/iconos/sprite.png\') '+data.image+' no-repeat;" src="/img/estructura/pixel.gif" width="12" height="8"/>');
				}
			}
		}
	);
}

function disable_vote_link(id, mess, background) {
	$('#a-va-' + id).html('<span>'+mess+'</span>');
	$('#a-va-' + id).css('background', background);
}

function parseLinkAnswer (id, link) {
	$('#problem-' + id).hide();
	if (link.error || id != link.id) {
		disable_vote_link(id, "vaya :-(", '#adcee9');
		alert("Error: "+link.error);
		return false;
	}
	votes = parseInt(link.votes)+parseInt(link.anonymous);
	if ($('#a-votes-' + link.id).html() != votes) {
		$('#a-votes-' + link.id).hide();
		$('#a-votes-' + link.id).html(votes+"");
		$('#a-votes-' + link.id).fadeIn('slow');
	}
	$('#a-neg-' + link.id).html(link.negatives+"");
	$('#a-usu-' + link.id).html(link.votes+"");
	$('#a-ano-' + link.id).html(link.anonymous+"");
	$('#a-karma-' + link.id).html(link.karma+"");
	
	if (link.value > 0) {
		if (link.aleatorio_valor == "no") {
			disable_vote_link(link.id, "&#161;biibaaa!", '#FFFFFF');
		} else {
			disable_vote_link(link.id, "valió " + link.aleatorio_valor, '#FFFFFF');
		}
	} else if (link.value < 0) {
		disable_vote_link(link.id, ":-(", '#FFFFFF');
	}
	return false;
}


function parsealeatorioLinkAnswer(id, link) {
	
	if (link.error || id != link.id) {
		disable_vote_link(id, "vaya :-(", '#adcee9');
		alert("Error: "+link.error);
		return false;
	}

	if (link.aleatorios_positivos == 0) color = '#aaa';
	else color = '';

	$('#a-ale_pos-' + link.id).html(link.aleatorios_positivos+"");

	document.getElementById('a-ale_pos-'+link.id).style.color = color;
	
	$('#a-ale_neg-' + link.id).html(link.aleatorios_negativos+"");

	if (link.aleatorios_negativos == 0) color = '';
	else color = 'red';
	document.getElementById('a-ale_neg-'+link.id).style.color = color;

	return false;
}
function securePasswordCheck(field) {
	/*La función comprueba si la clave contiene al menos
	 *ocho caracteres e incluye mayúsculas, minúsculas y números.
	 *
	 * Function checks if the password provided contains at least
	 * eight chars, including upper, lower and numbers.
	 *
	 * jotape - jplopez.net */

	if (field.value.length > 5 && field.value.match("^(?=.{6,})(?=(.*[a-z].*))(?=(.*[A-Z0-9].*)).*$", "g")) {
		if (field.value.match("^(?=.{8,})(?=(.*[a-z].*))(?=(.*[A-Z].*))(?=(.*[0-9].*)).*$", "g")) {
			field.className = "verde";
		} else {
			field.className = "amarillo";
		}
	} else {
		field.className = "rojo";
	}
	return false;
}

function checkEqualFields(field, against) {
	if(field.value == against.value) {
		field.className = "verde"
	} else {
		field.className = "rojo"
	}
	return false;
}

function enablebutton (button, button2, target) {
	var string = target.value;
	if (button2 != null) {
		button2.disabled = false;
	}
	if (string.length > 0) {
		button.disabled = false;
	} else {
		button.disabled = true;
	}
}

function checkfield (type, form, field) {
	var url = base_url + 'backend/checkfield.php?type='+type+'&name=' + encodeURIComponent(field.value);
	$.get(url,
		 function(html) {
			if (html == 'OK') {
				$('#'+type+'checkitvalue').html('<br/><span style="color:black">"' + encodeURI(field.value) + '": ' + html + '</span>');
				form.submit.disabled = '';
			} else {
				$('#'+type+'checkitvalue').html('<br/><span style="color:red">"' + encodeURI(field.value) + '": ' + html + '</span>');
				form.submit.disabled = 'disabled';
			}
		}
	);
	return false;
}

function check_checkfield(fieldname, mess) {
	field = document.getElementById(fieldname);
	if (field && !field.checked) {
		alert(mess);
		// box is not checked
		return false;
	}
}

function report_problem(frm, user, id, md5 /*id, code*/) {
	if (frm.ratings.value == 0)
		return;
	if (! confirm("¿Estás seguro de querer votar '" + frm.ratings.options[frm.ratings.selectedIndex].text +"'?") ) {
		frm.ratings.selectedIndex=0;
		return false;
	}
	var content = "id=" + id + "&user=" + user + "&md5=" + md5 + '&value=' +frm.ratings.value;
	var url=base_url + "backend/negativo.php?" + content;
	$.getJSON(url,
		 function(data) {
			parseLinkAnswer(id, data);
		}
	);
	return false;
}

// Get voters by Beldar <beldar.cat at gmail dot com>
// Generalized for other uses (gallir at gmail dot com)
function obtener(program,type,container,page,id) {
	var url = base_url + 'backend/'+program+'?id='+id+'&p='+page+'&type='+type;
	$('#'+container).load(url);
}

function modal_from_ajax(url, title) {
	if (typeof(title) == "undefined") title = '&nbsp';
	// !!!WARNING: this may (and will) break if you don't install jonéame at /
	//             because pixel.gif won't be found, so X button won't appear.
	//             change the path or just replace the image with a X if you 
	//             install jonéame with another base url! --neiKo
	$.modal('<div class="header" id="modalHeader" style="z-index: 10;display: block;" ><div id="modalTitle">'+title+'</div><a href="#" title="Cerrar" class="modalCloseX modalClose"><img src="/img/estructura/pixel.gif" width="16" height="16"/></a></div><div style="z-index: 10;display: block;" class="content" id="modalContent">Loading...</div>', {overlay: "50"});
	$.get(url, function(data){
	// create a modal dialog with the data
		$('#modalContent').html(data);
	});
}

// See http://www.shiningstar.net/articles/articles/javascript/dynamictextareacounter.asp?ID=AW
function textCounter(field,cntfield,maxlimit) {
	if (field.value.length > maxlimit)
	// if too long...trim it!
		field.value = field.value.substring(0, maxlimit);
	// otherwise, update 'characters left' counter
	else
		cntfield.value = maxlimit - field.value.length;
}


/**************************************
Tooltips functions
***************************************/
/**
  Stronglky modified, onky works with DOM2 compatible browsers.
  	Ricardo Galli
  From http://ljouanneau.com/softs/javascript/tooltip.php
 */


// create the tooltip object
function tooltip(){}

// setup properties of tooltip object
tooltip.id="tooltip";
tooltip.main=null;
tooltip.offsetx = 10;
tooltip.offsety = 10;
tooltip.shoffsetx = 8;
tooltip.shoffsety = 8;
tooltip.x = 0;
tooltip.y = 0;
tooltip.tooltipShadow=null;
tooltip.tooltipText=null;
tooltip.title_saved='';
tooltip.saveonmouseover=null;
tooltip.timeout = null;
tooltip.active = false;

tooltip.cache = new JSOC();

tooltip.ie = (document.all)? true:false;		// check if ie
if(tooltip.ie) tooltip.ie5 = (navigator.userAgent.indexOf('MSIE 5')>0);
else tooltip.ie5 = false;
tooltip.dom2 = ((document.getElementById) && !(tooltip.ie5))? true:false; // check the W3C DOM level2 compliance. ie4, ie5, ns4 are not dom level2 compliance !! grrrr >:-(




/**
* Open ToolTip. The title attribute of the htmlelement is the text of the tooltip
* Call this method on the mouseover event on your htmlelement
* ex :  <div id="myHtmlElement" onmouseover="tooltip.show(this)"...></div>
*/

tooltip.show = function (event, text) {
      // we save text of title attribute to avoid the showing of tooltip generated by browser
	if (this.dom2  == false ) return false;
	if (this.tooltipShadow == null) {
		this.tooltipShadow = document.createElement("div");
		this.tooltipShadow.setAttribute("id", "tooltip-shadow");
		document.body.appendChild(tooltip.tooltipShadow);

		this.tooltipText = document.createElement("div");
		this.tooltipText.setAttribute("id", "tooltip-text");
		document.body.appendChild(this.tooltipText);
	}
	this.saveonmouseover=document.onmousemove;
	document.onmousemove = this.mouseMove;
	this.mouseMove(event); // This already moves the div to the right position
	this.setText(text);
	this.tooltipText.style.visibility ="visible";
	this.tooltipShadow.style.visibility ="visible";
	this.active = true;
	return false;
}


tooltip.setText = function (text) {
	tooltip.tooltipShadow.style.width = 0+"px";
	tooltip.tooltipShadow.style.height = 0+"px";
	this.tooltipText.innerHTML=text;
	setTimeout('tooltip.setShadow()', 1);
	return false;
}

tooltip.setShadow = function () {
	tooltip.tooltipShadow.style.width = tooltip.tooltipText.clientWidth+"px";
	tooltip.tooltipShadow.style.height = tooltip.tooltipText.clientHeight+"px";
}


/**
* hide tooltip
* call this method on the mouseout event of the html element
* ex : <div id="myHtmlElement" ... onmouseout="tooltip.hide(this)"></div>
*/
tooltip.hide = function (event) {
	if (this.dom2  == false) return false;
	document.onmousemove=this.saveonmouseover;
	this.saveonmouseover=null;
	if (this.tooltipShadow != null ) {
		this.tooltipText.style.visibility = "hidden";
		this.tooltipShadow.style.visibility = "hidden";
		this.tooltipText.innerHTML='';
	}
	this.active = false;
}



// Moves the tooltip element
tooltip.mouseMove = function (e) {
   // we don't use "this", but tooltip because this method is assign to an event of document
   // and so is dreferenced

	if (tooltip.ie) {
		tooltip.x = event.clientX;
		tooltip.y = event.clientY;
	} else {
		tooltip.x = e.pageX;
		tooltip.y = e.pageY;
	}
	tooltip.moveTo( tooltip.x +tooltip.offsetx , tooltip.y + tooltip.offsety);
}

// Move the tooltip element
tooltip.moveTo = function (xL,yL) {
	if (this.ie) {
		xL +=  document.documentElement.scrollLeft;
		yL +=  document.documentElement.scrollTop;
	}
	if (this.tooltipText.clientWidth > 0  && document.documentElement.clientWidth > 0 && xL > document.documentElement.clientWidth * 0.55) {
		xL = xL - this.tooltipText.clientWidth - 2*this.offsetx;
	}
	this.tooltipText.style.left = xL +"px";
	this.tooltipText.style.top = yL +"px";
	xLS = xL + this.shoffsetx;
	yLS = yL + this.shoffsety;
	this.tooltipShadow.style.left = xLS +"px";
	this.tooltipShadow.style.top = yLS +"px";
}

// Show the content of a given comment
tooltip.c_show = function (event, type, element, link) {
      // we save text of title attribute to avoid the showing of tooltip generated by browser
	if (this.dom2  == false ) return false;
	if (element == 0 && link > 0) { // It's a #0  from a comment
		this.ajax_delayed(event,'get_link.php',link);
		return;
	}
	if (type == 'id') {
		
		this.ajax_delayed(event,'get_comment_tooltip.php',element+"&link="+link);
		return;
		
	} else if (type == 'order') {
		this.ajax_delayed(event,'get_comment_tooltip.php',element+"&link="+link);
		return;
	} else {
		text = element;
	}
	return this.show(event, text);
}

// Show the content of a given comment
tooltip.poll_c_show = function (event, type, element, link) {
      // we save text of title attribute to avoid the showing of tooltip generated by browser
	if (this.dom2  == false ) return false;
	if (element == 0 && link > 0) { // It's a #0  from a comment
		this.ajax_delayed(event,'get_encuesta.php',link);
		return;
	}
	if (type == 'id') {

		this.ajax_delayed(event,'get_opinion_tooltip.php',element+"&link="+link);
		return;
		
	} else if (type == 'order') {
		this.ajax_delayed(event,'get_opinion_tooltip.php',element+"&link="+link);
		return;
	} else {
		text = element;
	}
	return this.show(event, text);
}


tooltip.clear = function (event) {
	if (this.timeout != null) {
		clearTimeout(this.timeout);
		this.timeout = null;
	}
	this.hide(event);
}

tooltip.ajax_delayed = function (event, script, id, maxcache) {
	maxcache = maxcache || 600000; // 10 minutes in cache
	if (this.active) return false;
	if ((object = this.cache.get(script+id)) != undefined) {
		tooltip.show(event, object[script+id]);
	} else {
		this.show(event, 'cargando...'); // Translate this to your language: it's "loading..." ;-)
		this.timeout = setTimeout("tooltip.ajax_request('"+script+"', '"+id+"', "+maxcache+")", 100);
	}
}

tooltip.ajax_request = function(script, id, maxcache) {
	var url = base_url + 'backend/'+script+'?id='+id;
	tooltip.timeout = null;
	$.ajax({
		url: url,
		dataType: "html",
		success: function(html) {
			tooltip.cache.set(script+id, html, {'ttl':maxcache});
			tooltip.setText(html);
		}
	});
}

/************************
Simple format functions
**********************************/
/*
  Code from http://www.gamedev.net/community/forums/topic.asp?topic_id=400585
  strongly improved by Juan Pedro López for http://meneame.net
  2006/10/01, jotape @ http://jplopez.net
*/

function applyTag(id, tag) {
	obj = document.getElementById(id);
	if (obj) wrapText(obj, tag, tag);
}

function wrapText(obj, tag) {
	if(typeof obj.selectionStart == 'number') {
		// Mozilla, Opera and any other true browser
		var start = obj.selectionStart;
		var end   = obj.selectionEnd;

		if (start == end || end < start) return false;
		obj.value = obj.value.substring(0, start) +  replaceText(obj.value.substring(start, end), tag) + obj.value.substring(end, obj.value.length);
	} else if(document.selection) {
		// Damn Explorer
		// Checking we are processing textarea value
		obj.focus();
		var range = document.selection.createRange();
		if(range.parentElement() != obj) return false;
		if (range.text == "") return false;
		if(typeof range.text == 'string')
	        document.selection.createRange().text =  replaceText(range.text, tag);
	} else
		obj.value += text;
}

function replaceText(text, tag) {
		text = text.replace(/(^|\s)[\*_]([^\s]+)[\*_]/gm, '$1$2')
		text = text.replace(/([^\s]+)/gm, tag+"$1"+tag)
		return text;
}


// This function report the ajax request to stats trackers
// Only known how to do it with urchin/Google Analytics
// See http://www.google.com/support/analytics/bin/answer.py?answer=33985&topic=7292
function reportAjaxStats(page) {
	return; // Slow down
	if (window.urchinTracker) {
		urchinTracker(page+'.ajax'); 
	}
}

function bindTogglePlusMinus(img_id, link_id, container_id) {
	$(document).ready(function (){ 
		$('#'+link_id).bind('click',
			function() {
				if ($('#'+img_id).attr("src") == plus){
					$('#'+img_id).attr("src", minus);
				}else{
					$('#'+img_id).attr("src", plus);
				}
				$('#'+container_id).slideToggle("fast");
				return false;
			}
		);
	});
}

// puto wrapper feo
function abrirEditar(a,b,c,d,e) {
	get_ajax(a,b,c,d,e);
	document.getElementById("ajaxcontainer").style.height = 'auto';
	return;
}

function get_ajax(program,type,container,page,id) {
	var url = base_url + 'ajax/'+program+'?id='+id+'&p='+page+'&type='+type;
	$('#'+container).load(url);
}

function comment_reply(id) {
    ref = '#' + id + ' ';
    textarea = $('#comment');
    if (textarea.length == 0 ) return;
    var re = new RegExp(ref);
    var oldtext = textarea.val();
    if (oldtext.match(re)) return;
    if (oldtext.length > 0 && oldtext.charAt(oldtext.length-1) != "\n") oldtext = oldtext + "\n";
    textarea.val(oldtext + ref);
    textarea.get(0).focus();
}

function appySmiley(smiley){
	
	textarea = $('#comment');

	smiley = textarea.val()+' {'+smiley+'}';
	textarea.val(smiley);
        textarea.focus();
	textarea.select();
}

function cambiar_estado(id, accion){

//alert(elemento.text);

if (accion == 'formulario'){
	document.getElementById("link").OnClick = '';
	document.getElementById("link").innerHTML = '<input size="70" type="text" autocomplete="off" name="new_estado" id="new_estado" value="El texto irá aquí" /><a onclick="javascript:cambiar_estado(0, \'guardar\')" title="guardar"><img class="icon tick img-flotante" src="/img/estructura/pixel.gif" alt="guardar" title="guardar">';
}else {
	alert(document.getElementById("link").OnClick);
	//document.getElementById("link").OnClick = '';
	document.getElementById("link").innerHTML = 'actualizado';
	document.getElementById("link").OnClick = 'javascript:cambiar_estado('+id+', \'formulario\')';
	//alert(document.getElementById("link").OnClick);
	return false;
	}

}
