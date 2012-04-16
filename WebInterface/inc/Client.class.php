<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: Client.class.php
     * Last update: 22-02-2012
	******************************************************************/

	class Client {
		public function Client() {}
		
		public function addClient($firstname, $nickname, $valid) {
			$query = mysql_query("INSERT INTO clients (fname, nickname, valid, date_added) VALUES ('" . mysql_real_escape_string($firstname) . "', '" . mysql_real_escape_string($nickname) . "', '" . mysql_real_escape_string($valid) . "', NOW())");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}
		
		public function updateFieldByID($id, $field, $data) {
			$query = mysql_query("UPDATE clients SET " . mysql_real_escape_string($field) . " = '" . mysql_real_escape_string($data) . "' WHERE id = '" . mysql_real_escape_string($id) . "'");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}
		
		public function checkValueExists($field, $value) {
			$query = mysql_query("SELECT id FROM clients WHERE " . mysql_real_escape_string($field) . " = '" . $value . "'");
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
		
		public function getFieldDataByID($id, $field) {
			if(!is_numeric($id)) {
				return false;
			}
			else {
				$query = mysql_query("SELECT " . mysql_real_escape_string($field) . " FROM clients WHERE id = '" . $id . "'");
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
		
		public function deleteClient($id) {
			$query = mysql_query("DELETE FROM clients WHERE id = '" . mysql_real_escape_string($id) . "'");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}

		public function getTotalRecordCount() {
			$query = mysql_query("SELECT id FROM clients");
			if($query) {
				return mysql_num_rows($query);
			}
			else {
				return false;
			}
		}

		public function getClients($count, $startRecord) {
			$query = mysql_query("SELECT * FROM clients ORDER BY id ASC LIMIT " . mysql_real_escape_string($startRecord) . "," . mysql_real_escape_string($count));
			if($query) {
				if(mysql_num_rows($query) == 0) {
					return false;
				}
				else {
					$return = array();
					$deviceArray = array();
					while($data = mysql_fetch_assoc($query)) {
			$query1 = mysql_query("SELECT device_type, last_login, last_ip FROM assistants WHERE client_apple_account_id = '" . $data['apple_account_id'] . "' AND date_created > NOW() - INTERVAL 8 DAY GROUP BY client_apple_account_id");
                                            while($data1 = mysql_fetch_assoc($query1)) {
                                                    $deviceArray = $data1;
                                                }
						$return[] = $data + $deviceArray;
					}
					return $return;
				}
			}
			else {
				return false;
			}
		}

		public function getClientsLike($like) {
			$query = mysql_query("SELECT * FROM clients WHERE nickname LIKE '%" . mysql_real_escape_string($like) .
			"%' OR fname LIKE '%" . mysql_real_escape_string($like) . "%' ORDER BY id ASC");
			if($query) {
				if(mysql_num_rows($query) == 0) {
					return false;
				}
				else {
					$return = array();
					$deviceArray = array();
					while($data = mysql_fetch_assoc($query)) {
			$query1 = mysql_query("SELECT device_type, last_login, last_ip FROM assistants WHERE client_apple_account_id = '" . $data['apple_account_id'] . "' AND date_created > NOW() - INTERVAL 8 DAY GROUP BY client_apple_account_id");
                                            while($data1 = mysql_fetch_assoc($query1)) {
                                                    $deviceArray = $data1;
                                                }
						$return[] = $data + $deviceArray;
					}
					return $return;
				}
			}
			else {
				return false;
			}
		}
	}
?>