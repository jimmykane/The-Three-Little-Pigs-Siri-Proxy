<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: frequently-asked-questions.php
     * Last update: 22-02-2012
	******************************************************************/

	echo '<h1>FAQ (Frequently Asked Questions)</h1>';

	$faq = new Faq();
	$dataArr = $faq->getFAQ();
	if($dataArr == false) {
		echo '<p class="notification red">There are no existing frequently asked questions.</p>';
	}
	else {
		$i = 0;
		foreach($dataArr as $data) {
			echo '<p id="FAQ-' . $data['id'] . '"><b>Q:</b> ' . $data['question'] . '<br /><b>A:</b> ' . $data['answer'] . '</p>';
		}
	}
?>