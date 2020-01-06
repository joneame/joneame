#!/bin/bash
echo "Introduce tu usuario en Jonéame"
read usuario
echo "Introduce tu clave API"
read clave
echo "¿Qué estás haciendo?"
read mensaje
echo "Enviando notita..."
wget "https://joneame.net/api/newpost.php?user=$usuario&key=$clave&text=$mensaje" -O /dev/null > /dev/null
echo "Notita enviada ;-)"
