<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: Ban.class.php
     * Last update: 22-02-2012
	******************************************************************/

	class Ban {
		public function Ban() {}
		
		public function addBan($ip, $reason) {
			$query = mysql_query("INSERT INTO ip_bans (ip, reason, dtime) VALUES ('" . mysql_real_escape_string($ip) . "', '" . mysql_real_escape_string($reason) . "', NOW())");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}

		public function getBans() {
			$query = mysql_query("SELECT * FROM ip_bans ORDER by dtime DESC");
			if($query) {
				if(mysql_num_rows($query) == 0) {
					return false;
				}
				else {
					$return = array();
					while($data = mysql_fetch_assoc($query)) {
						$return[] = $data;
					}
					return $return;
				}
			}
			else {
				return false;
			}
		}
		
		public function updateBan($id, $ip, $reason) {
			$query = mysql_query("UPDATE ip_bans SET ip = '" . mysql_real_escape_string($ip) . "', reason= '" . mysql_real_escape_string($reason) . "' WHERE id = '" . mysql_real_escape_string($id) . "'");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}
		
		public function checkValueExists($field, $value) {
			$query = mysql_query("SELECT " . mysql_real_escape_string($field) . " FROM ip_bans WHERE " . mysql_real_escape_string($field) . " = '" . $value . "'");
			if($query) {
				if(mysql_num_rows($query) == 0) {
					return false;
				}
				else {
					return true;
				}
			}
			else {
				return false;
			}
		}
		
		public function checkBan($getContent = false) {
			$query = mysql_query("SELECT reason, dtime FROM ip_bans WHERE ip = '" . mysql_real_escape_string($_SERVER['REMOTE_ADDR']) . "'");
			if($query) {
				if(mysql_num_rows($query)) {
					$data = mysql_fetch_assoc($query);
					if($getContent == "reason") {
						return stripslashes($data['reason']);
					}
					elseif($getContent == "dtime") {
						return stripslashes($data['dtime']);
					}
					else {
						return true;
					}
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}
		
		public function deleteBan($id) {
			$query = mysql_query("DELETE FROM ip_bans WHERE id = '" . mysql_real_escape_string($id) . "'");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}
	}
?>