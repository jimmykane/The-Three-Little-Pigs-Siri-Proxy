<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: Faq.class.php
     * Last update: 22-02-2012
	******************************************************************/

	class Faq {
		public function Faq() {}
		
		public function addFAQ($question, $answer) {
			$query = mysql_query("INSERT INTO faq (question, answer, date_added) VALUES ('" . mysql_real_escape_string(htmlentities($question)) . "', '" . mysql_real_escape_string(htmlentities($answer)) . "', NOW())");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}
		
		public function getFAQ($noHtml = false) {
			$query = mysql_query("SELECT * FROM faq WHERE disabled = '0' ORDER by date_added DESC");
			if($query) {
				if(mysql_num_rows($query) == 0) {
					return false;
				}
				else {
					$return = array();
					while($data = mysql_fetch_assoc($query)) {
						if(!$noHtml) {
							$data['answer'] = stripslashes(html_entity_decode($data['answer']));
							$data['question'] = stripslashes(html_entity_decode($data['question']));
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
		
		public function updateFAQ($id, $question, $answer) {
			$query = mysql_query("UPDATE faq SET question = '" . mysql_real_escape_string(htmlentities($question)) . "', answer= '" . mysql_real_escape_string(htmlentities($answer)) . "' WHERE id = '" . mysql_real_escape_string($id) . "'");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}
		
		public function deleteFAQ($id) {
			$query = mysql_query("UPDATE faq SET disabled = '1' WHERE id = '" . mysql_real_escape_string($id) . "'");
			if($query) {
				return true;
			}
			else {
				return false;
			}
		}
	}
?>