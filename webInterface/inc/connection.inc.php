<?php
require_once ("config.inc.php");
require_once("mydbclass.inc.php");
$db = new myDB(DB_HOST,DB_USER,DB_PASS);
$db->SelectDB('siri');
?>
