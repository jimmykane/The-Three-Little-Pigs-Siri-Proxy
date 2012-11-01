<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: get-connected.php
     * Last update: 22-02-2012
	******************************************************************/

	$stats = getstats();

	echo '<h1>Get Connected Now</h1><p>This server is 100% compatible with the Spire GUI. A guide on how to get the Spire GUI <a target="_blank" href="http://wouterds.be/2011/12/27/how-to-install-the-first-legal-siri-gui-spire/465/">can be found here</a>.<br />After you got the GUI running up and well then you will need a Spire Proxy address.<br />Spire Proxy address: <strong>https://';

	$websiteProperty = new WebsiteProperty();
	echo $websiteProperty->getProperty('hostname_or_ip');

	echo '</strong> (note that it uses <strong>https</strong> and there is <strong>no</strong> port number)</p><p>You will also need a certificate in order to make a secure connection to the server.<br />You can download the certificate here: <a href="files/certs/certificate.pem">certificate.pem</a>.<br />Open it with Mobile Safari to install. If you are having trouble then download it via PC/Mac/Linux and email it to yourself.</p><p class="notification yellow">If the instructions are not clear to you, you might try <a href="https://www.youtube.com/watch?v=DdgZcgE05zM">watching this video tutorial</a>.<br />The server is accepting new people in <b>';

	$secondsLeft = $websiteProperty->getProperty("accepting_people_in") - $stats['happy_hour_elapsed'];
	if($secondsLeft < 0) {
		echo '<p class="notification green minimal">Happy Hour!</p>';
	}
	else {
		if($secondsLeft > 3600) {
			echo floor($secondsLeft / 3600) . ' hours ';
			echo floor(($secondsLeft % 3600) / 60) . ' minutes';
		}
		elseif($secondsLeft > 60) {
			echo floor($secondsLeft / 60) . ' minutes ';
			echo ($secondsLeft % 60) . ' seconds';
		}
		else {
			echo $secondsLeft . ' seconds';
		}
	}
	
	echo '</b>. Or when a new 4S is donated. So please <a href="?page=feed-the-piggy">feed the piggy</a>!</p>';

?>