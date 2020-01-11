<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

include('config.php');
header('Content-Type: text/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">';
echo '<ShortName>Búsqueda Jonéame</ShortName>';
echo '<Description>'._('chorradas elegidas por los usuarios').'</Description>';
echo '<InputEncoding>UTF-8</InputEncoding>';
echo '<Image height="16" width="16">https://'.get_server_name().$globals['base_url'].'img/favicons/favicon4.ico</Image>';
echo '<Url type="text/html" method="GET" template="https://'.get_server_name().$globals['base_url'].'search.php">';
echo '<Param name="q" value="{searchTerms}"/>';
echo '</Url>';
echo '<Url type="application/rss+xml" template="https://'.get_server_name().$globals['base_url'].'rss2.php">';
echo '<Param name="q" value="{searchTerms}"/>';
echo '</Url>';
echo '<SearchForm>https://'.get_server_name().$globals['base_url'].'search.php</SearchForm>';
echo '</OpenSearchDescription>';