<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: load.ajax.php
     * Last update: 16-02-2012
	******************************************************************/

	require_once("config.inc.php");
	require_once("connection.inc.php");
	require_once("functions.inc.php");
	require_once("Statistics.class.php");
	
	$config = getconfig();
	$stats = getstats();
	$keys = getkeys();
	$statistics = new Statistics();
	
	$serverStatus = $statistics->checkServer("173.0.57.230:443");
	
	$keyFactor = 0;
	if(count($keys[0]) > 0) {	
		foreach($keys[0] as $key) {
			if($key['keyload'] < $config['max_keyload']) {
				$keyFactor++;
			}
		}
	}	
	
	switch($_GET['get']) {
		default:
			echo 'No get variable set (api.php?get=x).<br /><br />Possible options:<br /><ul><li>server-running</li><li>server-uptime</li><li>dropdown-time</li><li>happy-hour</li></ul>';
			echo '<h1>' . $stats['happy_hour_elapsed'] . '</h1>';
		break;
		
		case "server-status":
            if($serverStatus)
                echo '<p class="notification green minimal">ON</p>';
            else
                echo '<p class="notification red minimal">OFF</p>';
		break;
		
		case "server-uptime":
            if ($serverStatus == true) {
				if($stats['up_time'] > 3600) {
					echo floor($stats['up_time'] / 3600) . ' hours ';
					echo floor(($stats['up_time'] % 3600) / 60) . ' minutes';
				}
				elseif($stats['up_time'] > 60) {
					echo floor($stats['up_time'] / 60) . ' minutes ';
					echo ($stats['up_time'] % 60) . ' seconds';
				}
				else {
					echo $stats['up_time'] . ' seconds';
				}
			}
			else {
				echo '<p class="notification red minimal">0 seconds</p>';
			}
		break;
		
		case "available-keys":
            if ($keyFactor == 0)
                echo '<p class="notification red minimal">0</p>';
            else
                echo '<p class="notification green minimal">' .  $keyFactor. '</p>';
		break;
		
		case "overloaded-keys":
            if (($keys[2]['availablekeys'] - $keyFactor) !== 0)
                echo '<p class="notification red minimal">' . ($keys[2]['availablekeys'] - $keyFactor) . '</p>';
            else
                echo '<p class="notification green minimal">0</p>';
		break;
		
		case "active-connections":
            if ($config['active_connections'] > ($config['max_connections'] * $keyFactor))
                echo '<p class="notification red minimal">' . $config['active_connections'] . '</p>';
            else
                echo '<p class="notification green minimal">' . $config['active_connections'] . '</p>';
		break;
		
		case "max-connections":
			echo $config['max_connections'];
		break;
		
		case "max-keyload":
			echo $config['max_keyload'];
		break;
		
		case "keyload-dropdown":
			echo $config['keyload_dropdown'];
		break;
		
		case "keyload-interval":
			echo $config['keyload_dropdown_interval'];
		break;
		
		case "dropdown-time":
			$secondsLeft = $config['keyload_dropdown_interval'] - $stats['elapsed_key_check_interval'];
		
			if($secondsLeft > 60) {
					echo floor($secondsLeft / 60) . ' minutes ';
					echo ($secondsLeft % 60). ' seconds';
			}
			else {
				echo $secondsLeft . ' seconds';
			}
		break;
		
		case "happy-hour":
				$secondsLeft = ACCEPTING_NEW_PEOPLE_IN - $stats['happy_hour_elapsed'];
				
				if($secondsLeft < 0) {
					echo '<p class="notification green">Happy Hour!</p>';
				}
				else {
					if($secondsLeft > 3600) {
						echo floor($secondsLeft / 3600) . ' hours ';
						echo floor(($secondsLeft % 3600) / 60) . ' minutes';
					}
					elseif($secondsLeft > 60) {
						echo floor($secondsLeft / 60) . ' minutes ';
						echo ($secondsLeft % 60) . ' seconds';
					}
					else {
						echo $secondsLeft . ' seconds';
					}
				}
		break;
	}
?>