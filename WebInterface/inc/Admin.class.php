<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: Admin.class.php
     * Last update: 22-02-2012
	******************************************************************/

	class Admin {
		public function Admin() {}
		
		public function addAdmin($username, $email, $pass) {
			$query = mysql_query("INSERT INTO admin_users (username,email, password,  date_created) VALUES ('" . mysql_real_escape_string($username) . "', '" . mysql_real_escape_string($email) . "', '" . md5($pass) . "', NOW())");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}
		
		public function getAdmins() {
			$query = mysql_query("SELECT * FROM admin_users ORDER BY date_created");
			if($query) {
				if(mysql_num_rows($query) == 0) {
					return false;
				}
				else {
					$return = array();
					while($data = mysql_fetch_assoc($query)) {
						$data['username'] = stripslashes($data['username']);
						$return[] = $data;
					}
					return $return;
				}
			}
			else {
				return false;
			}
		}
		
		public function checkValueExists($field, $value) {
			$query = mysql_query("SELECT id FROM admin_users WHERE " . mysql_real_escape_string($field) . " = '" . $value . "'");
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
		
		public function checkLoggedIn() {
			if(!empty($_SESSION['loggedIn'])) {
				$query = mysql_query("SELECT id FROM admin_users WHERE id = '" . mysql_real_escape_string($_SESSION['loggedIn']['id']) . "'");
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
		}
		
		public function login($username, $password) {
			$query = mysql_query("SELECT username, id FROM admin_users WHERE username = '" . mysql_real_escape_string($username) . "' AND password = '" . md5($password) . "'");
			if($query) {
				if(mysql_num_rows($query) == 0) {
					return false;
				}
				else {
					$data = mysql_fetch_assoc($query);
					$query = mysql_query("UPDATE admin_users SET last_login = NOW() where id = '" . $data['id'] . "'");
					if($query) {
						$_SESSION['loggedIn']['id'] = $data['id'];
						$_SESSION['loggedIn']['username'] = $data['username'];
						return true;
					}
					else {
						return false;
					}
				}
			}
			else {
				return false;
			}
		}
		
		public function getUsernameByID($id) {
			if(!is_numeric($id)) {
				return false;
			}
			else {
				$query = mysql_query("SELECT username FROM admin_users WHERE id = '" . $id . "'");
				if($query) {
					if(mysql_num_rows($query) == 0) {
						return false;
					}
					else {
						$data = mysql_fetch_assoc($query);
						return stripslashes($data['username']);
					}
				}
				else {
					return false;
				}
			}
		}
		
		public function getFieldDataByID($id, $field) {
			if(!is_numeric($id)) {
				return false;
			}
			else {
				$query = mysql_query("SELECT " . mysql_real_escape_string($field) . " FROM admin_users WHERE id = '" . $id . "'");
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
		
		public function updateFieldByID($id, $field, $data) {
			if(!is_numeric($id)) {
				return false;
			}
			else {
				$query = mysql_query("UPDATE admin_users SET " . mysql_real_escape_string($field) . " = '" . mysql_real_escape_string($data) . "' WHERE id = '" . $id . "'");
				if($query) {
					return true;
				}
				else {
					return false;
				}
			}
		}
		
		public function logout() {
			if(session_destroy()) {
				return true;
			}
			else {
				return false;
			}
		}
		
		public function deleteAdmin($id) {
			$query = mysql_query("DELETE FROM admin_users WHERE id = '" . mysql_real_escape_string($id) . "'");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}
	}
?>