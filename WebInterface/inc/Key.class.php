<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: Key.class.php
     * Last update: 22-02-2012
	******************************************************************/

	class Key {
		public function Key() {}

		public function getTotalRecordCount() {
			$query = mysql_query("SELECT id FROM `keys`");
			if($query) {
				return mysql_num_rows($query);
			}
			else {
				return false;
			}
		}
		
		public function addKey($assistantid, $speechid, $sessionValidation, $banned, $expired, $keyload, $iPad3) {
			$query = mysql_query("INSERT INTO `keys` (assistantid,speechid,sessionValidation,banned,expired,keyload,iPad3,date_added,last_used) 
                        VALUES  ('" . mysql_real_escape_string($assistantid) . "',
				 '" . mysql_real_escape_string($speechid) . "',
				 '" . mysql_real_escape_string($sessionValidation) . "',
				 '" . $banned . "',
				 '" . $expired . "',
				 '" . $keyload . "',
				 '" . $iPad3 . "',
				 NOW(),
				 NOW())");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}
		
		public function checkValueExists($field, $value) {
			$query = mysql_query("SELECT id FROM `keys` WHERE " . mysql_real_escape_string($field) . " = '" . $value . "'");
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
		
		public function updateFieldByID($id, $field, $data) {
			$query = mysql_query("UPDATE `keys` SET " . mysql_real_escape_string($field) . " = '" . mysql_real_escape_string($data) . "' WHERE id = '" . mysql_real_escape_string($id) . "'");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}
		
		public function getFieldDataByID($id, $field) {
			if(!is_numeric($id)) {
				return false;
			}
			else {
				$query = mysql_query("SELECT " . mysql_real_escape_string($field) . " FROM `keys` WHERE id = '" . $id . "'");
				if($query) {
					if(mysql_num_rows($query) == 0) {
						return false;
					}
					else {
						$data = mysql_fetch_assoc($query);
						return stripslashes($data[$field]);
					}
				}
				else {
					return false;
				}
			}
		}

		public function getKeys($count, $startRecord) {
			$query = mysql_query("SELECT * FROM `keys` ORDER BY date_added DESC LIMIT " . mysql_real_escape_string($startRecord) . ",
			" . mysql_real_escape_string($count));
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
		
		public function deleteKey($id) {
			$query = mysql_query("DELETE FROM `keys` WHERE id = '" . mysql_real_escape_string($id) . "'");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}
	}
?>
