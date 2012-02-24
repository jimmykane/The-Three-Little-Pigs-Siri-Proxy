<?php
    /******************************************************************
     * Project: The Three Little Pigs - Siri Proxy | Web Interface
     * Project start date: 21-01-2012
     * Author: Dimitrios Kanellopoulos
     * T: www.twitter.com/jimmykane9
     *
     * File: connection.inc.php
     * Last update: 02-02-2012
    ******************************************************************/

	require_once("mydbclass.inc.php");
	$db = new myDB(DB_HOST, DB_USER, DB_PASS);
	$db->SelectDB(DB_NAME);
?>
