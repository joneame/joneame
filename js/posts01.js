function post_load_form(c,a){var b=base_url+"backend/post_edit.php?id="+c;$.get(b,function(d){if(d.length>0){if(d.match(/^ERROR:/i)){alert(d)}else{$("#"+a).html(d)}reportAjaxStats("html","post_edit")}})}function nueva(){post_load_form(0,"addpost")}function respuesta(c,a){var d=0;var b=base_url+"backend/post_edit.php?id=0&reference="+a;$.get(b,function(e){if(e.length>0){if(e.match(/^ERROR:/i)){alert(e)}else{$("#respuesta-"+c).html(e)}reportAjaxStats("html","post_edit")}})}function editar(a){post_load_form(a,"pcontainer-"+a)}function responder(b,a){ref="@"+a+","+b+" ";textarea=$("#post");if(textarea.length==0){nueva()}post_add_form_text(ref,1)}function post_add_form_text(d,c){if(!c){c=1}textarea=$("#post");if(c<20&&textarea.length==0){c++;setTimeout('post_add_form_text("'+d+'", '+c+")",50);return false}if(textarea.length==0){return false}var b=new RegExp(d);var a=textarea.val();if(a.match(b)){return false}if(a.length>0&&a.charAt(a.length-1)!=" "){a=a+" "}textarea.val(a+d)}function hide_answers(a){div=document.getElementById("respuestas-"+a);div.style.display="none";$("#show-hide-"+a).html('<a align="right" href="javascript:show_answers('+a+')"> Mostrar</a><br/>')}function show_answers(a){div=document.getElementById("respuestas-"+a);div.style.display="";$("#show-hide-"+a).html('<a align="right" href="javascript:hide_answers('+a+')"> Ocultar</a><br/>')};