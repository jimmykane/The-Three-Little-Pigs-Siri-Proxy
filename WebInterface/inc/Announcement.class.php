<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: Announcement.class.php
     * Last update: 22-02-2012
	******************************************************************/

	class Announcement {
		public function Announcement() {}
		
		public function addAnnouncement($type, $text) {
			$query = mysql_query("INSERT INTO announcements (announcement_type, announcement_text, date_added) VALUES ('" . mysql_real_escape_string($type) . "', '" . mysql_real_escape_string(htmlentities($text)) . "', NOW())");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}
		
		public function getAnnouncements($noHtml = false) {
			$query = mysql_query("SELECT * FROM announcements WHERE disabled = '0' ORDER by date_added DESC");
			if($query) {
				if(mysql_num_rows($query) == 0) {
					return false;
				}
				else {
					$return = array();
					while($data = mysql_fetch_assoc($query)) {
						if(!$noHtml) {
							$data['announcement_text'] = stripslashes(html_entity_decode($data['announcement_text']));
						}
						$return[] = $data;
					}
					return $return;
				}
			}
			else {
				return false;
			}
		}
		
		public function updateAnnouncement($id, $type, $text) {
			$query = mysql_query("UPDATE announcements SET announcement_type = '" . mysql_real_escape_string($type) . "', announcement_text= '" . mysql_real_escape_string(htmlentities($text)) . "' WHERE id = '" . mysql_real_escape_string($id) . "'");
			if($query) {
				return true;
			}
			else {
				die(mysql_error());
				return false;
			}
		}
		
		public function deleteAnnouncement($id) {
			$query = mysql_query("UPDATE announcements SET disabled = '1' WHERE id = '" . mysql_real_escape_string($id) . "'");
			if($query) {
				return true;
			}
			else {
				die(mysql_error());
				return false;
			}
		}
	}
?>