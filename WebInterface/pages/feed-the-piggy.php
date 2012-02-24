<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: feed-the-piggy.php
     * Last update: 22-02-2012
	******************************************************************/

	echo '<h1>Feed The Three Little Pigs</h1><h2>This procedure is only for an iPhone 4S</h2><p>Below is a guide how to feed the Piggy a 4S key</p><ol><li>Install this certificate on the iPhone 4S <a href="files/certs/cerftificate.pem">cerftificate.pem</a></li><li>If the above certificate doesn\'t install then download it to your PC/Mac/Linux, and mail it to yourself. </li><li>Go to Settings > Wifi, press the blue arrow and change the DNS field to <b>';

	$websiteProperty = new WebsiteProperty();
	echo gethostbyname($websiteProperty->getProperty('hostname_or_ip'));

	echo '</b></li><li>Use Siri.</li><li>Done! <b>If Siri replied back to you then you just fed the Piggy another key.</b> Now check the <a href="?page=server-status">server status</a> to see if the key has been added!</li><li>In order to rollback to your old DNS just put the old DNS back, renew the lease or delete the DNS and quit the Settings app.</li></ol><p class="notification yellow">Warning! This is a public server and the validation data will be shared with more than one device! Although this server has several protections it is still possible the key gets blacklisted, however you can easily unblacklist your device (read as "Search on Google how to unblacklist").<br />PS: I take no responsibility for using this server, you agree that you do this on your own risk.</p>';
?>