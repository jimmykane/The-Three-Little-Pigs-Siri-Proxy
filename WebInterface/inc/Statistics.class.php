<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: Statistics.class.php
     * Last update: 22-02-2012
	******************************************************************/

	class Statistics {
		public function Statistics() {}

		public function checkServer($hostWithPort) {
			$hostWithPort = explode(":", $hostWithPort);
			$host = $hostWithPort[0];
			$port = $hostWithPort[1];
			if(@fsockopen($host, $port, $errNum, $errStr, 1)) {
				return true;
			}
			else {
				return false;
			}
		}

		public function getTableRecordCount($table, $when = "total") {
			if($when == "total") {
				$query = mysql_query("SELECT id FROM `" . mysql_real_escape_string($table) . "`");
			}
			elseif($when == "today") {
				
			}
			elseif($when == "yesterday") {
				
			}
			elseif($when == "last week") {
				
			}

			if($query) {
				return mysql_num_rows($query);
			}
			else {
				return false;
			}
		}

		public function log_ip($page) {
			if(empty($page)) {
				$page = "homepage";
			}
			if(!mysql_query("INSERT INTO ip_logs (ip, useragent, referer, currentPage, dtime) VALUES ('" . $_SERVER['REMOTE_ADDR'] . "', '" . $_SERVER['HTTP_USER_AGENT'] . "', '" . $_SERVER['HTTP_REFERER'] . "', '" . $page . "', NOW())")) {
				//echo '<!--- Query error: ' . mysql_error() . ' -->';
			}
		}
	}
?>