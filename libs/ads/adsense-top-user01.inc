<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

// This is an example for preparing the scripts for users' ads
echo '<script><!--' . "\n";
echo 'google_ad_client = "'.$globals['user_adcode'].'";' . "\n";
echo 'google_ad_width = 728;' . "\n";
echo 'google_ad_height = 90;' . "\n";
echo 'google_ad_format = "728x90_as";' . "\n";
if ($current_user->user_id == 0) 
	echo 'google_ad_type = "text_image";' . "\n";
else
	echo 'google_ad_type = "text";' . "\n";
if (!empty($globals['user_adchannel'])) 
	echo 'google_ad_channel ="'.$globals['user_adchannel'].'";' . "\n";
echo 'google_color_border = "FFFFFF";' . "\n";
echo 'google_color_bg = "F0F0F0";' . "\n";
echo 'google_color_link = "0033aa";' . "\n";
echo 'google_color_url = "000000";' . "\n";
echo 'google_color_text = "0c0c0c";' . "\n";
echo '//--></script>' . "\n";
echo '<script type="text/javascript"' . "\n";
echo '  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">' . "\n";
echo '</script>' . "\n";