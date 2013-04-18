// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jon Arano <arano.jon@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function update_votes_counter(id){

	var votos= $('#votos-e-' + id).html()
	var votos_totales= $('#usuarios-totales-' + id).html()

	votos ++;
	votos_totales ++;

	$('#votos-e-'+id).html(votos);
	$('#usuarios-totales-'+id).html(votos_totales);

}

function update_comments_counter(id){

	var comentarios= $('#opiniones-' + id).html()

	comentarios ++;
	$('#opiniones-'+id).html(comentarios);
}

function ajax_poll_vote(id) {

	var httpreq =  new XMLHttpRequest();    
	var cuenta = document.getElementById("cuenta_"+id).value;
	var valores = new Array();
	var opciones = new Array();

	for (i=0; i < cuenta ; i++) {
		 valores[i] = document.getElementById("opcion_"+id+"["+i+"]").checked;
	}


	for (i=0; i < cuenta; i++) {
		 if (valores[i] == true) {
		 opciones[i] = document.getElementById("opcion_"+id+"["+i+"]").value; // si está votado mete el id de la opción en el array
		} else opciones[i] = 0;
		
	}

	if (httpreq) {
	
		httpreq.onreadystatechange=function() {
			if (httpreq.readyState == 4) {

				var serverResponse = httpreq.responseText;

				/* Sobreescribe sobre el div el texto de la respuesta */
				document.getElementById("pollvotes"+id).innerHTML = String(serverResponse);
			
				update_votes_counter(id);

				return true;
				
			}
		}

		httpreq.open('POST', '/ajax/poll_vote.php', true);
		httpreq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		httpreq.send('poll_id='+id+'&opciones='+opciones); 
	}
}

// Free software licensed under AGPL
// (c) David <neikokz at gmail dot com>

function setOpacity(opacity) {
	obj = document.getElementById("ajaxcomments");
	container = document.getElementById("ajaxcontainer");
	
	obj.style.filter = "alpha(opacity:"+opacity+")";
	obj.style.KHTMLOpacity = opacity/100;
	obj.style.MozOpacity = opacity/100;
	obj.style.opacity = opacity/100;
	container.style.height = (opacity*obj.offsetHeight/100)+10+"px";
}

function fadeIn(opacity) {
	if (opacity <= 100) {
		setOpacity(opacity);
		opacity += 50;
		window.setTimeout("fadeIn("+opacity+")", 25);
	}
}

function startFade() {
	setOpacity(document.getElementById("ajaxcomments"), 0);
	document.getElementById("ajaxcomments").style.visibility = 'visible';
	fadeIn(0);
}

function submit_comment() {
	if (document.getElementById("poll_content").value == "") {
		document.getElementById("error_com").innerHTML = "Comentario vacío";
		document.getElementById("spinner").className = 'ko';
		document.getElementById("poll_content").focus();
		return false;
	}
	
	var poll_content = document.getElementById("poll_content").value;
	var process = document.getElementById("process").value;
	var poll_id = document.getElementById("poll_id").value;
	var user_id = document.getElementById("user_id").value;
	

	var httpreq =  new XMLHttpRequest();    

	if (httpreq) {
		document.getElementById("submit_com").disabled = true;
		document.getElementById("poll_content").disabled = true;
		

		document.getElementById("spinner").className = 'loading';
		
		httpreq.onreadystatechange=function() {
			if (httpreq.readyState == 4) {
				var serverResponse = httpreq.responseText;

				if (String(serverResponse).substring(0, 3) == "OK:") {
					document.getElementById("spinner").className = 'ok';
					document.getElementById("poll_content").value = "";
					document.getElementById("ajaxcomments").innerHTML = String(serverResponse).substring(3);
					startFade();
					update_comments_counter(id);
					return true;
				} else {
					document.getElementById("spinner").className = 'ko';
					document.getElementById("submit_com").disabled = false;
					document.getElementById("plol_content").disabled = false;
					
					document.getElementById("error_com").innerHTML = String(serverResponse).substring(3);
					return false;
				}
			}
		}

		httpreq.open('POST', '/ajax/poll_com.php', true);
		httpreq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		httpreq.send('poll_content='+encodeURIComponent(poll_content)+'&process='+process+'&poll_id='+poll_id+'&user_id='+user_id); 
	}
}


function edit_comment(com_id) {
	if (document.getElementById('comment_'+com_id).value == "") {
		document.getElementById('error_com_'+com_id).innerHTML = "Comentario vacío";
		document.getElementById('spinner_'+com_id).className = 'spinner ko';
		document.getElementById('comment_'+com_id).focus();
		return false;
	}
	
	var comment_content = document.getElementById("comment_"+com_id).value;

	var poll_id = document.getElementById("poll_id_"+com_id).value;
	var user_id = document.getElementById("user_id_"+com_id).value;

	var httpreq =  new XMLHttpRequest();    
	if (httpreq) {
		document.getElementById("submit_com_"+com_id).disabled = true;
		document.getElementById("comment_"+com_id).disabled = true;
		
		document.getElementById("spinner_"+com_id).className = 'spinner loading';

		httpreq.onreadystatechange=function() {
			if (httpreq.readyState == 4) {
				var serverResponse = httpreq.responseText;

				if (String(serverResponse).substring(0, 3) == "OK:") {
					document.getElementById("spinner_"+com_id).className = 'spinner ok';
					document.getElementById("comment_"+com_id).value = "";
					document.getElementById("ccontainer-"+com_id).innerHTML = String(serverResponse).substring(3);
					return true;
				} else {
					document.getElementById("spinner_"+com_id).className = 'spinner ko';
					document.getElementById("submit_com_"+com_id).disabled = false;
					document.getElementById("comment_"+com_id).disabled = false;
					document.getElementById("error_com_"+com_id).innerHTML = String(serverResponse).substring(3);
					return false;
				}
			}
		}
		
		httpreq.open('POST', '/ajax/edit_poll_comment.php?id='+com_id, true);
		httpreq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
                httpreq.send('id='+com_id+'&poll_content='+encodeURIComponent(comment_content)+'&process=editcomment&poll_id='+poll_id+'&user_id='+user_id); 
		var serverResponse = httpreq.responseText;
	}
}

