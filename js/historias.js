function link_show(b,a,d,f){var c=new XMLHttpRequest,e=document.getElementById("cargando").value;0==e&&(document.getElementById("spinner_h").className="loading");c&&(c.onreadystatechange=function(){if(4==c.readyState){var d=c.responseText;document.getElementById("contenido").innerHTML=String(d);document.getElementById("cargando").value=0;(1==a||2==a||8==a)&&$("a.fancybox").fancybox({transitionIn:"none",transitionOut:"none"});0==e&&(link_info(b),document.getElementById("spinner_h").className="blank");
return!0}},c.open("GET","/ajax/historias_ajax.php?link_id="+b+"&tab="+a+"&what="+d+"&server="+f,!0),c.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),c.send())}function link_info(b){var a=base_url+"ajax/link_info.php";$.getJSON(a+"?"+("id="+b),function(a){update_info(b,a)})}
function update_info(b,a){if(b!=a.id)return!1;votes_totales=parseInt(a.votes)+parseInt(a.anonymous)+parseInt(a.aleatorios_positivos);votes=parseInt(a.votes)+parseInt(a.anonymous);$("#a-votes-"+a.id).html()!=votes_totales&&($("#a-votes-"+a.id).hide(),$("#a-votes-"+a.id).html(votes_totales+""),$("#a-votes-"+a.id).fadeIn("slow"));$("#a-neg-"+a.id).html(a.negatives+"");$("#a-usu-"+a.id).html(a.votes+"");$("#a-ano-"+a.id).html(a.anonymous+"");$("#a-karma-"+a.id).html(a.karma+"")}
function smileys_list(){$("#smileylist").toggle(400)};