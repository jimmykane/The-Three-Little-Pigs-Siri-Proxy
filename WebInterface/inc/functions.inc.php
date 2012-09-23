<?php
    /******************************************************************
     * Project: The Three Little Pigs - Siri Proxy | Web Interface
     * Project start date: 21-01-2012
     * Author: Wouter De Schuyter
     * Website: www.wouterds.be
     * E: info[@]wouterds[.]be
     * T: www.twitter.com/wouterds
     *
     * File: functions.inc.php
     * Last update: 22-02-2012
    ******************************************************************/

    // redirect(location, time before redirected)
    function redirect($url, $seconds) {
    	header("Refresh: " . $seconds . "; URL=" . $url);
    }

    // shortString(string, to display characters)
    function shortString($string, $max) {
    	if(strlen($string) > ($max + 3)) {
    		return substr($string, 0, $max) . "..";
    	}
    	else {
    		return $string;
    	}
    }

    // ads()
    function ads() {
        return '<!--Place here your ad code, remove this line afterwards -->';
    }

    // supportMe()
    function supportMe() {
        // PLEASE DO NOT CHANGE ANYTHING ON THIS FUNCTION
        // IF YOU WANT US TO KEEP DEVELOPING AND IMPROVING THE CODE
        // SHOW US SOME RESPECT AND DON'T TOUCH THIS FUNCTION
        return '<h2 style="margin-top: 50px;">Contribute to keep this project alive!</h2><div style="overflow: auto;"><div style="float: left; width: 750px;"><p>If you like this public Siri Proxy server, please consider a donation. I\'m doing this in my free time and I\'m paying the server from my pocket. Support me and help me pay for the server costs, keep me working together with many others on this project and maintenancing it.<br /><br />I thank you,<br />WouterDS</p></div><div style="width: 150px; float: right; padding-top: 20px;"><form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"></form></div></div>';
    }

    /* Functions below from @JimmyKane9
    ******************************************************************/
    // getkeys()
    function getkeys() {
        extract($GLOBALS);
        $query = "SELECT * FROM `keys` ORDER by date_added DESC";
        $results = $db->MakeQuery($query);
        if(mysql_num_rows($results) > 0) {
    		while($data = mysql_fetch_assoc($results)) {
    			if($data['expired'] == "False") {
    				$keys[0][] = $data;
    			}
    			else {
    				$keys[1][] = $data;
    			}
    		}
    		$keys[2]['availablekeys'] = count($keys[0]);
            return $keys;
        }
    	else {
            return false;
    	}
    }
    // getkeydonors(appleaccountid from a key)
    function getkeydonors($accountid) {
        extract($GLOBALS);
        $query = 'SELECT * FROM `clients` WHERE apple_account_id="'.$accountid.'"';
        $results = $db->MakeQuery($query);
        if(mysql_num_rows($results) > 0) {
            while($data = mysql_fetch_array($results)) {
                if ($data['nickname'] == "NA") {
                    $client = $data['fname'];
                }
                else {
                    $client = $data['nickname'];
                }
            }
            return $client;
        }
        else {
            return false;
        }
    }
    // getassistants(id from a key record)
    function getassistants($keyid) {
        extract($GLOBALS);
        $query = 'SELECT * FROM `assistants` WHERE key_id="'.$keyid.'"';
        $result = $db->MakeQuery($query);
        $available_assistants_count = $db->GetRecordCount($result);
        if ($available_assistants_count > 0) {

            $assistants = $db->GetResultAsArray($result);
                    $assistants[0]['assistantscount'] = $available_assistants_count;
            return $assistants;
        }
        return false;
    }

    // getconfig
    function getconfig() {
        extract($GLOBALS);
        $query = "SELECT * FROM `config` WHERE id=1 ";
        $result = $db->MakeQuery($query);
        $config = $db->GetRecord($result);
        return $config;
    }

    // getstats
    function getstats() {
        extract($GLOBALS);
        $query = "SELECT * FROM `stats` WHERE id=1 ";
        $result = $db->MakeQuery($query);
        $stats = $db->GetRecord($result);
        return $stats;
    }
?>