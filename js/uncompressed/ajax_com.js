// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// David Martí <neikokz@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

/* Update comments mess */
function update_comments(id){

    var comentarios= $('#n_comentarios-' + id).html()

    comentarios ++;

    if (comentarios == false) comentarios = 1;

    $('#n_comentarios-'+id).html(comentarios);

    if (comentarios == 1) $('#t_comentarios-'+id).html(' comentario');

}

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
    if (document.getElementById("comment").value == "") {
        document.getElementById("error_com").innerHTML = "Comentario vacío";
        document.getElementById("spinner").className = 'ko';
        document.getElementById("comment").focus();
        return false;
    }

    var comment_content = document.getElementById("comment").value;
    var process = document.getElementById("process").value;
    var randkey = document.getElementById("randkey").value;
    var link_id = document.getElementById("link_id").value;
    var user_id = document.getElementById("user_id").value;
    var as_admin = document.getElementById("comentario-admin").checked;
    var hide_nick = document.getElementById("comentario-especial").checked;

    var httpreq =  new XMLHttpRequest();
    if (httpreq) {
        document.getElementById("submit_com").disabled = true;
        document.getElementById("comment").disabled = true;
        document.getElementById("comentario-admin").disabled = true;
        document.getElementById("comentario-especial").disabled = true;

        document.getElementById("spinner").className = 'loading';

        httpreq.onreadystatechange=function() {
            if (httpreq.readyState == 4) {
                var serverResponse = httpreq.responseText;

                if (String(serverResponse).substring(0, 3) == "OK:") {
                    document.getElementById("spinner").className = 'ok';
                    document.getElementById("comment").value = "";
                    document.getElementById("ajaxcomments").innerHTML = String(serverResponse).substring(3);
                    update_comments(link_id);
                    startFade();
                    return true;
                } else {
                    document.getElementById("spinner").className = 'ko';
                    document.getElementById("submit_com").disabled = false;
                    document.getElementById("comment").disabled = false;
                    document.getElementById("comentario-admin").disabled = false;
                    document.getElementById("comentario-especial").disabled = false;
                    document.getElementById("error_com").innerHTML = String(serverResponse).substring(3);
                    return false;
                }
            }
        }

        httpreq.open('POST', '/ajax/new_comment.php', true);
        httpreq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        httpreq.send('comment_content='+encodeURIComponent(comment_content)+'&process='+process+'&randkey='+randkey+'&link_id='+link_id+'&user_id='+user_id+'&type='+as_admin+'&especial='+hide_nick);
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
    var randkey = document.getElementById("key_"+com_id).value;
    var link_id = document.getElementById("link_id_"+com_id).value;
    var user_id = document.getElementById("user_id_"+com_id).value;
    var as_admin = document.getElementById("comentario-admin_"+com_id).checked;
    var hide_nick = document.getElementById("comentario-especial_"+com_id).checked;

    var httpreq =  new XMLHttpRequest();
    if (httpreq) {
        document.getElementById("submit_com_"+com_id).disabled = true;
        document.getElementById("comment_"+com_id).disabled = true;
        document.getElementById("comentario-admin_"+com_id).disabled = true;
        document.getElementById("comentario-especial_"+com_id).disabled = true;

        document.getElementById("spinner_"+com_id).className = 'spinner loading';

        httpreq.onreadystatechange=function() {
            if (httpreq.readyState == 4) {
                var serverResponse = httpreq.responseText;
                //alert((String(serverResponse).substring()));
                if (String(serverResponse).substring(0, 3) == "OK:") {
                    document.getElementById("spinner_"+com_id).className = 'spinner ok';
                    document.getElementById("comment_"+com_id).value = "";
                    document.getElementById("ccontainer-"+com_id).innerHTML = String(serverResponse).substring(3);
                    return true;
                } else {
                    document.getElementById("spinner_"+com_id).className = 'spinner ko';
                    document.getElementById("submit_com_"+com_id).disabled = false;
                    document.getElementById("comment_"+com_id).disabled = false;
                    document.getElementById("comentario-admin_"+com_id).disabled = false;
                    document.getElementById("comentario-especial_"+com_id).disabled = false;
                    document.getElementById("error_com_"+com_id).innerHTML = String(serverResponse).substring(3);
                    return false;
                }
            }
        }

        httpreq.open('POST', '/ajax/edit_comment.php?id='+com_id, true);
        httpreq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        httpreq.send('id='+com_id+'&comment_content='+encodeURIComponent(comment_content)+'&process=editcomment&key='+randkey+'&link_id='+link_id+'&user_id='+user_id+'&type='+as_admin+'&especial='+hide_nick);
        var serverResponse = httpreq.responseText;
    }
}
