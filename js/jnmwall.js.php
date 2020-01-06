<?php
include('../config.php');
header('Content-Type: text/javascript; charset=utf-8');
header('Cache-Control: max-age=3600');

global $db;

$files = $db->get_col('select avatar_id from avatars order by avatar_id asc');
print('var files = ' . json_encode($files) . ';');
