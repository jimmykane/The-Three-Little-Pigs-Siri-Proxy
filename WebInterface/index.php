<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: index.php
     * Last update: 22-02-2012
	******************************************************************/
	
	/* Error reporting (VERY important)
	******************************************************************/
	ini_set('display_errors', 'On');
	error_reporting(E_ALL ^ E_NOTICE);
	
	/* Check if is install is completed
	******************************************************************/
	if(!file_exists("inc/config.inc.php")) {
		echo '<p>Please run the <a href="install.php">installer first</a>.</p>';
	}
	elseif(file_exists('install.php')) {
		echo '<p>Please delete the installer <b>(install.php)</b> to protect your site!</p>';
	}
	else {
	/* Start session + fix php redirects 
	******************************************************************/
	session_start();
	ob_start();
	
	/* Required files (config, connection, functions, classes etc..) 
	******************************************************************/
	require_once("inc/Log.class.php");
	require_once("inc/Ban.class.php");
	require_once("inc/Faq.class.php");
	require_once("inc/Key.class.php");
	require_once("inc/config.inc.php");
	require_once("inc/Admin.class.php");
	require_once("inc/Client.class.php");
	require_once("inc/Layout.class.php");
	require_once("inc/functions.inc.php");
	require_once("inc/connection.inc.php");
	require_once("inc/Statistics.class.php");
	require_once("inc/PageManager.class.php");
	require_once("inc/Announcement.class.php");
	require_once("inc/WebsiteProperty.class.php");
	
	/* Build content
	******************************************************************/
	$pMgr = new PageManager("pages", "page");
	$websiteProperty = new WebsiteProperty();
	$ban = new Ban();
	$layout = new Layout(
		$websiteProperty->getProperty('website_title') . ' :: ' . $pMgr->getTitle(),
		$pMgr->navigation()
	);

	echo $layout->buildTop();

	if($ban->checkBan()) {
		echo '<p class="notification red">Your IP has been banned on ' . $ban->checkBan("dtime") . '<br />Reason: ' . $ban->checkBan("reason") . '</p>';
	}
	else {
		if($_GET['page'] == 'admin') {
			$pMgr->getPageContent();
		}
		else {
			$announcement = new Announcement();

			$dataArr = $announcement->getAnnouncements();

			if($dataArr !== false) {
				foreach($dataArr as $data) {
					echo '<p class="notification ' . strtolower($data['announcement_type']) . '">[' . $data['date_added'] . '] ' . stripslashes($data['announcement_text']) . '</p>';
				}
			}
							
			echo ads();
			$pMgr->getPageContent();
			echo supportMe();
			echo ads();
		}
	}

	echo $layout->buildBottom();
	
	/* Statistics + end fix php redirects
	******************************************************************/
	$statistics = new Statistics();
	$statistics->log_ip($_GET['page']);
	ob_end_flush();
	}
?>