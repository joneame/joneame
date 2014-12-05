<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// David Martí <neikokz at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

// Sistema de ayuda de Jonéame. La idea chachipiruli sería leer los textos de ayuda de
// un txt. Pero de todos modos esto tampoco tiene mucho sentido, ya que subir un php y
// un txt supone prácticamente el mismo esfuerzo :-)
// Todo esto está aquí como reemplazo de la abandonada wiki.

include('config.php');
include(mnminclude.'html1.php');
$globals['noindex'] = true;

do_header(_('Ayuda | Jonéame'));

ayuda_tabs();

echo '<div class="ayuda-contenido">';

if ($_REQUEST['id'] == 'faq') {
    echo '<h2>FAQ: preguntas más frecuentes</h2>';
    echo '<h3>¿Cómo promover las historias?</h3>';
    echo '<p>Selecciona la opción <a href="'.$globals['base_url'].'jonealas.php">pendientes</a> y te aparecerán las noticias no publicadas, ordenadas descendentemente por fecha de envío. Sólo tienes que "jonear" aquellas que más gracia te hagan, más te gusten, o más curiosas consideres. Una vez superado unos umbrales de votos y carisma serán promovidas a la página principal.</p>';
    echo '<p>No te olvides de leer las <a href="'.$globals['base_url'].'ayuda.php?id=uso">condiciones de uso</a>.</p>';
    echo '<h3>¿Qué es ese desplegable llamado "sensurar" que me aparece cuando voy a jonear noticias pendientes?</h3>';
    echo '<p>Es un formulario para indicar que una noticia es repetida o inadecuada. Dichos reportes son votos negativos a la noticia, no abuses de él. Los envíos que reúnan muchos votos negativos serán movidos a la cola de descartadas. Por último puedes votar mafia si te apetece, ¡para eso somos la mafia! (Pero no te pases '.put_smileys('{wink}').')</p>';
    echo '<h3>¿Qué es eso de cortos en la parte superior izquierda de la página?</h3>';
    echo '<p>La sección cortos es única y exclusiva de Jonéame. Es un formulario de envío de frases inteligentes, que después serán revisadas por un administrador. Dicho administrador aceptará o rechazará tu propuesta (no todo vale, debes seguir las instrucciones). Una vez aceptado, tu corto irá apareciendo en la parte superior de la página, mezclado con los enviados por los otros usuarios de Jonéame.</p>';
    echo '<h3>¿Cómo envío historias?</h3>';
    if ($current_user->user_id > 0) {
        echo '<p>Selecciona <a href="'.$globals['base_url'].'nueva_historia.php">enviar nueva historia</a>. En un proceso de tres pasos simples la historia será enviada a <a href="'.$globals['base_url'].'jonealas.php">la cola de pendientes</a>.</p>';
    } else {
        echo '<p>Debes <a href="'.$globals['base_url'].'register.php">registrarte</a> antes, es muy fácil y rápido. Luego seleccionas <a href="'.$globals['base_url'].'nueva_historia.php">enviar nueva historia</a>. En un proceso de tres pasos simples la historia será enviada a <a href="'.$globals['base_url'].'jonealas.php">la cola de pendientes</a>.</p>';
    }
    echo '<h3>¿Se responsabiliza Jonéame de los comentarios escritos por los usuarios?</h3>';
    echo '<p>NO. Cada uno puede opinar lo que quiera y sobre lo que quiera, ergo no nos hacemos responsables. Los administradores no revisan los comentarios antes de ser publicados, por lo que la responsabilidad recae sobre el usuario que ha puesto el comentario. Por favor, sé cuidadoso con lo que escribes.</p>';
    echo '<h3>¿Qué tipos de historias puedo enviar?</h3>';
    echo '<p>Las que tú desees, pero sigue leyendo. Estarán sujetas a la revisión de los lectores que las votarán, o no. Aún así, el objetivo principal es que se traten de noticias y apuntes de blogs. Lo que no debes hacer es spam, es decir enviar muchos enlaces de unas pocas fuentes. Intenta ser variado. Envía historias que puedan ser interesantes para muchos, intenta pasar un cromo interesante. No mires sólo tu ombligo, usa el sentido común y un mínimo de espíritu colaborativo y respeto hacia los demás.</p>';
    echo '<h3>¿Qué tipos de historias debería enviar?</h3>';
    echo '<p>En Jonéame puedes enviar lo que quieras pero, intenta que los envíos que hagas sean originales y divertidos y evita enviar enlaces con contenido que pueda resultar desagrable para el resto de usuarios.</p>';
    echo '<h3>No puedo votar negativo las noticias ni/o los comentarios</h3>';
    echo '<p>Hace falta un carisma mínimo para votar negativo las noticias ('.$globals['min_karma_for_negatives'].') y otro para cualquier voto a los comentarios ('.$globals['min_karma_for_comment_votes'].').</p>';
    echo '<h3>¿Cómo se seleccionan las noticias que se publican en la portada?</h3>';
    echo '<p>Lo hace un proceso que se ejecuta cada cinco minutos.</p>';
    echo '<p>Primero calcula cuál es el carisma mínimo que tienen que tener las noticias. Este valor depende de la media del carisma de las noticias que fueron promovidas en las últimas dos semanas, más un coeficiente que depende del tiempo transcurrido desde la publicación de la última noticia. Este coeficiente decrece a medida que pasa el tiempo y se hace uno (1) cuando ha pasado una hora. Eso quiere decir que pasada una hora, cuando el coeficiente se hizo uno, cualquier noticia que tenga un carisma igual o superior a la media será promovida. Esto tiene dos objetivos, por un lado se persigue que si la calidad es constante se promoverá una media de una noticia por hora, pero las que reciban más votos (se espera que sea incremental) serán publicadas antes.</p>';
    echo '<p>El carisma de cada noticia se calcula multiplicando el número de votos por el carisma del autor del voto. Si es anónimo ese voto vale cuatro (4), si es de un usuario registrado el valor es multiplicado por su carisma (si es mayor a 22, el valor se limita a ese número).</p>';
    echo '<p>Finalmente hay una restricción adicional para evitar abusos de los usuarios registrados: sólo pueden ser promovidas aquellas noticias que al menos tengan N votos, donde N actualmente es siete (7).</p>'; // !!TODO
    echo '<h3>¿Qué es esa pestaña "descartadas" en la página de votación de pendientes?</h3>';
    echo '<p>Cuando una noticia recibe más votos negativos (sensuras) que votos positivos, es movida a esta cola. Los usuarios pueden seguir votando y si consigue los votos suficientes volverá a la cola de pendientes normal.</p>';
    echo '<h3>¿Qué son las notitas?</h3>';
    echo '<p>Una herramienta de comunicación entre los usuarios de Jonéame que se organiza en pequeños apuntes, como los mini-post de un blog colectivo (de todos los usuarios de las notitas) y a la vez individual. Puedes usarlo para cuestiones relacionadas con Jonéame o para explicar lo que quieras.</p>';
    echo '<h3>¿Qué es la cotillona?</h3>';
    echo '<p>La cotillona muestra lo que sucede en Jonéame en tiempo real. Si eres usuario registrado también puedes usarla para chatear o ponerte en contacto con los administradores.</p>';
    echo '<a name="jabber"></a>';
    echo '<h3>¿Cómo escribo en la cotillona desde Jabber/GTalk?</h3>';
    echo '<p>Para escribir desde Jabber, asegúrate de haber establecido correctamente el campo <b>jabber/gtalk para la coti</b> en <a href="'.$globals['base_url'].'profile.php">la configuración de tu perfil</a>. Una vez hecho esto, debes añadir el siguiente contacto a tu lista:</p>';
    echo '<ul><li><b>cotillona@joneame.net</b></li></ul>';
    echo '<h3>¿Dónde notifico errores, problemas o sugerencias?</h3>';
    echo '<p>Puedes notificarnos en una notita o mediante un mail a ad<em></em>min&#64;jon<strong></strong>eame&#46;ne<strong></strong>t. Si es un problema de seguridad, te rogamos que uses el mail.</p>';
} elseif ($_REQUEST['id'] == 'emoticonos') {
    echo '<h2>Lista de emoticonos</h2>';
    echo '<table>';
    echo '<tr><th>Emoticono</th><th class="vertical">Resultado</th><th>Emoticono</th><th class="vertical">Resultado</th><th>Emoticono</th><th>Resultado</th></tr>';
    echo '<tr><td>:) :-)</td><td class="vertical">'.put_smileys('{smiley}').'</td><td>;) ;-)</td><td class="vertical">'.put_smileys('{wink}').'</td><td>:> :-></td><td>'.put_smileys('{wink}').'</td></tr>';
    echo '<tr><td>:D :-D :grin:</td><td class="vertical">'.put_smileys('{grin}').'</td><td>&lt;:( &lt;:-( :oops:</td><td class="vertical">'.put_smileys('{oops}').'</td><td>:O :-O</td><td>'.put_smileys('{shocked}').'</td></tr>';
    echo '<tr><td>&gt;&#58;(</td><td class="vertical">'.put_smileys('{angry}').'</td><td>?(</td><td class="vertical">'.put_smileys('{huh}').'</td><td>:-S :S</td><td>'.put_smileys('{confused}').'</td></tr>';
    echo '<tr><td>8) 8-) 8D 8-D</td><td class="vertical">'.put_smileys('{cool}').'</td><td>:roll:</td><td class="vertical">'.put_smileys('{roll}').'</td><td>:\'( :\'-( :cry:</td><td>'.put_smileys('{cry}').'</td></tr>';
    echo '<tr><td>:x :-x</td><td class="vertical">'.put_smileys('{lipssealed}').'</td><td>:/ :-/</td><td class="vertical">'.put_smileys('{undecided}').'</td><td>:* :-*</td><td>'.put_smileys('{kiss}').'</td></tr>';
    echo '<tr><td>xD :lol:</td><td class="vertical">'.put_smileys('{lol}').'</td><td>:| :-|</td><td class="vertical">'.put_smileys('{blank}').'</td><td>:ffu:</td><td>'.put_smileys('{ffu}').'</td></tr>';
    echo '<tr><td>:8: (8)</td><td class="vertical">'.put_smileys('{music}').'</td><td>:roto2:</td><td class="vertical">'.put_smileys('{roto}').'</td><td>:gaydude:</td><td>'.put_smileys('{gaydude}').'</td></tr>';
    echo '<tr><td>:palm:</td><td class="vertical">'.put_smileys('{palm}').'</td><td>:goat: :goatse:</td><td class="vertical">'.put_smileys('{goatse}').'</td><td>o_o :wow:</td><td>'.put_smileys('{wow}').'</td></tr>';
    echo '<tr><td>¬¬ :shame:</td><td class="vertical">'.put_smileys('{shame}').'</td><td>:sisi1:</td><td class="vertical">'.put_smileys('{sisi}').'</td><td>:nusenuse:</td><td>'.put_smileys('{nuse}').'</td></tr>';
    echo '<tr><td>:P :-P</td><td class="vertical">'.put_smileys('{tongue}').'</td><td>:awesome:</td><td class="vertical">'.put_smileys('{awesome}').'</td><td>:alone:</td><td>'.put_smileys('{alone}').'</td></tr>';
    echo '<tr><td>:trollface:</td><td class="vertical">'.put_smileys('{trollface}').'</td><td>:troll:</td><td class="vertical">'.put_smileys('{troll}').'</td><td>:yeah: :fuckyeah:</td><td>'.put_smileys('{yeah}').'</td></tr>';
        echo '<tr><td>:clint:</td><td class="vertical">'.put_smileys('{clint}').'</td><td>:yaoface:</td><td class="vertical">'.put_smileys('{yaoface}').'</td><td>:longcat:</td><td>'.put_smileys('{longcat}').'</td></tr>';
    echo '<tr><td>:cejas:</td><td class="vertical">'.put_smileys('{cejas}').'</td><td>:sisi3:</td><td class="vertical">'.put_smileys('{sisitres}').'</td></tr>';
    //echo '<tr><td></td><td class="vertical"></td><td></td><td  class="vertical"></td><td></td><td></td></tr>';
    echo '</table>';
} elseif ($_REQUEST['id'] == 'legal') {
    echo '<h2>Información legal bajo el dominio joneame.net</h2>';
    echo '<h3>Sobre los datos de los usuarios (LOPD)</h3>';
    echo '<p>El titular podrá ejercitar (si lo desea) los derechos reconocidos en la LOPD sobre este fichero, siempre y cuando se trate de información PRIVADA. Los usuarios pueden realizar estas acciones enviando una solicitud a admin&#64;joneame&#46;net. </p><p>El propio usuario podrá dar de baja su cuenta de usuario desde el sitio web en el momento que así lo desee, sin necesidad de enviar ningún email a la administración.</p>';
    echo '<p>Las empresas que llevan las estadísticas de acceso a joneame.net (Google), podrían usar <em>cookies</em> con fines estadísticos. Los usuarios pueden eliminarlos o impedir el envío de esos <em>cookies</em> desde las opciones de su navegador.</p>';

    echo '<h3>Información pública y privada</h3>';
    echo '<p>Los ficheros de joneame.net contienen:</p>';
    echo '<p><strong>Información privada:</strong> IP utilizada para cualquier actividad en joneame.net <em>(votos, encuestas, historias o comentarios)</em>. <em>Los gestores de joneame.net</em> conservan estos datos con el objetivo de mantener la coherencia de toda la actividad realizada en el sitio. </p>';
    echo '<p><strong>Información pública: </strong> Toda actividad (anteriormente descrita) realizada voluntariamente por el propio usuario en el sitio web.</p>';
    echo '<h3>Sobre la información privada</h3>';
    echo '<p> joneame.net elimina la IP de todos los votos pasados 2 meses de la emisión del mismo, y, si se diera el caso de la deshabilitación de una cuenta de usuario <em>(por el propio usuario, o por incumplir las normas de uso)</em>, su email de registro será eliminado, pasados 2 meses de la deshabilitación de la misma. En el caso de la IP de comentarios o historias, se mantiene (y no será eliminada) por si fuera necesaria en un futuro, y se entregará, o borrará, sólo si un juez así lo requiere.</p>';

    echo '<h3>Exclusión de garantías y responsabilidad</h3>';
    echo '<p><em>Los administradores y propietarios de joneame.net</em> no garantizan la licitud, fiabilidad, exactitud, exhaustividad, actualidad y utilidad de los contenidos.</p>';
    echo '<p>El establecimiento de un hiperenlace, enlace, intercambio, no implica en ningún caso la existencia de relaciones entre <em>los administradores de joneame.net</em> y el propietario del lugar web con la que se establezca, ni la aceptación y aprobación de sus contenidos o servicios.</p>';
    echo '<p><em>Los administradores de joneame.net</em> excluyen toda responsabilidad en los sitios enlazados desde esta web y no pueden controlar y no controlan que entre ellos aparezcan sitios de Internet cuyos contenidos puedan resultar ilícitos, ilegales, contrarios a la moral o a las buenas costumbres o inapropiados. El usuario, por tanto, debe extremar la prudencia en la valoración y utilización de la información, contenidos y servicios existentes en los sitios enlazados.</p>';
    echo '<p><em>Los administradores de joneame.net</em> excluyen toda responsabilidad por las noticias e informaciones publicadas por los usuarios, terceros y de las mismas serán responsables los usuarios o terceros de quienes procedan.</p>';
    echo '<a name="contacto"></a>';
    echo '<h3>Contacto</h3>';
    echo '<p><strong>Contacto por correo electrónico:</strong> ad<em></em>min&#64;jon<strong></strong>eame&#46;ne<strong></strong>t</p>';
} elseif ($_REQUEST['id'] == 'uso') {
    echo '<h2>Condiciones de uso de Jonéame</h2>';
    echo '<h3>Envío de enlaces</h3>';
    echo '<p>Toda noticia debe reflejar, aunque sólo sea parcialmente, el contenido del enlace.</p>';
    echo '<p>Las etiquetas de las historias deberán ser las correctas para facilitar su posterior búsqueda por los demás mafiosos.</p>';
    echo '<p>Las historias con contenido pornográfico (NSFW) y para adultos (+18) deberán ser marcadas como tal en el momento de su envío. Asimismo, las noticias que no lo precisen no se marcarán como tal, para poder diferenciar las que realmente contengan contenido pornográfico de las que no.</p>';
    echo '<p>El usuario se abstendrá de escribir y enviar enlaces difamatorios, racistas, obscenos, ofensivos, que promuevan el odio racial étnico o religioso, de violencia explícita o incitación a la violencia, que afecten a la privacidad y/o derechos de la infancia.</p>';
    echo '<h3>Ilegalidad</h3>';
    echo '<p>El usuario se abstendrá de utilizar cualquiera de los servicios ofrecidos en joneame.net con fines o efectos ilícitos, lesivos de los derechos e intereses de terceros, o que puedan dañar, inutilizar, sobrecargar, deteriorar o impedir la normal utilización de los servicios, los equipos informáticos o los documentos, archivos y cualquier contenido almacenado en joneame.net o servidores externos enlazados desde joneame.net.</p>';
    echo '<h3>Cuentas de usuario</h3>';
    echo '<p>El usuario se abstendrá de usar Jonéame con el objetivo de:</p>';
    echo '<ol><li>La promoción exclusiva de un sitio web, empresas, redes de blogs o de afiliación de enlaces (spam)</li><li>Las campañas comerciales (aunque el lugar promocionado no contenga publicidad directa), políticas o ideológicas promoviendo el voto masivo a las noticias objeto de la campaña o del lugar promocionado.</li><li>La provocación gratuita o molestia injustificada a los demás usuarios y lectores de Jonéame.</li></ol>';
    echo '<p>El usuario se abstendrá de crear múltiples cuentas con el fin de promocionar sitios webs, participar en discusiones simulando las opiniones de personas distintas (astroturfing), suplantar la identidad de otras personas o intentar alterar artificialmente los contadores de votos y carisma y crear múltiples usuarios con el único objetivo de eludir las restricciones y penalizaciones generales del sistema.</p>';
    echo '<h3>Convivencia en la comunidad</h3>';
    echo '<p>El usuario se abstendrá de acosar, amenazar y obtener o divulgar información privada de otros usuarios de Jonéame. Esto es aplicable también en el uso de mensajes privados.</p>';
    echo '<p>El usuario se abstendrá de usar cualquier titular, entradilla, notita o comentario refiriéndose a un usuario de forma ofensiva o difamatoria con una obvia intención de molestarle.</p>';
    echo '<h3>Incumplimiento de las mismas</h3>';
    echo '<p>El incumplimiento de las condiciones de uso podría significar el bloqueo de la cuenta de usuario y/o dominio web, el borrado y/o edición del texto ofensivo, y las medidas legales adecuadas según las leyes españolas y europeas.</p>';
    echo '<p><b>AVISO:</b> Con el objetivo de mejorar el servicio y minimizar los problemas, se reserva el derecho a modificar y actualizar las condiciones de uso sin previo aviso. Es <strong>obligación del usuario</strong> el mantenerse informado de los cambios.</p>';
} else if ($_REQUEST['id'] == 'ignores') {
    echo '<h2>Ignores</h2>';
    echo '<h3>¿Qué son los ignores?</h3>';
    echo '<p>Los ignores se utilizan para dejar de leer a gente en la cotillona, y para evitar que el usuario ignorado nos lea lo que escribimos en la cotillona. No te recomendamos usarlo si no es estrictamente necesario. Si tienes algún problema con algún usuario, háblalo con él para solucionarlo, jonéame is for the lulz :-)</p>';
    echo '<h3>¿Cómo pongo a alguien en ignore?</h3>';
    echo '<p>Para poner un ignore, ve al perfil del usuario a ignorar, y pulsa 2 veces el corazón del usuario, hasta dejarlo de color negro.</p>';
}  else if ($_REQUEST['id'] == 'cotillona') {
    echo '<h2>Cotillona de Jonéame</h2>';
    echo '<h3>¿Qué es la cotillona?</h3>';
    echo '<p>La cotillona muestra todo lo que pasa en Jonéame en tiempo real. Puedes ver los votos, notitas, comentarios, o historias enviadas en el momento. Además, si eres usuario registrado, puedes utilizarlo para chatear con los demás usuarios o pedir ayuda a algún administrador.</p>';
    echo '<h3>¿Qué es la pestaña amigos?</h3>';
    echo '<p>La pestaña amigos se utiliza para que sólo aquellas personas que tú hayas seleccionado como amigos lean lo que escribes en la cotillona. Para hablar por la pestaña amigos basta que añadas el símbolo arroba (@) al comienzo de la frase. Diferenciarás la pestaña amigos de la pestaña todos por el color verde clarito.</p>';
    echo '<h3>Dices que usando la cotillona puedo contactar con un administrador, ¿cómo lo hago?</h3>';
    echo '<p>Basta que saludes y preguntes por un administrador para que alguno te atienda. Si no hay ninguno en ese momento, puedes poner una notita o enviarnos un email. También es posible que cualquier otro usuario pueda ayudarte.</p>';
    echo '<h3>¿Cómo veo quién está conectado a la cotillona?</h3>';
    echo '<p>Escribe <em>!usuarios</em> y pulsa <em>Enviar</em>. Pero eso no indica que dicho usuario esté atento, sólo que está conectado. Es posible que esté lurkeando.</p>';
    echo '<h3>¿Qué es lurkear?</h3>';
    echo '<p>Lurkear es lo que hacen los lurkers. Los lurkers son esas personas que están leyendo la cotillona pero no participan en la conversación.</p>';
    echo '<h3>¿Cómo recibo lo que se dice en la cotillona por Jabber/Gtalk?</h3>';
    echo '<p>Para ello, ve a la edición de tu perfil y indicanos cuál es tu email (nunca será visible a los demás). Después agrega como contacto a cotillona@joneame.net y podrás escribir desde ahí sin necesidad de tener que entrar por web. Escribe !off cuando no quieras saber nada de la cotillona.</p>';
}  else if ($_REQUEST['id'] == 'privados') {
    echo '<h2>Mensajería privada</h2>';
    echo '<h3>¿Qué son los mensajes privados?</h3>';
    echo '<p>Los mensajes privados son la única forma privada para contactar con algún usuario de la web.</p>';
    echo '<h3>¿Cómo hago para enviar un mensaje privado?</h3>';
    echo '<p>Para enviar un mensaje privado a un usuario, ve a su perfil y haz clic en el sobre al lado de su corazoncito.</p>';
    echo '<h3>¿Y para ver mi bandeja de entrada?</h3>';
    echo '<p>Puedes encontrar tu bandeja de entrada desde tu perfil o desde el sobre en la cabecera de todas las páginas de la web.</p>';
    echo '<h3>Recibo un email cada vez que me envían un mensaje privado y no quiero, ¿cómo lo desactivo?</h3>';
    echo '<p>Es fácil. Ve a la configuración de tus mensajes privados y desactiva la opción para no recibir emails.</p>';
    echo '<h3>¿Quién puede enviarme mensajes privados?</h3>';
    echo '<p>Por defecto todos los usuarios registrados pueden hacerlo, pero es configurable. Ve a la configuración y selecciona quién puede hacerlo, si todos, sólo tus amigos, o nadie.</p>';
} else if ($_REQUEST['id'] == 'login') {
    echo '<h3>¿Qué es eso de login con Twitter o Facebook?</h3>';
    echo '<p>En Jonéame sabemos que todos tenemos Twitter o Facebook, y también sabemos que os pasáis el día conectados a esas redes sociales. Si estás logueado en ellas, y eres un vago que no te apetece utilizar tus credenciales de usuario de jonéame, esta es tu opción. Conecta tu cuenta de Jonéame con Twitter o Facebook, y una vez lo hayas hecho, podrás iniciar sesión en un sólo clic. ¿A que mola?</p>';
    echo '<h3>Venga, vale, ¿y cómo conecto las cuentas?</h3>';
    echo '<p>Es fácil. Si estás registrado sólo tienes que ir a tu perfil, dónde encontrarás las opciones para conectar las cuentas que quieras. Este proceso sólo será necesario la primera vez que quieras acceder. Después sólo tienes que <a href="login.php">loguearte</a>.</h3>';
    echo '<h3>¿Y cómo quito la conexión después?</h3>';
    echo '<p>Sólo tienes que revocar el acceso a la aplicación de Jonéame en <a href="https://twitter.com/settings/applications">Twitter</a> o <a href="https://www.facebook.com/settings?tab=applications">Facebook</a>. Consulta con nosotros si tienes dudas.</p><br/><br/>';
    echo '<p>Nota: Jonéame no utiliza ni llega a conocer la contraseña de usuario de estas redes sociales. Las opciones están programadas, y son posibles gracias a las propias claves API de dichas redes sociales.</p>';
} else {
    echo '<h2>¿Qué es Jonéame?</h2>';
    echo '<h3>Bueno, y ¿qué es esto de Jonéame?</h3>';
    echo '<p>Jonéame es una red social, en la cual puedes compartir enlaces, conocer gente, chatear, y perder el tiempo.</p>';
    echo '<h3>¿De dónde viene Jonéame?</h3>';
    echo '<p>Ha sido desarrollado por <a href="'.$globals['base_url'].'credits.php">los propios usuarios</a>, partiendo de la base de <a href="http://meneame.net/" target="_blank">Menéame</a>. Ten paciencia si algo no te funciona. Contacta con nosotros para reportar los errores que veas.</p>';
    echo '<h3>Y ¿de qué va todo esto?</h3>';
    echo '<p>Jonéame comienza en el cachondeo, y acaba en el cachondeo. Nos gusta la pornografía, fotos, noticias, vídeos graciosos, noticias manipuladas, humor, viñetas, curiosidades, etc... ¡Y se permite el microblogging! Eso sí: recuerda leerte las <a href="'.$globals['legal'].'">condiciones de uso</a> antes de enviar nada.</p>';
    if ($current_user->user_id > 0) {
        echo '<h3>¿Qué puedo hacer en Jonéame?</h3>';
    } else {
        echo '<h3>¿Por qué debería registrarme?</h3>';
        echo '<p>Pues porque como usuario registrado podrás, entre otras cosas:</p>';
    }
    echo '<ul>';
    echo '<li><strong>Enviar <a href="'.$globals['base_url'].'">historias</a></strong><br/>Una vez registrado puedes enviar las historias que consideres curiosas/cachondas/interesantes para la comunidad. Si tienes algún tipo de duda sobre que tipo de historias puedes enviar revisa nuestras preguntas frecuentes sobre Jonéame (o simplemente echa un ojo a las <a href="'.$globals['base_url'].'">publicadas</a>).</li>';
    echo '<li><strong>Escribir comentarios</strong><br/>Puedes escribir tu opinión sobre las historias enviadas a Jonéame mediante comentarios de texto. También puedes votar positivamente aquellos comentarios ingeniosos, divertidos o interesantes y negativamente aquellos que consideres inoportunos.</li>';
    echo '<li><strong>Chatear en la <a href="'.$globals['base_url'].'cotillona.php">cotillona</a></strong><br/>Gracias a la cotillona puedes ver en tiempo real toda la actividad de Jonéame. Además como usuario registrado podrás chatear con mucha más gente de la comunidad mafiosa. Puedes usarla para ponerte en contacto con algún administrador también si lo deseas.</li>';
    echo '<li><strong>Enviar <a href="'.$globals['base_url'].'cortos.php">cortos</a></strong><br/>Una vez registrado puedes enviar cortos. Los cortos son pequeños textos que pueden hablar de lo que quieras. Lo que se te ocurra. Estos aparecerán en la parte superior de toda la web, seleccionados aleatoriamente. ¿A qué esperas para ver el tuyo ahí?</li>';
    echo '<li><strong>Enviar mensajes privados a otros usuarios</strong><br/>Exclusivamente en Jonéame puedes enviar mensajes privados a otros usuarios registrados. Para ello sólo tienes que ir al perfil de dicho usuario y hacer click en "privados". No, si al final acabas ligando y todo...</li>';
    echo '<li><strong>Hacer <a href="'.$globals['base_url'].'encuestas.php">encuestas</a></strong><br/>También puedes enviar encuestas. Añade las opciones que desees y los usuarios podrán responderla, eligiendo entre esas opciones.</li>';
    echo '</ul>';
    if (!($current_user->user_id > 0)) {
        echo '<p>Regístrate haciendo clic <a href="'.$globals['base_url'].'register.php">aquí</a>.</p>';
    }
}

echo '</div>';

do_footer();

function ayuda_tabs($tab_selected = false) {
    global $globals;
    $active = ' class="current"';
    echo '<ul class="tabhoriz">' . "\n";

    if (!empty($_SERVER['QUERY_STRING']))
        $query = "?".htmlentities($_SERVER['QUERY_STRING']);

    $tabs = array(
                '¿Qué es Jonéame?' => 'joneame',
                'FAQ' => 'faq',
                // 'Ignores' => 'ignores',
                'Emoticonos' => 'emoticonos',
                // 'Login' => 'login',
                'Cotillona' => 'cotillona',
                'Mensajes privados' => 'privados',
                'Condiciones legales' => 'legal',
                'Condiciones de uso' => 'uso',
            );

    foreach ($tabs as $name => $tab) {
        if ($tab_selected == $tab) {
            echo '<li'.$active.'><a href="'.$globals['base_url'].'ayuda.php?id='.$tab.'" title="'.$reload_text.'">'._($name).'</a></li>' . "\n";
        } else {
            echo '<li><a href="'.$globals['base_url'].'ayuda.php?id='.$tab.'">'._($name).'</a></li>' . "\n";
        }
    }
    echo '</ul>' . "\n";
}