function edit_poll(id){

	var httpreq =  new XMLHttpRequest();    
	var process = document.getElementById("process-"+id).value;
		
	if (process == 'save_settings'){

		var cuenta = document.getElementById("cuenta"+id).value;
		var titulo = document.getElementById("titulo").value;
		var descripcion = document.getElementById("descripcion").value;

		var opciones = new Array();
		var ids = new Array();


		for (i=0; i < cuenta; i++) {
			ids[i] = document.getElementById("opcion"+i).value; 
						
		}

		for (i in ids) {
					
			 opciones[i] = document.getElementById("valor"+i).value; 

		
		}

	}

	if (httpreq) {
	
		httpreq.onreadystatechange=function() {
			if (httpreq.readyState == 4) {

				var serverResponse = httpreq.responseText;
										
				/* Sobreescribe sobre el div el texto de la respuesta */
				document.getElementById("editbox-"+id).innerHTML = String(serverResponse);
			
				if (process == 'save_settings') save_options(id, opciones, process, titulo, descripcion)
			
				return true;
				
			}
		}
		
		httpreq.open('POST', '/ajax/polls_utils.php', true);
		httpreq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		httpreq.send('poll_id='+id+'&process='+process); 
	}
}

function save_options(id, opciones, process, titulo, descripcion) {

	var httpreq =  new XMLHttpRequest();    
	

	if (httpreq) {
	
		httpreq.onreadystatechange=function() {
			if (httpreq.readyState == 4) {

				var serverResponse = httpreq.responseText;
				
				/* Sobreescribe sobre el div el texto de la respuesta */
				document.getElementById("pollvotes"+id).innerHTML = String(serverResponse);
			
				return true;
				
			}
		}
		
		httpreq.open('POST', '/ajax/polls_utils.php', true);
		httpreq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		httpreq.send('poll_id='+id+'&process='+process+'&opciones='+opciones+'&titulo='+titulo+'&descripcion='+descripcion); 
	}
}

function delete_poll(id) {

	var httpreq =  new XMLHttpRequest();    
	

	if (httpreq) {
	
		httpreq.onreadystatechange=function() {
			if (httpreq.readyState == 4) {

				var serverResponse = httpreq.responseText;
				
				document.getElementById("encuesta"+id).innerHTML = '';
			
				return true;
				
			}
		}
		
		httpreq.open('POST', '/ajax/poll_delete.php', true);
		httpreq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		httpreq.send('poll_id='+id); 
	}
}

function add_option(){

	var allParas = document.getElementsByTagName('dt');
	var num = allParas.length;
	var capa = document.getElementById("encuesta-opciones");
	var dt = document.createElement('dt');
	var dd = document.createElement('dd');

	if (num == 15 ) {
		alert('Has superado el número de opciones máximas');
		return true;
	}

	previous = parseInt(num)- 1;

	/* Quitar enlace de eliminar en la opción anterior */
	eliminar = document.getElementById('delete-'+previous);
	padre3 = eliminar.parentNode;
	padre3.removeChild(eliminar);

	original = num; // número de opciones actuales
		
	/* Añadir texto */
	dt.id = 'opcion-'+original;
	siguiente = parseInt(original) + parseInt(1);
	dt.innerHTML = 'opción '+ siguiente ;


	/* Añadir cuadro de texto */
	dd.id = 'opcion-t-'+original;
	dd.innerHTML = '<input type="text" value="" size="70" name="opts['+original+']" id="opts['+original+']"> <span id="delete-'+original+'"> <a href="javascript:delete_option('+original+')"> <img class="icon delete" alt="eliminar opción" title="eliminar opción"></a></span>';

	/* Crear objetos */
	capa.appendChild(dt);
	capa.appendChild(dd);


}

function delete_option(number){

	previous = parseInt(number)- 1;

	opcion = document.getElementById('opcion-'+number);
	cuadro = document.getElementById('opcion-t-'+number);
	contenido = document.getElementById('opts['+previous+']').value; // para no perder el valor de la opción anterior

	/* Eliminar texto y cuadro de texto de la opción */
	padre = opcion.parentNode;
	padre.removeChild(opcion);
	padre2 = cuadro.parentNode;
	padre2.removeChild(cuadro);

	/* Devolver enlace para eliminar la opción anterior */
	dd = document.getElementById("opcion-t-"+previous);
	dd.innerHTML = '<input type="text" value="'+contenido+'" size="70" name="opts['+previous+']" id="opts['+previous+']"><span id="delete-'+previous+'"><a href="javascript:delete_option('+previous+')" > <img class="icon delete" alt="eliminar opción" title="eliminar opción"></a></span>'; // añadimos el enlace de eliminar opción anterior

}
