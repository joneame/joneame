// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jon Arano <arano.jon@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function link_show(id, tab, what, server) {

    var httpreq =  new XMLHttpRequest();
    var tab;
    var cargando = document.getElementById("cargando").value;

    /* No lo pone si la página no ha acabado de generarse */
    if (cargando == 0) document.getElementById("spinner_h").className = 'loading';

    if (httpreq) {

        httpreq.onreadystatechange=function() {
            if (httpreq.readyState == 4) {

                var serverResponse = httpreq.responseText;

                /* Sobreescribe sobre el div el texto de la respuesta */
                document.getElementById("contenido").innerHTML = String(serverResponse);

                /* Ahora está generada */
                document.getElementById("cargando").value = 0;

                /* Para las ventanas modales */
                if (tab==1 || tab ==2 || tab == 8)
                    $("a.fancybox").fancybox({transitionIn: "none", transitionOut: "none"});


                    if (cargando == 0) {
                    link_info(id);
                    document.getElementById("spinner_h").className = 'blank';
                }

                return true;

            }
        }

        httpreq.open('GET', '/ajax/historias_ajax.php?link_id='+id+'&tab='+tab+'&what='+what+'&server='+server, true);
        httpreq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        httpreq.send();
    }
}

function link_info(id)
{
    var url = base_url + 'ajax/link_info.php';
    var content = 'id=' + id;
    url = url + '?' + content;

    $.getJSON(url,
         function(data) {
                update_info(id, data);
        }
    );

}

function update_info(id, link){

    if (id != link.id) {
        return false;
    }

    votes_totales = parseInt(link.votes)+parseInt(link.anonymous)+parseInt(link.aleatorios_positivos);
    votes = parseInt(link.votes)+parseInt(link.anonymous);
    if ($('#a-votes-' + link.id).html() != votes_totales) {
        $('#a-votes-' + link.id).hide();
        $('#a-votes-' + link.id).html(votes_totales+"");
        $('#a-votes-' + link.id).fadeIn('slow');
    }

    $('#a-neg-' + link.id).html(link.negatives+"");
    $('#a-usu-' + link.id).html(link.votes+"");
    $('#a-ano-' + link.id).html(link.anonymous+"");
    $('#a-karma-' + link.id).html(link.karma+"");
}

function smileys_list(){
    $('#smileylist').toggle(400);
}
