<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: install.php
     * Last update: 24-02-2012
	******************************************************************/

	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$inputErrors = array();

		if(empty($_POST['db_host'])) {
			$mysql = 'Please enter a value for the database host.';
			$inputErrors[] = 'db_host';
		}
		elseif(empty($_POST['db_user'])) {
			$mysql = 'Please enter a value for the database user.';
			$inputErrors[] = 'db_user';
		}
		elseif(empty($_POST['db_pass'])) {
			$mysql = 'Please enter a value for the database pass.';
			$inputErrors[] = 'db_pass';
		}
		elseif(empty($_POST['db_name'])) {
			$mysql = 'Please enter a value for the database name.';
			$inputErrors[] = 'db_name';
		}
		else {
			if(!mysql_connect($_POST['db_host'], $_POST['db_user'], $_POST['db_pass'])) {
				$mysql = 'Could not connect to the database server.<br />Please check your database info.';
				$inputErrors[] = 'db_host';
				$inputErrors[] = 'db_user';
				$inputErrors[] = 'db_pass';
			}
			else {
				if(!mysql_select_db($_POST['db_name'])) {
					$mysql = 'Could not select the database. Make sure the database exists.';
					$inputErrors[] = 'db_name';
				}
				else {
					if(empty($_POST['pc_threads'])) {
						$proxyconfig = 'Please enter a value for the maximum threads.';
						$inputErrors[] = 'pc_threads';
					}
					elseif(empty($_POST['pc_maxCon'])) {
						$proxyconfig = 'Please enter a value for the maximum connections per key.';
						$inputErrors[] = 'pc_maxCon';
					}
					elseif(empty($_POST['pc_maxKeyload'])) {
						$proxyconfig = 'Please enter a value for the maximum keyload per key.';
						$inputErrors[] = 'pc_maxKeyload';
					}
					elseif(empty($_POST['pc_keyloadDropdown'])) {
						$proxyconfig = 'Please enter a value for the keyload dropdown.';
						$inputErrors[] = 'pc_keyloadDropdown';
					}
					elseif(empty($_POST['pc_dropdownTime'])) {
						$proxyconfig = 'Please enter a value for the dropdown time.';
						$inputErrors[] = 'pc_dropdownTime';
					}
					elseif(!is_numeric($_POST['pc_threads'])) {
						$proxyconfig = 'Please enter a numeric value for the maximum threads.';
						$inputErrors[] = 'pc_threads';
					}
					elseif(!is_numeric($_POST['pc_maxCon'])) {
						$proxyconfig = 'Please enter a numeric value for the maximum connections per key.';
						$inputErrors[] = 'pc_maxCon';
					}
					elseif(!is_numeric($_POST['pc_maxKeyload'])) {
						$proxyconfig = 'Please enter a numeric value for the maximum keyload per key.';
						$inputErrors[] = 'pc_maxKeyload';
					}
					elseif(!is_numeric($_POST['pc_keyloadDropdown'])) {
						$proxyconfig = 'Please enter a numeric value for the keyload dropdown.';
						$inputErrors[] = 'pc_keyloadDropdown';
					}
					elseif(!is_numeric($_POST['pc_dropdownTime'])) {
						$proxyconfig = 'Please enter a numeric value for the dropdown time.';
						$inputErrors[] = 'pc_dropdownTime';
					}
					else {
						if(empty($_POST['admin_username'])) {
							$adminuser = 'Please enter a value for the admin username.';
							$inputErrors[] = 'admin_username';
						}
						elseif(empty($_POST['admin_email'])) {
							$adminuser = 'Please enter a value for the admin email.';
							$inputErrors[] = 'admin_email';
						}
						elseif(@!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST['admin_email'])) {
							$adminuser = 'Please enter a valid email.';
							$inputErrors[] = 'admin_email';
						}
						elseif(empty($_POST['admin_password'])) {
							$adminuser = 'Please enter a value for the admin password.';
							$inputErrors[] = 'admin_password';
						}
						else {
							if(empty($_POST['wp_hostname'])) {
								$websiteproperties = 'Please enter a value for the hostname.';
								$inputErrors[] = 'wp_hostname';
							}
							elseif(empty($_POST['wp_gbEntries'])) {
								$websiteproperties = 'Please enter a value for the maximum guestbook entries per page.';
								$inputErrors[] = 'wp_gbEntries';
							}
							elseif(empty($_POST['wp_kEntries'])) {
								$websiteproperties = 'Please enter a value for the maximum keys per page.';
								$inputErrors[] = 'wp_kEntries';
							}
							elseif(empty($_POST['wp_cEntries'])) {
								$websiteproperties = 'Please enter a value for the maximum clients per page.';
								$inputErrors[] = 'wp_cEntries';
							}
							elseif(!is_numeric($_POST['wp_gbEntries'])) {
								$websiteproperties = 'Please enter a numeric value for the maximum GB entries per page.';
								$inputErrors[] = 'wp_gbEntries';
							}
							elseif(!is_numeric($_POST['wp_kEntries'])) {
								$websiteproperties = 'Please enter a numeric value for the maximum keys per page.';
								$inputErrors[] = 'wp_kEntries';
							}
							elseif(!is_numeric($_POST['wp_cEntries'])) {
								$websiteproperties = 'Please enter a numeric value for the maximum clients per page.';
								$inputErrors[] = 'wp_cEntries';
							}
							elseif(empty($_POST['wp_cName'])) {
								$websiteproperties = 'Please enter a value for the contact name.';
								$inputErrors[] = 'wp_cName';
							}
							elseif(empty($_POST['wp_cEmail'])) {
								$websiteproperties = 'Please enter a value for the contact email.';
								$inputErrors[] = 'wp_cEmail';
							}
							elseif(@!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST['wp_cEmail'])) {
								$websiteproperties = 'Please enter a valid email.';
								$inputErrors[] = 'wp_cEmail';
							}
							else {
								$success = true;
								$data =
								'<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: config.inc.php
     * Generated on: ' . date("d-m-Y H:i:s") . '
	******************************************************************/

	define(DB_HOST, \'' . $_POST['db_host'] . '\'); // Your host
	define(DB_USER, \'' . $_POST['db_user'] . '\'); // Your db username
	define(DB_PASS, \'' . $_POST['db_pass'] . '\'); // Your db password
	define(DB_NAME, \'' . $_POST['db_name'] . '\'); // Your db name
?>';
								$fh = fopen("inc/config.inc.php", "w");
								if($fh) {
									if(fwrite($fh, $data)) {
										$queries = "
										/*
										 SQL Structure + Essential data
										 ---------------------------------------
										 Source Server         : Siri Proxy TTLP
										 Source Server Type    : MySQL
										 Source Server Version : 50161
										 Source Host           : 173.0.57.230
										 Source Database       : siri

										 Target Server Type    : MySQL
										 Target Server Version : 50161
										 File Encoding         : utf-8

										 Date: 02/21/2012 16:34:36 PM
										*/

										SET NAMES utf8;
										SET FOREIGN_KEY_CHECKS = 0;

										-- ----------------------------
										--  Table structure for `admin_logs`
										-- ----------------------------
										DROP TABLE IF EXISTS `admin_logs`;
										CREATE TABLE `admin_logs` (
										  `id` int(11) NOT NULL AUTO_INCREMENT,
										  `user_id` int(11) DEFAULT NULL,
										  `action` text,
										  `dtime` datetime DEFAULT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

										-- ----------------------------
										--  Table structure for `admin_users`
										-- ----------------------------
										DROP TABLE IF EXISTS `admin_users`;
										CREATE TABLE `admin_users` (
										  `id` int(11) NOT NULL AUTO_INCREMENT,
										  `username` varchar(32) DEFAULT NULL,
										  `email` text,
										  `password` varchar(32) DEFAULT NULL,
										  `last_login` datetime DEFAULT NULL,
										  `date_created` datetime DEFAULT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

										-- ----------------------------
										--  Table structure for `announcements`
										-- ----------------------------
										DROP TABLE IF EXISTS `announcements`;
										CREATE TABLE `announcements` (
										  `id` int(11) NOT NULL AUTO_INCREMENT,
										  `announcement_type` varchar(12) DEFAULT NULL,
										  `announcement_text` text,
										  `date_added` datetime DEFAULT NULL,
										  `disabled` int(1) DEFAULT '0',
										  PRIMARY KEY (`id`)
										) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

										-- ----------------------------
										--  Table structure for `assistants`
										-- ----------------------------
										DROP TABLE IF EXISTS `assistants`;
										CREATE TABLE `assistants` (
										  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
										  `key_id` int(255) unsigned NOT NULL,
										  `client_apple_account_id` longtext NOT NULL,
										  `assistantid` longtext NOT NULL,
										  `speechid` longtext NOT NULL,
										  `device_type` mediumtext NOT NULL,
                                                                                  `device_OS` text NOT NULL,
										  `date_created` datetime NOT NULL,
										  `last_login` datetime NOT NULL,
										  `last_ip` text NOT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

										-- ----------------------------
										--  Table structure for `clients`
										-- ----------------------------
										DROP TABLE IF EXISTS `clients`;
										CREATE TABLE `clients` (
										  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
										  `fname` mediumtext,
										  `nickname` mediumtext,
										  `apple_db_id` longtext NOT NULL,
										  `apple_account_id` longtext NOT NULL,
										  `valid` enum('False','True') NOT NULL DEFAULT 'True',
										  `devicetype` mediumtext NOT NULL,
										  `deviceOS` text NOT NULL,
										  `date_added` datetime NOT NULL,
										  `last_login` datetime NOT NULL,
										  `last_ip` text NOT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
                                                                                
                                                                                INSERT INTO `clients` VALUES ('1', 'NA', 'NA', 'NA', 'NA', 'False', 'NA', 'NA', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'NA');

										-- ----------------------------
										--  Table structure for `config`
										-- ----------------------------
										DROP TABLE IF EXISTS `config`;
										CREATE TABLE `config` (
										  `id` int(2) NOT NULL,
										  `max_threads` int(5) unsigned NOT NULL DEFAULT '40',
										  `max_connections` int(5) unsigned NOT NULL DEFAULT '50',
										  `active_connections` int(100) unsigned NOT NULL DEFAULT '0',
										  `max_keyload` int(5) unsigned NOT NULL DEFAULT '1800',
										  `keyload_dropdown` int(5) unsigned NOT NULL DEFAULT '600',
										  `keyload_dropdown_interval` int(5) unsigned NOT NULL DEFAULT '600',
										  PRIMARY KEY (`id`)
										) ENGINE=MyISAM DEFAULT CHARSET=utf8;

										-- ----------------------------
										--  Table structure for `faq`
										-- ----------------------------
										DROP TABLE IF EXISTS `faq`;
										CREATE TABLE `faq` (
										  `id` int(11) NOT NULL AUTO_INCREMENT,
										  `question` text,
										  `answer` text,
										  `date_added` datetime DEFAULT NULL,
										  `disabled` int(1) DEFAULT '0',
										  PRIMARY KEY (`id`)
										) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

										-- ----------------------------
										--  Table structure for `guestbook`
										-- ----------------------------
										DROP TABLE IF EXISTS `guestbook`;
										CREATE TABLE `guestbook` (
										  `id` int(11) NOT NULL AUTO_INCREMENT,
										  `name` varchar(128) NOT NULL,
										  `email` varchar(512) NOT NULL,
										  `showEmail` tinyint(1) NOT NULL,
										  `enableEmoticons` tinyint(1) NOT NULL,
										  `message` text NOT NULL,
										  `time` datetime NOT NULL,
										  `ip` varchar(15) NOT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

										-- ----------------------------
										--  Table structure for `ip_bans`
										-- ----------------------------
										DROP TABLE IF EXISTS `ip_bans`;
										CREATE TABLE `ip_bans` (
										  `id` int(11) NOT NULL AUTO_INCREMENT,
										  `ip` varchar(23) DEFAULT NULL,
										  `reason` text,
										  `dtime` datetime DEFAULT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

										-- ----------------------------
										--  Table structure for `ip_logs`
										-- ----------------------------
										DROP TABLE IF EXISTS `ip_logs`;
										CREATE TABLE `ip_logs` (
										  `id` int(11) NOT NULL AUTO_INCREMENT,
										  `ip` varchar(23) NOT NULL,
										  `useragent` text NOT NULL,
										  `referer` text NOT NULL,
										  `currentPage` text NOT NULL,
										  `dtime` datetime NOT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

										-- ----------------------------
										--  Table structure for `key_stats`
										-- ----------------------------
										DROP TABLE IF EXISTS `key_stats`;
										CREATE TABLE `key_stats` (
										  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
										  `key_id` int(255) unsigned NOT NULL,
										  `total_finishspeech_requests` int(255) unsigned NOT NULL,
										  `total_tokens_recieved` int(255) unsigned NOT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

										-- ----------------------------
										--  Table structure for `keys`
										-- ----------------------------
										DROP TABLE IF EXISTS `keys`;
										CREATE TABLE `keys` (
										  `id` int(100) unsigned NOT NULL AUTO_INCREMENT,
										  `assistantid` longtext NOT NULL,
										  `speechid` longtext NOT NULL,
										  `sessionValidation` longtext NOT NULL,
										  `banned` enum('False','True') NOT NULL DEFAULT 'False',
										  `expired` enum('False','True') NOT NULL DEFAULT 'False',
										  `keyload` int(255) unsigned NOT NULL DEFAULT '0',
										  `date_added` datetime NOT NULL,
										  `last_used` datetime NOT NULL,
										  `iPad3` enum('False','True') NOT NULL DEFAULT 'False',
										  `client_apple_account_id` longtext NOT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

										-- ----------------------------
										--  Table structure for `stats`
										-- ----------------------------
										DROP TABLE IF EXISTS `stats`;
										CREATE TABLE `stats` (
										  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
										  `elapsed_key_check_interval` int(255) NOT NULL,
										  `up_time` int(255) NOT NULL,
										  `happy_hour_elapsed` int(255) unsigned NOT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
                                                                                
                                                                                INSERT INTO `stats` VALUES ('1', '0', '0','0');
                                                                                
										-- ----------------------------
										--  Table structure for `website_properties`
										-- ----------------------------
										DROP TABLE IF EXISTS `website_properties`;
										CREATE TABLE `website_properties` (
										  `id` int(11) NOT NULL AUTO_INCREMENT,
										  `property_name` varchar(32) DEFAULT NULL,
										  `property_content` text,
										  `dtime` datetime DEFAULT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

										SET FOREIGN_KEY_CHECKS = 1;

										INSERT INTO `config` (	id,
																max_threads,
																max_connections,
																active_connections,
																max_keyload,
																keyload_dropdown,
																keyload_dropdown_interval)

													VALUES (	'1', 
																'" . $_POST['pc_threads'] . "',
																'" . $_POST['pc_maxCon'] . "',
																'0', 
																'" . $_POST['pc_maxKeyload'] . "',
																'" . $_POST['pc_keyloadDropdown'] . "',
																'" . $_POST['pc_dropdownTime'] . "');

										INSERT INTO `admin_users` (
																username,
																email,
																password,
																last_login,
																date_created)
														VALUES(	'" . mysql_real_escape_string($_POST['admin_username']) . "',
																'" . mysql_real_escape_string($_POST['admin_email']) . "',
																'" . md5($_POST['admin_password']) . "',
																'0000-00-00 00:00:00',
																NOW());

										INSERT INTO `website_properties` (
																property_name,
																property_content,
																dtime)
														VALUES(	'website_title',
																'The Three Little Pigs | Siri Proxy',
																NOW()),

															(	'max_log_entries_per_page',
																'15',
																NOW()),

															(	'hostname_or_ip',
																'" . mysql_real_escape_string($_POST['wp_hostname']). "',
																NOW()),

															(	'max_gb_entries_per_page',
																'" . mysql_real_escape_string($_POST['wp_gbEntries']). "',
																NOW()),

															(	'max_key_entries_per_page',
																'" . mysql_real_escape_string($_POST['wp_kEntries']). "',
																NOW()),

															(	'max_client_entries_per_page',
																'" . mysql_real_escape_string($_POST['wp_cEntries']). "',
																NOW()),

															(	'contact_name',
																'" . mysql_real_escape_string($_POST['wp_cName']). "',
																NOW()),

															(	'contact_email',
																'" . mysql_real_escape_string($_POST['wp_cEmail']). "',
																NOW()),

															(	'accepting_people_in',
																'14400',
																NOW());";
											$queries = explode(';', $queries);
											$fails = 0;
											foreach($queries as $query) {
												if(!mysql_query($query)) {
													$fails++;
												}
											}

											if(fails == 0) {
												$completed = true;
											}
											else {
												$error = 'Something went wrong while executing the queries.';
											}
									}
									else {
										$error = 'Something went wrong while writing the config file.<br />Make sure to set the permissions of the inc folder to 777.';
									}
								}
								else {
										$error = 'Something went wrong while writing the config file.<br />Make sure to set the permissions of the inc folder to 777.';
								}
							}
						}
					}
				}
			}
		}
	}

	echo '	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
			<script type="text/javascript">
				$(document).ready(function () {
					$(\'.tooltip\')
						.hide()
						.prev()
							.focusin(function () {
								$(this).next().show();
							})
							.focusout(function () {
								$(this).next().hide();
							});';
				if(count($inputErrors) > 0) {
					echo 'var errors = new Array();';
					foreach($inputErrors as $error) {
						echo 'errors.push("' . $error . '");';
					}
					echo '$(errors).each(function (key, value) {
						var referenceString;
						$("#" + value)
							.addClass("error")
							.focusin(function () {
								referenceString = $(this).val();
								console.log(referenceString);
							})
							.focusout(function () {
								console.log(referenceString);
								if(referenceString !== $(this).val()) {
									$(this).removeClass("error");
								}
							});
					});';
				}
	echo '		});
			</script>
			<title>Installation The Three Little Pigs - Siri Proxy</title>
			<style type="text/css">
			* {
			    margin: 0;
			    padding: 0;
			}

			body {
			    font-family: Helvetica, Verdana, Arial, sans-serif;
			    font-size: 62.5%;
			    background-color: #f9f9f9;
			    color: #282828;
			}

			h1 {
			    background-color: #33363b;
			    font-size: 2.4em;
			    padding: 48px 0 48px 48px;
			    color: #fff;
			}

			fieldset {
			    position: relative;
			    width: 400px;
			    margin: 24px;
			    border: 0px solid #000;
			    background-color: #fff;
			    padding: 24px;
			}

			fieldset p {
				font-size: 1.3em;
				padding: 15px 0;
				line-height: 20px;
			}

			legend {
			    position: absolute;
			    top: 32px;
			    color: #3b3632;
			    font-size: 2.4em;
			}

			legend > span {
			    background-color: #8aae4c;
			    color: #fff;
			    padding: 12px;
			    margin-right: 12px;
			}

			.form-block {
			    margin-top: 60px;
			}

			label,
			input {
			    display: block;
			    width: 350px;
			    margin-bottom: 6px;
			    font-size: 1.8em;
			}

			input {
			    display: block;
			    border: 1px solid #cdcdcd;
			    font-size: 1.2em;
			    margin: 6px 0 12px 0px;
			    padding: 6px;
			    background-color: #fff;
			    outline: none;
			}

			input[type="submit"] {
			    background-color: #8aae4c;
			    color: #fff;
			    width: auto;
			    margin-top: 24px;
			    cursor: pointer;
			}

			#join {
			    background-color: #8aae4c;
			    color: #fff;
			    width: 72px;
			    margin-top: 24px;
			}

			.error {
			    border-color: #bc1b1b;
				color: #bf0000;
			}

			.success {
				color: #5fbf00;
			}

			.more {
			    cursor: pointer;
			    color: #cdcdcd;
			}
			.more:hover {
			    color: #282828;
			}

			.tooltip {
			    display: block;
			    font-size: 1.2em;
			    margin-bottom: 12px;
			}
			</style>
			</head>
			<body>
			<h1>Installation The Three Little Pigs - Siri Proxy</h1>';
			if($success == true) {
				echo '<fieldset><legend><span>✓</span>Installation feedback</legend><div class="form-block">';
				if(empty($error)) {
					echo '<p class="success">You have successfully installed the web interface.<br />Now please delete this file (install.php) to get started!</p>';
				}
				else {
					echo '<p class="error">' . $error . '<br />' . mysql_error() . '</p>';
				}
				echo '</div></fieldset>';
			}
			else {
			
			echo '<form action="" method="post">
			<fieldset>
				<legend><span>1</span> Database configuration</legend>
				<div class="form-block">';
					if(isset($mysql)) {
						echo '<p class="error">' . $mysql . '</p>';
					}
					echo '<label for="db_host">Host</label>
					<input type="text" name="db_host" id="db_host" value="' . $_POST['db_host'] . '" />
					<span class="tooltip">(Default: localhost)</span>

					<label for="db_name">Name</label>
					<input type="text" name="db_name" id="db_name" value="' . $_POST['db_name'] . '" />
					<span class="tooltip">(Default: siri)</span>

					<label for="db_user">User</label>
					<input type="text" name="db_user" id="db_user" value="' . $_POST['db_user'] . '" />

					<label for="db_pass">Pass</label>
					<input type="password" name="db_pass" id="db_pass" value="' . $_POST['db_pass'] . '" />
				</div>
			</fieldset>
			<fieldset>
				<legend><span>2</span> Proxy configuration</legend>
				<div class="form-block">';
					if(isset($proxyconfig)) {
						echo '<p class="error">' . $proxyconfig . '</p>';
					}
					echo '
					<label for="pc_threads">Max threads</label>
					<input type="text" name="pc_threads" id="pc_threads" value="' . $_POST['pc_threads'] . '" />
					<span class="tooltip">(Recommended: 40)</span>
					
					<label for="pc_maxCon">Max connections</label>
					<input type="text" name="pc_maxCon" id="pc_maxCon" value="' . $_POST['pc_maxCon'] . '" />
					<span class="tooltip">(Recommended: 50)</span>
					
					<label for="pc_maxKeyload">Max keyload</label>
					<input type="text" name="pc_maxKeyload" id="pc_maxKeyload" value="' . $_POST['pc_maxKeyload'] . '" />
					<span class="tooltip">(Recommended: 1800)</span>
					
					<label for="pc_keyloadDropdown">Keyload dropdown</label>
					<input type="text" name="pc_keyloadDropdown" id="pc_keyloadDropdown" value="' . $_POST['pc_keyloadDropdown'] . '" />
					<span class="tooltip">(Recommended: 600)</span>
					
					<label for="pc_dropdownTime">Dropdown time</label>
					<input type="text" name="pc_dropdownTime" id="pc_dropdownTime" value="' . $_POST['pc_dropdownTime'] . '" />
					<span class="tooltip">(Recommended: 600)</span>
				</div>
			</fieldset>
			<fieldset>
				<legend><span>3</span> Admin user</legend>
				<div class="form-block">';
					if(isset($adminuser)) {
						echo '<p class="error">' . $adminuser . '</p>';
					}
					echo '
					<label>Username</label>
					<input type="text" name="admin_username" id="admin_username" value="' . $_POST['admin_username'] . '" />

					<label>Email</label>
					<input type="text" name="admin_email" id="admin_email" value="' . $_POST['admin_email'] . '" />

					<label>Password</label>
					<input type="password" name="admin_password" id="admin_password" value="' . $_POST['admin_password'] . '" />
				</div>
			</fieldset>
			<fieldset>
				<legend><span>4</span> Website properties</legend>
				<div class="form-block">';
					if(isset($websiteproperties)) {
						echo '<p class="error">' . $websiteproperties . '</p>';
					}
					echo '
					<label for="wp_hostname">Hostname or IP</label>
					<input type="text" name="wp_hostname" id="wp_hostname" value="' . $_POST['admin_password'] . '" />
					<span class="tooltip">(Where proxy is running on)</span>

					<label for="dd">Guestbook entries per page</label>
					<input type="text" name="wp_gbEntries" id="wp_gbEntries" value="' . $_POST['wp_gbEntries'] . '" />
					
					<label for="wp_kEntries">Keys per page</label>
					<input type="text" name="wp_kEntries" id="wp_kEntries" value="' . $_POST['wp_kEntries'] . '" />
					
					<label for="wp_cEntries">Clients per page</label>
					<input type="text" name="wp_cEntries" id="wp_cEntries" value="' . $_POST['wp_cEntries'] . '" />
					
					<label for="wp_cName">Contact name</label>
					<input type="text" name="wp_cName" id="wp_cName" value="' . $_POST['wp_cName'] . '" />
					
					<label for="wp_cEmail">Contact email</label>
					<input type="text" name="wp_cEmail" id="wp_cEmail" value="' . $_POST['wp_cEmail'] . '" />
				</div>
			</fieldset>

			<fieldset>
				<legend><span>5</span> Finish installation</legend>
				<div class="form-block">
					<p>Check if all the entered information is correct and then run the install.<br />
					Make sure to <i>delete this file <b>(install.php)</b></i> after the installation.</p>
					<input type="submit" value="Start installation" />
				</div>
			</fieldset>
			</form>';
		}
		
		echo '</body>
			</html>';
?>