<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: admin.php
     * Last update: 22-02-2012
	******************************************************************/

	$admin = new Admin();
	$log = new Log();
	
	if($admin->checkLoggedIn()) {
		echo '<h1>Admin Panel | <a href="?page=' . $_GET['page'] . '&amp;logout=true">Logout</a></h1>';
		if($_GET['logout']) {
			if($admin->logout()) {
				echo '<p class="notification green">You have successfully logged out. Please wait until the page reloads.</p>';
				redirect("?page=" . $_GET['page'], "3");
			}
			else {
				echo '<p class="notification red">Something went wrong while logging you out. Please try again.</p>';
			}
		}
		else {
			// Logged IN Content
			echo '<ul id="subNavigation">';
				echo '<li><a href="?page=' . $_GET['page']  . '&amp;action=home"';
				if($_GET['action'] == "home" || empty($_GET['action'])) {
					echo ' class="current"';
				}
				echo '>Home</a></li>';
				echo '<li><a href="?page=' . $_GET['page']  . '&amp;action=general"';
				if($_GET['action'] == "general") {
					echo ' class="current"';
				}
				echo '>General</a></li>';
				echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=manage-announcements"';
				if($_GET['action'] == "manage-announcements") {
					echo ' class="current"';
				}
				echo '>Manage Announcements</a></li>';
				echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=manage-keys"';
				if($_GET['action'] == "manage-keys") {
					echo ' class="current"';
				}
				echo '>Manage Keys</a></li>';
				echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=manage-clients"';
				if($_GET['action'] == "manage-clients") {
					echo ' class="current"';
				}
				echo '>Manage Clients</a></li>';
				echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=manage-admins"';
				if($_GET['action'] == "manage-admins") {
					echo ' class="current"';
				}
				echo '>Manage Admins</a></li>';
				echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=manage-faq"';
				if($_GET['action'] == "manage-faq") {
					echo ' class="current"';
				}
				echo '>Manage FAQ</a></li>';
				// echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=statistics-and-info"';
				// if($_GET['action'] == "statistics-and-info") {
				// 	echo ' class="current"';
				// }
				// echo '>Statistics</a></li>';
				echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=bans"';
				if($_GET['action'] == "bans") {
					echo ' class="current"';
				}
				echo '>Bans</a></li>';
				echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=logs"';
				if($_GET['action'] == "logs") {
					echo ' class="current"';
				}
				echo '>Logs</a></li>';
			echo '</ul>';
			
			$action = $_GET['action'];
			
			if(empty($_GET['action'])) {
				$action = "home";
			}
			
			switch($action) {
				case "home":
					if($admin->getFieldDataByID($_SESSION['loggedIn']['id'], "last_login") == NULL || $admin->getFieldDataByID($_SESSION['loggedIn']['id'], "last_login") == "0000-00-00 00:00:00") {
						echo '<p class="notification green">Welcome to the admin panel <b>' . $_SESSION['loggedIn']['username'] . '</b>.</p>';
					}
					else {
						echo '<p class="notification green">Welcome back to the admin panel <b>' . $_SESSION['loggedIn']['username'] . '</b>.</p>';
					}
					
					?>
                    <p>Feel free to change and modify everything as you wish.<br />But please consider a donation for all the hard work and time that I've put into this project.</p>
                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHRwYJKoZIhvcNAQcEoIIHODCCBzQCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYAzqbJWdT5N4Fl5I43uVeszAUW0vmCsjZYNqC9rKHqtJNhmDGpM0xVvnGp1mb4e2W6VuTnKtlWBRE/4UUX0tZXDEh2a9mxsylpDEe4WTGHbDtq+ThWJT4S5ppzGRS7TkebUEfwVc2e8El1ttWmjK4qfO56Ik3K4A1yj5LslhX6Q4TELMAkGBSsOAwIaBQAwgcQGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIn6c3ejoHMz6AgaC7SsrANSl0HpP0zM32hlfaIharx9nzD1wR2tBGrsGs425QT0tsvKCrWVZYYhLeMlHkk6TAaYBsVFwZcDFA39hFjA8+EX4+Dp3QUABaYcfEiYtjtc02Pj5r/8uYd3jiKQcNktnEKPvUEtalp8j+VCjxzB8FfmeTzxUIU2wHRrJIxEhuhoc3kYQWa4TDLi8JHjBGX84Z2qkovOEqY24bf1FCoIIDhzCCA4MwggLsoAMCAQICAQAwDQYJKoZIhvcNAQEFBQAwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMB4XDTA0MDIxMzEwMTMxNVoXDTM1MDIxMzEwMTMxNVowgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDBR07d/ETMS1ycjtkpkvjXZe9k+6CieLuLsPumsJ7QC1odNz3sJiCbs2wC0nLE0uLGaEtXynIgRqIddYCHx88pb5HTXv4SZeuv0Rqq4+axW9PLAAATU8w04qqjaSXgbGLP3NmohqM6bV9kZZwZLR/klDaQGo1u9uDb9lr4Yn+rBQIDAQABo4HuMIHrMB0GA1UdDgQWBBSWn3y7xm8XvVk/UtcKG+wQ1mSUazCBuwYDVR0jBIGzMIGwgBSWn3y7xm8XvVk/UtcKG+wQ1mSUa6GBlKSBkTCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb22CAQAwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCBXzpWmoBa5e9fo6ujionW1hUhPkOBakTr3YCDjbYfvJEiv/2P+IobhOGJr85+XHhN0v4gUkEDI8r2/rNk1m0GA8HKddvTjyGw/XqXa+LSTlDYkqI8OwR8GEYj4efEtcRpRYBxV8KxAW93YDWzFGvruKnnLbDAF6VR5w/cCMn5hzGCAZowggGWAgEBMIGUMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbQIBADAJBgUrDgMCGgUAoF0wGAYJKoZIhvcNAQkDMQsGCSqGSIb3DQEHATAcBgkqhkiG9w0BCQUxDxcNMTIwMTI4MDAzMDQ4WjAjBgkqhkiG9w0BCQQxFgQU7yTZUuRNDE3MInMIPMmoP/uR/gwwDQYJKoZIhvcNAQEBBQAEgYAMedV7gXjBcWkkPz/ev6n4SbNE6YtEd1B7jELankW49lXUaV8fGLIaZW6Z+W2YXPmqHgT9fDXS1lO31SajiT8AS/V53OLXk7X7zthdJAkwm84VcvLQr3AnrMkABKHutAtuONOlvTHXR/8C7VkuO3DRKYpUesqbNGE1SmNzH3J+/Q==-----END PKCS7-----
">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
                    <?php
				break;
				
				case "general":
						$announcement = new Announcement();
					echo '
					<div style="overflow: auto;" id="admin">
                    <div style="float: left; width: 160px;">';
                    echo '<ul id="subMenu">';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=website-properties"';
						if($_GET['do'] == 'website-properties' || empty($_GET['do'])) {
							echo ' class="current"';
						}
						echo '>Website properties</a></li>';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=proxy-properties"';
						if($_GET['do'] == 'proxy-properties') {
							echo ' class="current"';
						}
						echo '>Proxy properties</a></li>';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=custom-query"';
						if($_GET['do'] == 'custom-query') {
							echo ' class="current"';
						}
						echo '>Custom Query</a></li>';
                   	echo '</ul>';
                    echo '</div>
                    <div style="float: right; width: 750px;">';
					
					
					if($_GET['do'] == 'website-properties' || empty($_GET['do'])) {
						$wProperty = new WebsiteProperty();
						?>
                        <p>Here you can edit the website properties.</p>
                        <?php
							if($_SERVER['REQUEST_METHOD'] == "POST") {
								$fails = 0;
								$i = 0;
								while(count($_POST['property_id']) > $i) {
									//echo "\nID: " . $_POST['property_id'][$i] . "\nPROPERTY NAME: " .$_POST['property_name'][$i] . "\nPROPERTY CONTENT: " . $_POST['property_content'][$i++] . "\n";
									
									if(!$wProperty->updateProperty($_POST['property_id'][$i], $_POST['property_name'][$i], $_POST['property_content'][$i++])) {
										$fails++;
									}
									
								}
								if($fails == 0) {
									echo '<p class="notification green">You have successfully updated the website properties.</p>';
									$log->addLog($_SESSION['loggedIn']['id'], "Updated website properties.");
									$hideForm = true;
								}
								else {
									echo '<p class="notification red">Something went wrong while updating the website properties.</p>';
								}
							}
							if($hideForm !== true) {
						?>
                        <form action="" method="post" class="styled">
                        <table width="100%">
                        	<tr>
                            	<th width="20px">#</th>
                                <th width="150px">Property name</th>
                                <th>Property content</th>
                                <th width="150px">Time added</th>
                           	</tr>
                        <?php
						$dataArr = $wProperty->getProperties();
						if($dataArr == false) {
							echo '<tr><td colspan="4"><p class="notification red minimal">There are no website properties that can be edited.</p>';
						}
						else {
							$i = 0;
							foreach($dataArr as $data) {
								echo '<tr>';
								echo '<td>' . ++$i . '<input type="hidden" name="property_id[]" value="' . $data['id'] . '" /></td>';
								echo '<td><input style="width: 140px;" type="text" name="property_name[]" value="' . $data['property_name'] . '" /></td>';
								echo '<td><input style="width: 340px;" type="text" name="property_content[]" value="' . $data['property_content'] . '" /></td>';
								echo '<td>' . $data['dtime'] . '</td>';
								echo '</tr>';
							}
						}
						if($dataArr !== false) {
						?>
                        <tr><td colspan="4"><input style="margin-left: 5px;" type="submit" value="Save website properties" /></td></tr>
                        <?php } ?>
                        </table>
                        </form>
                        <?php
							}
					}
					elseif($_GET['do'] == 'proxy-properties') {
						echo '<p>On this page you can edit the proxy configuration.</p>';
						if($_SERVER[REQUEST_METHOD] == "POST") {
							$query = 'UPDATE `config` SET ';
							$reverse = array_flip($_POST);
							$i = 0;
							foreach($_POST as $data) {
								$query .= $reverse[$data] . ' = \'' . $data . '\'';
								if(count($_POST) !== ++$i) {
									$query .= ', ';
								}
							}
							$query .= ' WHERE id = \'1\'';

							if($query) {
								echo '<p class="notification green">The proxy configuration has been updated successfully.</p>';
								$log->addLog($_SESSION['loggedIn']['id'], "Updated the proxy configuration.");
								$hideForm = true;
							}
							else {
								echo '<p class="notification red">Something went wrong while updating the proxy configuration, try again.</p>';
							}
						}

						if($hideForm !== true) {
						$config = getconfig();

						?>

                        <form action="" method="post" class="styled">
                        <table width="100%">
                        	<tr>
                            	<th width="20px">#</th>
                                <th width="150px">Property name</th>
                                <th>Property content</th>
                           	</tr>
                           	<?php
                           	$reverse = array_flip($config);
							foreach($config as $data) {
								if($reverse[$data] !== "id") {
									echo '<tr>';
									echo '<td>' . ++$i . '</td>';
									echo '<td>' . $reverse[$data] . '</td>';
									echo '<td><input style="width: 340px;" type="text" name="' . $reverse[$data] . '" value="' . $data . '" /></td>';
									echo '</tr>';
								}
							}
							echo '
                        <tr><td colspan="3"><input style="margin-left: 5px;" type="submit" value="Save proxy properties" /></td></tr>';
							echo '</table></form>';
							echo '<p class="notification yellow" style="margin-top: 15px;">Changes will only take effect if the proxy server is not running. Hope to fix this soon.</p>';
						}
					}
					elseif($_GET['do'] == 'custom-query') {
						echo '<p>You can enter custom SQL queries here. Please only use this if you know what you are doing.</p>';
						if($_SERVER['REQUEST_METHOD'] == "POST") {
							if(!empty($_POST['query'])) {
								$query = mysql_query($_POST['query']);
								if($query) {
									echo '<p class="notification green">Query executed successfully.</p>';
									if($query !== true && $query !== false) {
										if(mysql_num_rows($query) > 0) {
											$data = mysql_fetch_assoc($query);
											echo '<pre class="notification green">Query data: ';
											print_r($data);
											echo '</pre>';
										}
									}
									$log->addLog($_SESSION['loggedIn']['id'], "Executed a custom query.");
								}
								else {
									echo '<p class="notification red">Query error: ' . mysql_error() . '</p>';
								}
							}
						}
						?>
                    <form action="" method="post" class="styled">
                        <label style="width: 100px;">SQL Query</label>
                    
                        <input type="text" name="query" value="" />

                        <input style="margin-left: 25px;" type="submit" value="Execute query" />
                        	
                    </form>
                    <?php

					}
                    echo '</div>
                    </div>';
					
				break;
				
				case "manage-announcements":
					$announcement = new Announcement();
					echo '
					<div style="overflow: auto;" id="admin">
                    <div style="float: left; width: 160px;">';
                    echo '<ul id="subMenu">';
                    	echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=add-announcement"';
						if($_GET['do'] == 'add-announcement' || empty($_GET['do'])) {
							echo ' class="current"';
						}
						echo '>Add announcement</a></li>';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=edit-announcement"';
						if($_GET['do'] == 'edit-announcement') {
							echo ' class="current"';
						}
						echo '>Edit announcement</a></li>';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=delete-announcement"';
						if($_GET['do'] == 'delete-announcement') {
							echo ' class="current"';
						}
						echo '>Delete announcement</a></li>';
                   	echo '</ul>';
                    echo '</div>
                    <div style="float: right; width: 750px;">';
if($_GET['do'] == 'add-announcement' || empty($_GET['do'])) {
					?>
                    <p>You can add an announcement, which will be visible on all pages, on top of the content.</p>
                    <?php
						if($_SERVER['REQUEST_METHOD'] == "POST") {
							if(!empty($_POST['announcement_text']) && !empty($_POST['announcement_type'])) {
								if($announcement->addAnnouncement($_POST['announcement_type'], $_POST['announcement_text'])) {
									echo '<p class="notification green">You have successfully added a new announcement.</p>';
									$log->addLog($_SESSION['loggedIn']['id'], "Added a new announcement.");
									$hideform = true;
								}
								else {
									echo '<p class="notification red">Something went wrong while adding the new announcement. Please try again.</p>';
								}
							}
							else {
								echo '<p class="notification red">Please fill in all the fields.</p>';
							}
						}
						if($hideform !== true) {
					?>
                    <form action="" method="post" class="styled">
                        <label style="width: 140px;">Announcement text</label>
                    
                        <input type="text" name="announcement_text" value="" /> <span class="note">(Can contain HTML)</span>
                        <br />
                        
                        <label style="width: 140px;">Announcement type</label>
                    
                        <select name="announcement_type">
                        	<option>Green</option>
                            <option>Yellow</option>
                            <option>Red</option>
                        </select>
                        <br />
                        
                        <input style="margin-left: 155px;" type="submit" value="Add announcement" />
                        	
                    </form>
                    <?php
						}
					}
					elseif($_GET['do'] == 'edit-announcement') {
						?>
                        <p>Here you can edit active announcements.</p>
                        <?php
							if($_SERVER['REQUEST_METHOD'] == "POST") {
								$fails = 0;
								$i = 0;
								while(count($_POST['announcement_id']) > $i) {
									//echo "\n ID: " . $_POST['announcement_id'][$i] . "\nTYPE: " .$_POST['announcement_type'][$i] . "\n TEXT: " . $_POST['announcement_text'][$i++] . "\n";
									
									if(!$announcement->updateAnnouncement($_POST['announcement_id'][$i], $_POST['announcement_type'][$i], $_POST['announcement_text'][$i++])) {
										$fails++;
									}
									
								}
								if($fails == 0) {
									echo '<p class="notification green">You have successfully updated the announcements.</p>';
									$log->addLog($_SESSION['loggedIn']['id'], "Updated announcements.");
									$hideForm = true;
								}
								else {
									echo '<p class="notification red">Something went wrong while updating the announcements.</p>';
								}
							}
							if($hideForm !== true) {
						?>
                        <form action="" method="post" class="styled">
                        <table width="100%">
                        	<tr>
                            	<th width="20px">#</th>
                                <th width="150px">Announcement type</th>
                                <th>Announcement text</th>
                                <th width="150px">Time added</th>
                           	</tr>
                        <?php
						$dataArr = $announcement->getAnnouncements(true);
						if($dataArr == false) {
							echo '<tr><td colspan="4"><p class="notification red minimal">There are no active announcements that can be edited.</p>';
						}
						else {
							$i = 0;
							foreach($dataArr as $data) {
								echo '<tr>';
								echo '<td>' . ++$i . '<input type="hidden" name="announcement_id[]" value="' . $data['id'] . '" /></td>';
								echo '<td><select name="announcement_type[]" style="width: 125px;">';
								echo '<option';
								if($data['announcement_type'] == "Green") {
									echo ' selected="selected"';
								}
								echo '>Green</option>';
								echo '<option';
								if($data['announcement_type'] == "Yellow") {
									echo ' selected="selected"';
								}
								echo '>Yellow</option>';
								echo '<option';
								if($data['announcement_type'] == "Red") {
									echo ' selected="selected"';
								}
								echo '>Red</option>';
								echo '</select></td>';
								echo '<td><input style="width: 340px;" type="text" name="announcement_text[]" value="' . $data['announcement_text'] . '" /></td>';
								echo '<td>' . $data['date_added'] . '</td>';
								echo '</tr>';
							}
						}
						if($dataArr !== false) {
						?>
                        <tr><td colspan="4"><input style="margin-left: 5px;" type="submit" value="Save announcements" /></td></tr>
                        <?php } ?>
                        </table>
                        </form>
                        <?php
							}
						
					}
					elseif($_GET['do'] == 'delete-announcement') {
						echo '<p>You can delete active announcements here.</p>';
						if(!empty($_GET['delete'])) {
							if(is_numeric($_GET['delete'])) {
								if($announcement->deleteAnnouncement($_GET['delete'])) {
									echo '<p class="notification green">You have successfully deleted an announcement.</p>';
									$log->addLog($_SESSION['loggedIn']['id'], "Deleted an announcement.");
								}
							}
						}
						?>
                        
                        
                        <table width="100%">
                        	<tr>
                            	<th width="20px">#</th>
                                <th width="150px">Announcement type</th>
                                <th>Announcement text</th>
                                <th width="150px">Time added</th>
                                <th width="50px">Delete</th>
                           	</tr>
                        <?php
						$dataArr = $announcement->getAnnouncements(true);
						if($dataArr == false) {
							echo '<tr><td colspan="5"><p class="notification red minimal">There are no active announcements that can be deleted.</p>';
						}
						else {
							$i = 0;
							foreach($dataArr as $data) {
								echo '<tr>';
								echo '<td>' . ++$i . '<input type="hidden" name="announcement_id[]" value="' . $data['id'] . '" /></td>';
								echo '<td>' . $data['announcement_type'] . '</td>';
								echo '<td>' . shortString($data['announcement_text'], 40) . '</td>';
								echo '<td>' . $data['date_added'] . '</td>';
								echo '<td style="text-align: center;"><a class="image" href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;delete=' . $data['id'] . '">';
								echo '<img src="design/img/delete.png" alt="delete" /></a></td>';
								echo '</tr>';
							}
						}
						?>
                        </table>
                        <?php
					}
                    echo '</div></div>';
				break;
				
				case "manage-keys":
				
				
						$key = new Key();
					echo '
					<div style="overflow: auto;" id="admin">
                    <div style="float: left; width: 160px;">';
                    echo '<ul id="subMenu">';
                    	echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=add-key"';
						if($_GET['do'] == 'add-key' || empty($_GET['do'])) {
							echo ' class="current"';
						}
						echo '>Add Key</a></li>';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=edit-key"';
						if($_GET['do'] == 'edit-key') {
							echo ' class="current"';
						}
						echo '>Edit Key</a></li>';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=delete-key"';
						if($_GET['do'] == 'delete-key') {
							echo ' class="current"';
						}
						echo '>Delete Key</a></li>';
                   	echo '</ul>';
                    echo '</div>
                    <div style="float: right; width: 750px;">';
					
					
					if($_GET['do'] == 'add-key' || empty($_GET['do'])) {
					?>
                    <p>You can add keys here.</p>
                    <?php
						if($_SERVER['REQUEST_METHOD'] == "POST") {
							if(!empty($_POST['key_sessionValidation']) && !empty($_POST['key_banned']) && !empty($_POST['key_expired']) && !empty($_POST['iPad3'])) {
								if($key->addKey( $_POST['key_assistantid'], $_POST['key_speechid'],$_POST['key_sessionValidation'], $_POST['key_banned'], $_POST['key_expired'], $_POST['key_keyload'], $_POST['iPad3'] '')) {
                                                                        echo '<p class="notification green">You have successfully added a new '
                                                                            if ($_POST['iPad3'] == false) {
                                                                                echo '4S';
                                                                            }
                                                                            else {
                                                                                echo 'iPad 3';
                                                                            }
                                                                            'key.</p>';
									$log->addLog($_SESSION['loggedIn']['id'], 'Added a new '. $_POST['iPad3'] .' key.');
									$hideform = true;
								}
								else {
									echo '<p class="notification red">Something went wrong while adding the new key. Please try again.</p>';
								}
							}
							else {
								echo '<p class="notification red">All fields are required except Speech ID, Assistant ID and Apple Account ID.</p>';
							}
						}
						if($hideform !== true) {
					?>
                    <form action="" method="post" class="styled">
                        <label style="width: 170px;">Speech ID</label>
                    
                        <input type="text" name="key_speechid" value="" />
                        <br />
                        <label style="width: 170px;">Assistant ID</label>
                    
                        <input type="text" name="key_assistantid" value="" />
                        <br />
                        
                        <label style="width: 170px;">Session Validation Data</label>
                        <textarea cols="" rows="" name="key_sessionValidation" style="height: 100px;"></textarea>
                        <br />

                        <label style="width: 170px;">Banned</label>
                        <select name="key_banned">
                        	<option>False</option>
                        	<option>True</option>
                        </select>
                        <br />

                        <label style="width: 170px;">Expired</label>
                        <select name="key_expired">
                        	<option>False</option>
                        	<option>True</option>
                        </select>
                        <br />

                        <label style="width: 170px;">Keyload</label>
                    
                        <input type="text" name="key_keyload" value="0" />
                        <br />

                        <label style="width: 170px;">iPad3</label>
                        <select name="iPad3">
                        	<option>False</option>
                        	<option>True</option>
                        	<option label="Sorta (Only for iPad3's on iOS 6+)">Sorta</option>
                        </select>
                        <br />

<!--                        <label style="width: 170px;">Apple Account ID</label>
                    
                        <input type="text" name="client_apple_account_id" value="0" />
                        <br />-->
                        
                        <input style="margin-left: 185px;" type="submit" value="Add Key" />
                        	
                    </form>
                    <?php
						}
					}
					elseif($_GET['do'] == 'edit-key') {
					
					if(!empty($_GET['unban']) && is_numeric($_GET['unban'])) {
						if($key->checkValueExists("id", $_GET['unban'])) {
							$status = $key->getFieldDataByID($_GET['unban'], "banned");

							if($status == "True") {
								$status = "False";
							}
							else {
								$status = "True";
							}

							if($key->updateFieldByID($_GET['unban'], "banned", $status)) {
								echo '<p class="notification green">You have successfully (un)banned a key.</p>';
								$log->addLog($_SESSION['loggedIn']['id'], "(Un)banned a key.");
							}
						}
						else {
							echo '<p class="notification red">There was no such key found with this ID.</p>';
						}
					}
					elseif(!empty($_GET['edit']) && is_numeric($_GET['edit'])) {
						$showTable = true;
						if($key->checkValueExists("id", $_GET['edit'])) {
							$showTable = false;
							if($_SERVER['REQUEST_METHOD'] == "POST") {
								if(!empty($_POST['key_sessionValidation']) && !empty($_POST['key_banned']) && !empty($_POST['key_expired'])) {

									if($key->updateFieldByID($_GET['edit'], "speechid", $_POST['key_speechid']) && $key->updateFieldByID($_GET['edit'], "assistantid", $_POST['key_assistantid']) && $key->updateFieldByID($_GET['edit'], "sessionValidation", $_POST['key_sessionValidation']) && $key->updateFieldByID($_GET['edit'], "banned", $_POST['key_banned']) && $key->updateFieldByID($_GET['edit'], "expired", $_POST['key_expired']) && $key->updateFieldByID($_GET['edit'], "keyload", $_POST['key_keyload'])) {
										echo '<p class="notification green">You have successfully update a key.</p>';
										$log->addLog($_SESSION['loggedIn']['id'], "Updated a key.");
										$hideform = true;
										$showTable = true;
									}
									else {
										echo '<p class="notification red">Something went wrong while adding the new key. Please try again.</p>';
									}
								}
								else {
									echo '<p class="notification red">All fields are required except Speech ID and Assistant ID.</p>';
								}
							}
							if($hideform !== true) {
						?>
	                    <form action="" method="post" class="styled">
	                        <label style="width: 170px;">Speech ID</label>
	                    
	                        <input type="text" name="key_speechid"  value="<?php echo $key->getFieldDataByID($_GET['edit'], "speechid"); ?>" />
	                        <br />
	                        <label style="width: 170px;">Assistant ID</label>
	                    
	                        <input type="text" name="key_assistantid" value="<?php echo $key->getFieldDataByID($_GET['edit'], "assistantid"); ?>" />
	                        <br />
	                        
	                        <label style="width: 170px;">Session Validation Data</label>
	                        <textarea cols="" rows="" name="key_sessionValidation" style="height: 100px;">
	                        <?php echo $key->getFieldDataByID($_GET['edit'], "sessionValidation"); ?>
	                        </textarea>
	                        <br />

	                        <label style="width: 170px;">Banned</label>
	                        <select name="key_banned">
	                        	<option>False</option>
	                        	<option<?php if($key->getFieldDataByID($_GET['edit'], "banned") == "True") { echo ' selected="selected"'; } ?>>True</option>
	                        </select>
	                        <br />

	                        <label style="width: 170px;">Expired</label>
	                        <select name="key_expired">
	                        	<option>False</option>
	                        	<option<?php if($key->getFieldDataByID($_GET['edit'], "expired") == "True") { echo ' selected="selected"'; } ?>>True</option>
	                        </select>
	                        <br />

	                        <label style="width: 170px;">Keyload</label>
	                    
	                        <input type="text" name="key_keyload" value="<?php echo $key->getFieldDataByID($_GET['edit'], "keyload"); ?>" />
	                        <br />
	                        
	                        <input style="margin-left: 185px;" type="submit" value="Update Key" />
	                        	
	                    </form>
	                    <?php
							}
						}
					}
					
					if($showTable !== false) {

					$websiteProperty = new WebsiteProperty();

					$pid = $_GET['page-id'];
					
					if(empty($pid)) { $pid = 1; }
					
					$lastPage = ceil($key->getTotalRecordCount()/$websiteProperty->getProperty('max_key_entries_per_page'));
					
					if($pid < 1) { 
						$pid = 1;
					}
					elseif($pid > $lastPage) {
						$pid = $lastPage;
					}
					

					$dataArr = $key->getKeys($websiteProperty->getProperty('max_key_entries_per_page'),
												   ($pid - 1) * $websiteProperty->getProperty('max_key_entries_per_page'));
					 
		
						echo '<p>Here you can edit existing keys.</p>';
                        
                        echo '<table width="100%">
                        	<tr>
                            	<th width="20px">#</th>
                                <th>Validation Data Hash</th>
                                <th>Date Added</th>
                                <th width="50px">Banned</th>
                                <th width="50px">Expired</th>
                                <th width="50px">(Un)ban</th>
                                <th width="35px">Edit</th>
                           	</tr>';
                        
						if($dataArr == false) {
							echo '<tr><td colspan="6"><p class="notification red minimal">There are no existing keys that can be edited.</p>';
						}
						else {
							$i = ($pid - 1) * $websiteProperty->getProperty('max_key_entries_per_page');
							foreach($dataArr as $data) {
								echo '<tr>';
								echo '<td>' . ++$i . '</td>';
								echo '<td>' . md5($data['sessionValidation']) . '</td>';
								echo '<td>' . $data['date_added'] . '</td>';
								echo '<td><p class="notification minimal ';
								if($data['banned'] == "True") {
									echo 'red';
								}
								else {
									echo 'green';
								}
								echo '">' . $data['banned'] . '</p></td>';
								echo '<td><p class="notification minimal ';
								if($data['expired'] == "True") {
									echo 'red';
								}
								else {
									echo 'green';
								}
								echo '">' . $data['expired'] . '</p></td>';
								echo '<td style="text-align: center;"><a class="image" href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;unban=' . $data['id'] . '">';
								echo '<img src="design/img/refresh.png" alt="unban" /></a></td>';
								echo '<td style="text-align: center;"><a class="image" href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;edit=' . $data['id'] . '">';
								echo '<img src="design/img/edit.png" alt="edit" /></a></td>';
								echo '</tr>';
							}
						}
						echo '</table>';

						
	echo "<div id='pagination'>";
		
		if ($pid > 1) 

 {

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=1'>&lt;&lt;</a> ";

 echo " ";

 $previous = $pid-1;

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$previous'>&lt;</a> ";

 } 


			if(($pid - 3) > 0) {
				$i = $pid - 3;
			}
			else {
				$i = 1;
			}
			if(($pid + 3) <= $lastPage) {
				$mPage = $pid + 3;
			}
			else {
				$mPage = $lastPage;
			}
			while($i <= $mPage) {
				echo '<a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;page-id=' . $i . '"';
				if($pid == $i) {
					echo ' class="current"';
				}
				echo '>' . $i . '</a>';
				$i++;
			}


 //This does the same as above, only checking if we are on the last page, and then generating the Next and Last links

 if ($pid < $lastPage) 

 {

 $next = $pid+1;

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$next'>&gt;</a> ";

 echo " ";

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$lastPage'>&gt;&gt;</a>";

 } 
   echo ' </div>';
}

					}
					elseif($_GET['do'] == 'delete-key') {
						echo '<p>Here you can delete existing keys.</p>';
						if(!empty($_GET['delete'])) {
							if(is_numeric($_GET['delete'])) {
								if($key->deleteKey($_GET['delete'])) {
									echo '<p class="notification green">You have successfully deleted a key.</p>';
									$log->addLog($_SESSION['loggedIn']['id'], "Deleted a key.");
								}
							}
						}		$websiteProperty = new WebsiteProperty();

					$pid = $_GET['page-id'];
					
					if(empty($pid)) { $pid = 1; }
					
					$lastPage = ceil($key->getTotalRecordCount()/$websiteProperty->getProperty('max_key_entries_per_page'));
					
					if($pid < 1) { 
						$pid = 1;
					}
					elseif($pid > $lastPage) {
						$pid = $lastPage;
					}
					

					$dataArr = $key->getKeys($websiteProperty->getProperty('max_key_entries_per_page'),
												   ($pid - 1) * $websiteProperty->getProperty('max_key_entries_per_page'));
					 
		
                        
                        echo '<table width="100%">
                        	<tr>
                            	<th width="20px">#</th>
                                <th>Validation Data Hash</th>
                                <th>Date Added</th>
                                <th width="50px">Banned</th>
                                <th width="50px">Expired</th>
                                <th width="35px">Delete</th>
                           	</tr>';
                        
						if($dataArr == false) {
							echo '<tr><td colspan="6"><p class="notification red minimal">There are no existing keys that can be edited.</p>';
						}
						else {
							$i = ($pid - 1) * $websiteProperty->getProperty('max_key_entries_per_page');
							foreach($dataArr as $data) {
								echo '<tr>';
								echo '<td>' . ++$i . '</td>';
								echo '<td>' . md5($data['sessionValidation']) . '</td>';
								echo '<td>' . $data['date_added'] . '</td>';
								echo '<td><p class="notification minimal ';
								if($data['banned'] == "True") {
									echo 'red';
								}
								else {
									echo 'green';
								}
								echo '">' . $data['banned'] . '</p></td>';
								echo '<td><p class="notification minimal ';
								if($data['expired'] == "True") {
									echo 'red';
								}
								else {
									echo 'green';
								}
								echo '">' . $data['expired'] . '</p></td>';
								echo '<td style="text-align: center;"><a class="image" href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;delete=' . $data['id'] . '">';
								echo '<img src="design/img/delete.png" alt="delete" /></a></td>';
								echo '</tr>';
							}
						}
						echo '</table>';

						
	echo "<div id='pagination'>";
		
		if ($pid > 1) 

 {

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=1'>&lt;&lt;</a> ";

 echo " ";

 $previous = $pid-1;

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$previous'>&lt;</a> ";

 } 


			if(($pid - 3) > 0) {
				$i = $pid - 3;
			}
			else {
				$i = 1;
			}
			if(($pid + 3) <= $lastPage) {
				$mPage = $pid + 3;
			}
			else {
				$mPage = $lastPage;
			}
			while($i <= $mPage) {
				echo '<a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;page-id=' . $i . '"';
				if($pid == $i) {
					echo ' class="current"';
				}
				echo '>' . $i . '</a>';
				$i++;
			}


 //This does the same as above, only checking if we are on the last page, and then generating the Next and Last links

 if ($pid < $lastPage) 

 {

 $next = $pid+1;

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$next'>&gt;</a> ";

 echo " ";

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$lastPage'>&gt;&gt;</a>";

 } 
   echo ' </div>';
					}
						echo '</div>';
						echo '</div>';
				break;
				
				case "manage-clients":
				$client = new Client();
				echo '
					<div style="overflow: auto;" id="admin">
                    <div style="float: left; width: 160px;">';
                    echo '<ul id="subMenu">';
                    	echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=add-client"';
						if($_GET['do'] == 'add-client' || empty($_GET['do'])) {
							echo ' class="current"';
						}
						echo '>Add Client</a></li>';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=edit-client"';
						if($_GET['do'] == 'edit-client') {
							echo ' class="current"';
						}
						echo '>Edit Client</a></li>';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=delete-client"';
						if($_GET['do'] == 'delete-client') {
							echo ' class="current"';
						}
						echo '>Delete Client</a></li>';
                   	echo '</ul>';
                    echo '</div>
                    <div style="float: right; width: 750px;">';
					if($_GET['do'] == 'add-client' || empty($_GET['do'])) {
						echo '<p>On this page you can add a client.</p>';
						if($_SERVER['REQUEST_METHOD'] == "POST") {
							if(empty($_POST['client_firstname']) || empty($_POST['client_nickname']) || empty($_POST['client_valid'])) {
								echo '<p class="notification red">All fields are required.</p>';
							}
							else {
								if(strlen($_POST['client_nickname']) > 32) {
									echo '<p class="notification red">The client username cannot contain more than 32 characters!</p>';
								}
								elseif($_POST['client_valid'] !== "True" && $_POST['client_valid'] !== "False") {
									echo '<p class="notification red">The client status is not valid.</p>';
								}
								else {
									if($client->addClient($_POST['client_firstname'], $_POST['client_nickname'], $_POST['client_valid'])) {
										echo '<p class="notification green">You have successfully added a client user.</p>';
										$log->addLog($_SESSION['loggedIn']['id'], "Added a client.");
										$hideform = true;
									}
								}
							}
						}
							
						if($hideform !== true) {
							?>
							<form action="" method="post" class="styled">
								<label style="width: 150px;">Client Firstname</label>
							
								<input type="text" name="client_firstname" />
								<br />
								<label style="width: 150px;">Client Nickname</label>
							
								<input type="text" name="client_nickname" />
								<br />

								<label style="width: 150px;">Client Valid</label>
								<select name="client_valid">
									<option>True</option>
									<option>False</option>
								</select>
								<br />
								
								<input style="margin-left: 165px;" type="submit" value="Add Client" />
							</form>
							<?php
						}
					}
					elseif($_GET['do'] == "edit-client") {

						$websiteProperty = new WebsiteProperty();

						echo '<p>On this page you can edit clients.</p>';
            
						if(!empty($_GET['invert']) && is_numeric($_GET['invert'])) {
							$status = $client->getFieldDataByID($_GET['invert'], "valid");

							if($status !== false) {
								if($status == "True") {
									$status = "False";
								}
								else {
									$status = "True";
								}
									
							}
							if($client->updateFieldByID($_GET['invert'], "valid", $status)) {
								echo '<p class="notification green">You have successfully inverted the status of a client.</p>';
								$log->addLog($_SESSION['loggedIn']['id'], "Updated a client status.");
							}
							else {
								echo '<p class="notification red">Something went wrong while updating the status of the client.</p>';
							}
						}
						elseif(!empty($_GET['edit']) && is_numeric($_GET['edit'])) {
							$showTable = true;
							if($client->checkValueExists("id", $_GET['edit'])) {
								$showTable = false;
								if($_SERVER['REQUEST_METHOD'] == "POST") {
									if(empty($_POST['client_firstname']) || empty($_POST['client_nickname']) || empty($_POST['client_valid'])) {
										echo '<p class="notification red">All fields are required.</p>';
									}
									else {
										if(strlen($_POST['client_nickname']) > 32) {
											echo '<p class="notification red">The client username cannot contain more than 32 characters!</p>';
										}
										elseif($_POST['client_valid'] !== "True" && $_POST['client_valid'] !== "False") {
											echo '<p class="notification red">The client status is not valid.</p>';
										}
										else {
											if(	$client->updateFieldByID($_GET['edit'], "fname", $_POST['client_firstname']) &&
												$client->updateFieldByID($_GET['edit'], "nickname", $_POST['client_nickname']) &&
												$client->updateFieldByID($_GET['edit'], "valid", $_POST['client_valid'])) {
													echo '<p class="notification green">You have successfully updated a client user.</p>';
													$log->addLog($_SESSION['loggedIn']['id'], "Updated a client.");
												$hideform = true;
												$showTable = true;
											}
										}
									}
								}
							
								if($hideform !== true) {
							?>
							<form action="" method="post" class="styled">
								<label style="width: 150px;">Client Firstname</label>
							
								<input type="text" name="client_firstname" value="<?php echo $client->getFieldDataByID($_GET['edit'], "fname"); ?>" />
								<br />
								<label style="width: 150px;">Client Nickname</label>
							
								<input type="text" name="client_nickname" value="<?php echo $client->getFieldDataByID($_GET['edit'], "nickname"); ?>" />
								<br />

								<label style="width: 150px;">Client Valid</label>
								<select name="client_valid">
									<option>True</option>
									<option
									<?php
										if($client->getFieldDataByID($_GET['edit'], "valid") == "False")
											echo ' selected="selected"';
									?>>False</option>
								</select>
								<br />
								
								<input style="margin-left: 165px;" type="submit" value="Edit Client" />
							</form>
							<?php
								}
							}
							else {
								echo '<p class="notification red">There was no such client found with this ID.</p>';
							}
						}
						
						
   if($showTable !== false) {
   	echo '
    <table width="100%">
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Nickname</th>
            <th>Device</th>
            <th>Valid</th>
            <th>Last Login</th>
            <th>Last IP</th>
            <th>Date Added</th>
            <th>Invert Status</th>
            <th>Edit</th>
        </tr>';

	$pid = $_GET['page-id'];
	
	if(empty($pid)) { $pid = 1; }
	
	$lastPage = ceil($client->getTotalRecordCount()/$websiteProperty->getProperty('max_client_entries_per_page'));
	
	if($pid < 1) { 
		$pid = 1;
	}
	elseif($pid > $lastPage) {
		$pid = $lastPage;
	}

	if($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_POST['search'])) {
		$dataArr = $client->getClientsLike($_POST['search']);
	}
	else {
		$dataArr = $client->getClients($websiteProperty->getProperty('max_client_entries_per_page'),
								   ($pid - 1) * $websiteProperty->getProperty('max_client_entries_per_page'));
	}	   
		
	
	if($dataArr !== false) {
		foreach($dataArr as $data) {
			echo '<tr>';
			echo '<td>' . $data['id'] . '</td>';
			echo '<td>';
			if($data['fname'] == "NA") {
				echo '<p class="notification red minimal">n/a</p>';
			}
			else {
				echo $data['fname'];
			}
			echo '</td>';
			echo '<td>';
			if($data['nickname'] == "NA") {
				echo '<p class="notification red minimal">n/a</p>';
			}
			else {
				echo $data['nickname'];
			}
			echo '</td>';
			echo '<td>' . $data['device_type'] . '</td>';
			echo '<td width="50px">';
			echo '<p class="notification ';
			if($data['valid'] == 'True') {
				echo 'green';
			}
			else {
				echo 'red';
			}
			echo ' minimal">' . $data['valid'];
			echo '</td>';
			echo '<td width="150px">' . $data['last_login'] . '</td>';
			echo '<td width="150px">' . $data['last_ip'] . '</td>';
			echo '<td width="150px">' . $data['date_added'] . '</td>';
									echo '<td style="text-align: center;" width="100px"><a class="image" href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;invert=' . $data['id'] . '">';
									echo '<img src="design/img/refresh.png" alt="invert" /></a></td>';
									echo '<td style="text-align: center;" width="50px"><a class="image" href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;edit=' . $data['id'] . '">';
									echo '<img src="design/img/edit.png" alt="edit" /></a></td>';
			echo '</tr>';
		}
	echo '
	<tr><td colspan="10"><form class="styled" action="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '" method="post"><label style="width: 180px;">First name or nickname</label>
   				<input type="text" name="search" style="width: 200px;" />
                <input type="submit" value="Search" style="margin-left: 25px;"/></form></td></tr>
	</table>';
	
	echo "<div id='pagination'>";
		
		if ($pid > 1) 

 {

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=1'>&lt;&lt;</a> ";

 echo " ";

 $previous = $pid-1;

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$previous'>&lt;</a> ";

 } 


			if(($pid - 3) > 0) {
				$i = $pid - 3;
			}
			else {
				$i = 1;
			}
			if(($pid + 3) <= $lastPage) {
				$mPage = $pid + 3;
			}
			else {
				$mPage = $lastPage;
			}
			while($i <= $mPage) {
				echo '<a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;page-id=' . $i . '"';
				if($pid == $i) {
					echo ' class="current"';
				}
				echo '>' . $i . '</a>';
				$i++;
			}


 //This does the same as above, only checking if we are on the last page, and then generating the Next and Last links

 if ($pid < $lastPage) 

 {

 $next = $pid+1;

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$next'>&gt;</a> ";

 echo " ";

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$lastPage'>&gt;&gt;</a>";

 } 
   echo ' </div>';
					}
				else {
					echo '<p class="notification red">There were no clients found.</p>';
				}
				}

		}
					elseif($_GET['do'] == "delete-client") {


						$websiteProperty = new WebsiteProperty();

						echo '<p>On this page you can delete clients.</p>';

						if(!empty($_GET['delete']) && is_numeric($_GET['delete'])) {
							if($client->checkValueExists("id", $_GET['delete'])) {
								if($client->deleteClient($_GET['delete'])) {
									echo '<p class="notification green">You have successfully deleted a client.</p>';
									$log->addLog($_SESSION['loggedIn']['id'], "Deleted a client.");
								}
							}
							else {
								echo '<p class="notification red">There was no such client found with this ID.</p>';
							}
						}
						
   	echo '
    <table width="100%">
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Nickname</th>
            <th>Device</th>
            <th>Valid</th>
            <th>Last Login</th>
            <th>Last IP</th>
            <th>Date Added</th>
            <th>Delete</th>
        </tr>';

	$pid = $_GET['page-id'];
	
	if(empty($pid)) { $pid = 1; }
	
	$lastPage = ceil($client->getTotalRecordCount()/$websiteProperty->getProperty('max_client_entries_per_page'));
	
	if($pid < 1) { 
		$pid = 1;
	}
	elseif($pid > $lastPage) {
		$pid = $lastPage;
	}

	if($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_POST['search'])) {
		$dataArr = $client->getClientsLike($_POST['search']);
	}
	else {
		$dataArr = $client->getClients($websiteProperty->getProperty('max_client_entries_per_page'),
								   ($pid - 1) * $websiteProperty->getProperty('max_client_entries_per_page'));
	}	   
		
	
	if($dataArr !== false) {
		foreach($dataArr as $data) {
			echo '<tr>';
			echo '<td>' . $data['id'] . '</td>';
			echo '<td>';
			if($data['fname'] == "NA") {
				echo '<p class="notification red minimal">n/a</p>';
			}
			else {
				echo $data['fname'];
			}
			echo '</td>';
			echo '<td>';
			if($data['nickname'] == "NA") {
				echo '<p class="notification red minimal">n/a</p>';
			}
			else {
				echo $data['nickname'];
			}
			echo '</td>';
                        echo '<td>' . $data['device_type'] . '</td>';
			echo '<td width="50px">';
			echo '<p class="notification ';
			if($data['valid'] == 'True') {
				echo 'green';
			}
			else {
				echo 'red';
			}
			echo ' minimal">' . $data['valid'];
			echo '</td>';
			echo '<td width="150px">' . $data['last_login'] . '</td>';
			echo '<td width="150px">' . $data['last_ip'] . '</td>';
			echo '<td width="150px">' . $data['date_added'] . '</td>';
									echo '<td style="text-align: center;" width="50px"><a class="image" href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;delete=' . $data['id'] . '">';
									echo '<img src="design/img/delete.png" alt="delete" /></a></td>';
			echo '</tr>';
		}
	echo '
	<tr><td colspan="9"><form class="styled" action="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '" method="post"><label style="width: 180px;">First name or nickname</label>
   				<input type="text" name="search" style="width: 200px;" />
                <input type="submit" value="Search" style="margin-left: 25px;"/></form></td></tr>
	</table>';
	
	echo "<div id='pagination'>";
		
		if ($pid > 1) 

 {

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=1'>&lt;&lt;</a> ";

 echo " ";

 $previous = $pid-1;

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$previous'>&lt;</a> ";

 } 


			if(($pid - 3) > 0) {
				$i = $pid - 3;
			}
			else {
				$i = 1;
			}
			if(($pid + 3) <= $lastPage) {
				$mPage = $pid + 3;
			}
			else {
				$mPage = $lastPage;
			}
			while($i <= $mPage) {
				echo '<a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;page-id=' . $i . '"';
				if($pid == $i) {
					echo ' class="current"';
				}
				echo '>' . $i . '</a>';
				$i++;
			}


 //This does the same as above, only checking if we are on the last page, and then generating the Next and Last links

 if ($pid < $lastPage) 

 {

 $next = $pid+1;

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$next'>&gt;</a> ";

 echo " ";

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$lastPage'>&gt;&gt;</a>";

 } 
   echo ' </div>';
					
				}
				else {
					echo '<p class="notification red">There were no clients found.</p>';
				}
   
					}
					 echo '</div></div>';
				break;
				
				case "manage-faq":
				
						$faq = new Faq();
					echo '
					<div style="overflow: auto;" id="admin">
                    <div style="float: left; width: 160px;">';
                    echo '<ul id="subMenu">';
                    	echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=add-faq"';
						if($_GET['do'] == 'add-faq' || empty($_GET['do'])) {
							echo ' class="current"';
						}
						echo '>Add FAQ</a></li>';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=edit-faq"';
						if($_GET['do'] == 'edit-faq') {
							echo ' class="current"';
						}
						echo '>Edit FAQ</a></li>';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=delete-faq"';
						if($_GET['do'] == 'delete-faq') {
							echo ' class="current"';
						}
						echo '>Delete FAQ</a></li>';
                   	echo '</ul>';
                    echo '</div>
                    <div style="float: right; width: 750px;">';
					
					
					if($_GET['do'] == 'add-faq' || empty($_GET['do'])) {
					?>
                    <p>You can add a frequently asked question here.</p>
                    <?php
						if($_SERVER['REQUEST_METHOD'] == "POST") {
							if(!empty($_POST['faq_question']) && !empty($_POST['faq_answer'])) {
								if($faq->addFAQ($_POST['faq_question'], $_POST['faq_answer'])) {
									echo '<p class="notification green">You have successfully added a new frequently asked question.</p>';
									$log->addLog($_SESSION['loggedIn']['id'], "Added a new frequently asked question.");
									$hideform = true;
								}
								else {
									echo '<p class="notification red">Something went wrong while adding the new FAQ. Please try again.</p>';
								}
							}
							else {
								echo '<p class="notification red">Please fill in all the fields.</p>';
							}
						}
						if($hideform !== true) {
					?>
                    <form action="" method="post" class="styled">
                        <label style="width: 100px;">Question</label>
                    
                        <input type="text" name="faq_question" value="" />
                        <br />
                        
                        <label style="width: 100px;">Answer</label>
                        <textarea cols="" rows="" name="faq_answer" style="height: 100px;"></textarea> <span class="note">(Can contain HTML)</span>
                        <br />
                        
                        <input style="margin-left: 115px;" type="submit" value="Add FAQ" />
                        	
                    </form>
                    <?php
						}
					}
					elseif($_GET['do'] == 'edit-faq') {
						?>
                        <p>Here you can edit existing frequently asked questions.</p>
                        <?php
							if($_SERVER['REQUEST_METHOD'] == "POST") {
								$fails = 0;
								$i = 0;
								while(count($_POST['faq_id']) > $i) {
									//echo "\n ID: " . $_POST['announcement_id'][$i] . "\nTYPE: " .$_POST['announcement_type'][$i] . "\n TEXT: " . $_POST['announcement_text'][$i++] . "\n";
									
									if(!$faq->updateFAQ($_POST['faq_id'][$i], $_POST['faq_question'][$i], $_POST['faq_answer'][$i++])) {
										$fails++;
									}
									
								}
								if($fails == 0) {
									echo '<p class="notification green">You have successfully updated the existing frequently asked questions.</p>';
									$log->addLog($_SESSION['loggedIn']['id'], "Updated frequently asked questions.");
									$hideForm = true;
								}
								else {
									echo '<p class="notification red">Something went wrong while updating the frequently asked questions.</p>';
								}
							}
							if($hideForm !== true) {
						?>
                        <form action="" method="post" class="styled">
                        <table width="100%">
                        	<tr>
                            	<th width="20px">#</th>
                                <th width="160px">Question</th>
                                <th>Answer</th>
                                <th width="150px">Time added</th>
                           	</tr>
                        <?php
						$dataArr = $faq->getFAQ(true);
						if($dataArr == false) {
							echo '<tr><td colspan="4"><p class="notification red minimal">There are no existing frequently asked questions that can be edited.</p>';
						}
						else {
							$i = 0;
							foreach($dataArr as $data) {
								echo '<tr>';
								echo '<td>' . ++$i . '<input type="hidden" name="faq_id[]" value="' . $data['id'] . '" /></td>';
								echo '<td><input style="width: 150px;" type="text" name="faq_question[]" value="' . $data['question'] . '" /></td>';
								echo '<td><input style="width: 320px;" type="text" name="faq_answer[]" value="' . $data['answer'] . '" /></td>';
								echo '<td>' . $data['date_added'] . '</td>';
								echo '</tr>';
							}
						}
						if($dataArr !== false) {
						?>
                        <tr><td colspan="4"><input style="margin-left: 5px;" type="submit" value="Save FAQ" /></td></tr>
                        <?php } ?>
                        </table>
                        </form>
                        <?php
							}
						
					}
					elseif($_GET['do'] == 'delete-faq') {
						echo '<p>You can delete existing frequently asked questions here.</p>';
						if(!empty($_GET['delete'])) {
							if(is_numeric($_GET['delete'])) {
								if($faq->deleteFAQ($_GET['delete'])) {
									echo '<p class="notification green">You have successfully deleted a frequently asked question.</p>';
									$log->addLog($_SESSION['loggedIn']['id'], "Deleted a frequently asked question.");
								}
							}
						}
						?>
                        
                        <table width="100%">
                        	<tr>
                            	<th width="20px">#</th>
                                <th width="150px">Question</th>
                                <th>Answer</th>
                                <th width="150px">Time added</th>
                                <th width="50px">Delete</th>
                           	</tr>
                        <?php
						$dataArr = $faq->getFAQ(true);
						if($dataArr == false) {
							echo '<tr><td colspan="5"><p class="notification red minimal">There are no existing frequently asked questions that can be deleted.</p>';
						}
						else {
							$i = 0;
							foreach($dataArr as $data) {
								echo '<tr>';
								echo '<td>' . ++$i . '<input type="hidden" name="faq_id[]" value="' . $data['id'] . '" /></td>';
								echo '<td>' . shortString($data['question'], 19) . '</td>';
								echo '<td>' . shortString($data['answer'], 41) . '</td>';
								echo '<td>' . $data['date_added'] . '</td>';
								echo '<td style="text-align: center;"><a class="image" href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;delete=' . $data['id'] . '">';
								echo '<img src="design/img/delete.png" alt="delete" /></a></td>';
								echo '</tr>';
							}
						}
						?>
                        </table>
                        <?php
					}
						echo '</div>';
						echo '</div>';
				break;
				
				case "manage-admins":
					echo '
					<div style="overflow: auto;" id="admin">
                    <div style="float: left; width: 160px;">';
                    echo '<ul id="subMenu">';
                    	echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=add-admin"';
						if($_GET['do'] == 'add-admin' || empty($_GET['do'])) {
							echo ' class="current"';
						}
						echo '>Add admin</a></li>';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=edit-admin"';
						if($_GET['do'] == 'edit-admin') {
							echo ' class="current"';
						}
						echo '>Edit admin</a></li>';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=delete-admin"';
						if($_GET['do'] == 'delete-admin') {
							echo ' class="current"';
						}
						echo '>Delete admin</a></li>';
                   	echo '</ul>';
                    echo '</div>
                    <div style="float: right; width: 750px;">';
					
					
					if($_GET['do'] == 'add-admin' || empty($_GET['do'])) {
					?>
                    <p>You can add an admin user here.</p>
                    <?php
						if($_SERVER['REQUEST_METHOD'] == "POST") {
							if(empty($_POST['admin_username']) || empty($_POST['admin_email']) ||  empty($_POST['admin_email_ver']) || empty($_POST['admin_pw']) || empty($_POST['admin_pw_ver'])) {
								echo '<p class="notification red">Please fill in all the fields.</p>';
							}
							else {
								if(strlen($_POST['admin_username']) > 32) {
									echo '<p class="notification red">The admin username cannot contain more than 32 characters!</p>';
								}
								elseif($_POST['admin_email'] !== $_POST['admin_email_ver']) {
									echo '<p class="notification red">The email and email verification don\'t match.</p>';
								}
								elseif($_POST['admin_pw'] !== $_POST['admin_pw_ver']) {
									echo '<p class="notification red">The password and password verification don\'t match.</p>';
								}
								elseif(@!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST['admin_email'])) {
									echo '<p class="notification red">Please use a valid email.</p>';
								}
								elseif($admin->checkValueExists("username", $_POST['admin_username'])) {
									echo '<p class="notification red">This username is already taken!</p>';
									
								}
								elseif($admin->checkValueExists("email", $_POST['admin_email'])) {
									echo '<p class="notification red">This email is already taken!</p>';
									
								}
								else {
									if($admin->addAdmin($_POST['admin_username'], $_POST['admin_email'], $_POST['admin_pw'])) {
										echo '<p class="notification green">You have successfully added a new admin.</p>';
										$log->addLog($_SESSION['loggedIn']['id'], "Added a new admin, " . $_POST['username'] . ".");
										$hideform = true;
									}
									else {
										echo '<p class="notification red">Something went wrong while adding the new admin. Please try again.</p>';
									}
								}
							}
						}
						if($hideform !== true) {
					?>
                    <form action="" method="post" class="styled">
                        <label style="width: 150px;">Username</label>
                    
                        <input type="text" name="admin_username" />
                        <br />
                        <label style="width: 150px;">Email</label>
                    
                        <input type="text" name="admin_email" />
                        <br />
                        <label style="width: 150px;">Email Verification</label>
                    
                        <input type="text" name="admin_email_ver" />
                        <br />
                        <label style="width: 150px;">Password</label>
                    
                        <input type="password" name="admin_pw" />
                        <br />
                        <label style="width: 150px;">Password Verification</label>
                    
                        <input type="password" name="admin_pw_ver" />
                        <br />
                        
                        <input style="margin-left: 165px;" type="submit" value="Add Admin" />
                    </form>
                    <?php
						}
					}
					elseif($_GET['do'] == 'edit-admin') {
						?>
                        <p>Here you can edit existing admins.</p>
                        <?php
						if(!empty($_GET['edit']) && is_numeric($_GET['edit'])) {
							if($admin->checkValueExists("id", $_GET['edit'])) {
								if($_SERVER['REQUEST_METHOD'] == "POST") {
									if(empty($_POST['admin_username']) || empty($_POST['admin_email'])) {
										echo '<p class="notification red">The username and email field can not be blank.</p>';
									}
									else {
										if(strlen($_POST['admin_username']) > 32) {
											echo '<p class="notification red">The admin username cannot contain more than 32 characters!</p>';
										}
										elseif(@!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST['admin_email'])) {
											echo '<p class="notification red">Please use a valid email.</p>';
										}
										else {
											if($admin->getFieldDataByID($_GET['edit'], "username") !== $_POST['admin_username']) {
												if($admin->checkValueExists("username", $_POST['admin_username'])) {
													echo '<p class="notification red">This username is already taken!</p>';
													$stop = true;
												}
											}
											
											if($admin->getFieldDataByID($_GET['edit'], "email") !== $_POST['admin_email']) {
												if($admin->checkValueExists("email", $_POST['admin_email'])) {
													echo '<p class="notification red">This email is already taken!</p>';
													$stop = true;
												}
											}
											
											if($stop !== true) {
												
												if(!empty($_POST['admin_pw']) && !empty($_POST['admin_pw_ver'])) {
													if($_POST['admin_pw'] !== $_POST['admin_pw_ver']) {
														echo '<p class="notification red">The password and password verification don\'t match.</p>';
														$fail = true;
													}
													else {
														$updatePass = $admin->updateFieldByID($_GET['edit'], "password", md5($_POST['admin_pw']));
													}
												}
													
															if(!empty($updatePass)) {
																if($fail !== true) {
																	if($updatePass) {
																		$updateGen = true;
																	}
																	else {
																		echo '<p class="notification red">Something went wrong while updating the admin user. Please try again.</p>';
																	}
																}
															}
															else {
																$updateGen = true;
															}
															
															if($updateGen) {
																$updateGeneral = $admin->updateFieldByID($_GET['edit'], "username", $_POST['admin_username']) && $admin->updateFieldByID($_GET['edit'], "email", $_POST['admin_email']);
																if($updateGeneral) {
																	echo '<p class="notification green">You have successfully updated the admin user.</p>';
																	$log->addLog($_SESSION['loggedIn']['id'], "Updated an admin user.");
																	$hideform = true;
																}
															}
															
											}
											
										}
									}
								}
								
								if($hideform !== true) {
							?>
							<form action="" method="post" class="styled">
								<label style="width: 150px;">Username</label>
							
								<input type="text" name="admin_username" value="<?php echo $admin->getFieldDataByID($_GET['edit'], "username"); ?>" />
								<br />
								<label style="width: 150px;">Email</label>
							
								<input type="text" name="admin_email" value="<?php echo $admin->getFieldDataByID($_GET['edit'], "email"); ?>" />
								<br />
                                
								<label style="width: 150px;">Password</label>
							
								<input type="password" name="admin_pw" />
								<br />
								<label style="width: 150px;">Password Verification</label>
							
								<input type="password" name="admin_pw_ver" />
								<br />
								
								<input style="margin-left: 165px;" type="submit" value="Edit Admin" />
							</form>
                            <p class="notification yellow" style="margin-top: 25px;">If you don't want to change the password leave the password fields blank.</p>
							<?php
								}
							}
							else {
								echo '<p class="notification red">There was no such user found with this ID.</p>';
							}
						}
						else {
						?>
                        <table width="100%">
                        	<tr>
                            	<th width="20px">#</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th width="140px">Last login</th>
                                <th width="140px">Time added</th>
                                <th width="30px">Edit</th>
                           	</tr>
                        <?php
						$dataArr = $admin->getAdmins();
						if($dataArr == false) {
							echo '<tr><td colspan="4"><p class="notification red minimal">There are no existing admins.</p>';
						}
						else {
							$i = 0;
							foreach($dataArr as $data) {
								echo '<tr>';
								echo '<td>' . ++$i . '<input type="hidden" name="faq_id[]" value="' . $data['id'] . '" /></td>';
								echo '<td>' . $data['username'] . '</td>';
								echo '<td>' . $data['email'] . '</td>';
								echo '<td>' . $data['last_login'] . '</td>';
								echo '<td>' . $data['date_created'] . '</td>';
								echo '<td style="text-align: center;"><a class="image" href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;edit=' . $data['id'] . '">';
								echo '<img src="design/img/edit.png" alt="edit" /></a></td>';
								echo '</tr>';
							}
						echo '</table>';
							}
						}	
						
					}
					elseif($_GET['do'] == 'delete-admin') {
						
						?>
                        <p>Here you can edit existing admins.</p>
                        <?php
						if(!empty($_GET['delete']) && is_numeric($_GET['delete'])) {
							if($admin->checkValueExists("id", $_GET['delete'])) {
								if($admin->deleteAdmin($_GET['delete'])) {
									echo '<p class="notification green">You have successfully deleted an admin user.</p>';
									$log->addLog($_SESSION['loggedIn']['id'], "Deleted an admin.");
								}
								
							}
							else {
								echo '<p class="notification red">There was no such user found with this ID.</p>';
							}
						}
						else {
						?>
                        <table width="100%">
                        	<tr>
                            	<th width="20px">#</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th width="140px">Last login</th>
                                <th width="140px">Time added</th>
                                <th width="50px">Delete</th>
                           	</tr>
                        <?php
						$dataArr = $admin->getAdmins();
						if($dataArr == false) {
							echo '<tr><td colspan="4"><p class="notification red minimal">There are no existing admins.</p>';
						}
						else {
							$i = 0;
							foreach($dataArr as $data) {
								echo '<tr>';
								echo '<td>' . ++$i . '<input type="hidden" name="faq_id[]" value="' . $data['id'] . '" /></td>';
								echo '<td>' . $data['username'] . '</td>';
								echo '<td>' . $data['email'] . '</td>';
								echo '<td>' . $data['last_login'] . '</td>';
								echo '<td>' . $data['date_created'] . '</td>';
								echo '<td style="text-align: center;"><a class="image" href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;delete=' . $data['id'] . '">';
								echo '<img src="design/img/delete.png" alt="delete" /></a></td>';
								echo '</tr>';
							}
						echo '</table>';
							}
						}
					}
						echo '</div>';
						echo '</div>';
				break;
				
				// case "statistics-and-info":
				// 	echo '<p class="notification red">This page is not completed yet and still under construction. It will be updated regularly.</p>';
				// 	$statistics = new Statistics();
				// 	$websiteProperty = new WebsiteProperty();
					
				// 	echo '<div style="overflow: hidden;">';
				// 	echo '<div style="width: 66%; float: left; overflow: hidden;">';
				// 	echo '<div style="width: 49%; float: left;">';
				// 	echo '<h2>Status Servers</h2>';
				// 	echo '
				// 	<table width="100%">
				// 		<tr>
				// 			<th>Status DNS server</th>
				// 			<td>';
				// 			if($statistics->checkServer($websiteProperty->getProperty("dns_host_and_port"))) {
				// 				echo '<p class="notification green minimal">ON</p>';
				// 			}
				// 			else {
				// 				echo '<p class="notification red minimal">OFF</p>';
				// 			}
				// 			echo '</td>
				// 		</tr>
				// 		<tr>
				// 			<th>Status MySQL Server</th>
				// 			<td>';
				// 			if($statistics->checkServer($websiteProperty->getProperty("mysql_host_and_port"))) {
				// 				echo '<p class="notification green minimal">ON</p>';
				// 			}
				// 			else {
				// 				echo '<p class="notification red minimal">OFF</p>';
				// 			}
				// 			echo '</td>
				// 		</tr>
				// 		<tr>
				// 			<th>Status Web Server</th>
				// 			<td>';
				// 			if($statistics->checkServer($websiteProperty->getProperty("web_host_and_port"))) {
				// 				echo '<p class="notification green minimal">ON</p>';
				// 			}
				// 			else {
				// 				echo '<p class="notification red minimal">OFF</p>';
				// 			}
				// 			echo '</td>
				// 		</tr>
				// 		<tr>
				// 			<th>Status Proxy Server</th>	
				// 			<td>';
				// 			if($statistics->checkServer($websiteProperty->getProperty("proxy_host_and_port"))) {
				// 				echo '<p class="notification green minimal">ON</p>';
				// 			}
				// 			else {
				// 				echo '<p class="notification red minimal">OFF</p>';
				// 			}
				// 			echo '</td>
				// 		</tr>
				// 	</table>';
				// 	echo '
				// 	<h2>Proxy Stats</h2>
				// 	<table width="100%">
				// 		<tr>
				// 			<th>Server uptime</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>Active connections</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>Time until dropdown</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>Time until happy hour</th>
				// 			<td></td>
				// 		</tr>
				// 	</table>
				// 	<br />';

				// 	echo '<h2>Guestbook Stats</h2>';
				// 	echo '
				// 	<table width="100%">	
				// 		<tr>
				// 			<th>Total guestbook entries</th>
				// 			<td>' . $statistics->getTableRecordCount("guestbook") . '</td>
				// 		</tr>
				// 		<tr>
				// 			<th>Total words written in guestbook</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>Average words per entry</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>Last new guestbook entry</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>New guestbook entries today</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>New guestbook entries yesterday</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>New guestbook entries last week</th>
				// 			<td></td>
				// 		</tr>
				// 	</table>';

				// 	echo '</div>';
				// 	echo '<div style="width: 49%; float: right;">';

				// 	echo '<h2>Key Stats</h2>';
				// 	echo '<table width="100%">
				// 			<tr>
				// 				<th>Total keys served</th>
				// 				<td>' . $statistics->getTableRecordCount("keys") . '</td>
				// 			</tr>
				// 			<tr>
				// 				<th>Active keys</th>
				// 				<td></td>
				// 			</tr>
				// 			<tr>
				// 				<th>Overloaded keys</th>
				// 				<td></td>
				// 			</tr>
				// 			<tr>
				// 				<th>Expired keys</th>
				// 				<td></td>
				// 			</tr>
				// 			<tr>
				// 				<th>New keys today</th>
				// 				<td></td>
				// 			</tr>
				// 			<tr>
				// 				<th>New keys yesterday</th>
				// 				<td></td>
				// 			</tr>
				// 			<tr>
				// 				<th>New keys last week</th>
				// 				<td></td>
				// 			</tr>
				// 		</table>';

				// 	echo '
				// <h2>Visits &amp; Pagehits</h2>
				// <table width="100%">
				// 	<tr>
				// 		<th>Total pagehits</th>
				// 		<td>' . $statistics->getTableRecordCount("ip_logs") . '</td>
				// 	</tr>
				// 	<tr>
				// 		<th>Total pagehits today</th>
				// 		<td></td>
				// 	</tr>
				// 	<tr>
				// 		<th>Total pagehits yesterday</th>
				// 		<td></td>
				// 	</tr>
				// 	<tr>
				// 		<th>Total pagehits last week</th>
				// 		<td></td>
				// 	</tr>
				// 	<tr>
				// 		<th>Average pagehits</th>
				// 		<td></td>
				// 	</tr>
				// 	<tr>
				// 		<th>Average pagehits / minute today</th>
				// 		<td></td>
				// 	</tr>
				// 	<tr>
				// 		<th>Average pagehits / minute yesterday</th>
				// 		<td></td>
				// 	</tr>
				// 	<tr>
				// 		<th>Average pagehits / last week</th>
				// 		<td></td>
				// 	</tr>
				// 	<tr>
				// 		<th>Total unique visits</th>
				// 		<td></td>
				// 	</tr>
				// 	<tr>
				// 		<th>Total unique visits today</th>
				// 		<td></td>
				// 	</tr>
				// 	<tr>
				// 		<th>Total unique visits last week</th>
				// 		<td></td>
				// 	</tr>
				// </table>';

				// 	echo '</div>';
				// 	echo '</div>';

				// 	echo '<div style="width: 33%; float: right;">';
				// 	echo '<h2>Client Stats</h2>';
				// 	echo '
				// 	<table width="100%">	
				// 		<tr>
				// 			<th>Total Clients</th>
				// 			<td>' . $statistics->getTableRecordCount("clients") . '</td>
				// 		</tr>
				// 		<tr>
				// 			<th>New clients today</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>New clients yesterday</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>New clients last week</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>Last new client</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>Valid clients</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>Invalid clients</th>
				// 			<td></td>
				// 		</tr>
				// 	</table><br />';
				// 	echo '<h2>Admin Stats</h2>';
				// 	echo '
				// 	<table width="100%">	
				// 		<tr>
				// 			<th>Total Admins</th>
				// 			<td>' . $statistics->getTableRecordCount("admin_users") . '</td>
				// 		</tr>
				// 		<tr>
				// 			<th>Total Admin Logs</th>
				// 			<td>' . $statistics->getTableRecordCount("admin_logs") . '</td>
				// 		</tr>
				// 	</table><br />';
				// 	echo '<h2>Forum Stats</h2>';
				// 	echo '
				// 	<table width="100%">	
				// 		<tr>
				// 			<th>Total forum users</th>
				// 			<td>' . $statistics->getTableRecordCount("minibb_users") . '</td>
				// 		</tr>
				// 		<tr>
				// 			<th>Total forum posts</th>
				// 			<td>' . $statistics->getTableRecordCount("minibb_posts") . '</td>
				// 		</tr>
				// 		<tr>
				// 			<th>Average posts per user</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>New posts today</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>New posts yesterday</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>New posts last week</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>New users today</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>New users yesterday</th>
				// 			<td></td>
				// 		</tr>
				// 		<tr>
				// 			<th>New users last week</th>
				// 			<td></td>
				// 		</tr>
				// 	</table>';
				// 	echo '</div>';
				// 	echo '</div>';

				// break;
				
				case "bans":
					echo '
					<div style="overflow: auto;" id="admin">
                    <div style="float: left; width: 160px;">';
                    echo '<ul id="subMenu">';
                    	echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=add-ban"';
						if($_GET['do'] == 'add-ban' || empty($_GET['do'])) {
							echo ' class="current"';
						}
						echo '>Add Ban</a></li>';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=edit-ban"';
						if($_GET['do'] == 'edit-ban') {
							echo ' class="current"';
						}
						echo '>Edit Ban</a></li>';
                        echo '<li><a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=delete-ban"';
						if($_GET['do'] == 'delete-ban') {
							echo ' class="current"';
						}
						echo '>Delete Ban</a></li>';
                   	echo '</ul>';
                    echo '</div>
                    <div style="float: right; width: 750px;">';
                    	$ban = new Ban();
                    if($_GET['do'] == 'add-ban' || empty($_GET['do'])) {
                    	echo '<p>On this page you can add someone to the banlist.</p>';


						if($_SERVER['REQUEST_METHOD'] == "POST") {
							if(!empty($_POST['ban_ip']) && !empty($_POST['ban_reason'])) {
								if($ban->addBan($_POST['ban_ip'], $_POST['ban_reason'])) {
									if(strlen($_POST['ban_ip']) > 15) {
										echo '<p class="notification red">The IP can not contain more than 15 characters (only IP4 support yet).</p>';
									}
									else {
										echo '<p class="notification green">You have successfully added a new ban.</p>';
										$log->addLog($_SESSION['loggedIn']['id'], "Added a new ban.");
										$hideform = true;
									}
								}
								else {
									echo '<p class="notification red">Something went wrong while adding the new ban. Please try again.</p>';
								}
							}
							else {
								echo '<p class="notification red">Please fill in all the fields.</p>';
							}
						}
						if($hideform !== true) {
					?>
                    <form action="" method="post" class="styled">
                        <label style="width: 100px;">IP</label>
                    
                        <input type="text" name="ban_ip" value="" />
                        <br />
                        
                        <label style="width: 100px;">Reason</label>
                        <input type="text" name="ban_reason" value="" />
                        <br />
                        
                        <input style="margin-left: 115px;" type="submit" value="Add Ban" />
                        	
                    </form>
                    <?php
						}

                    }
                    elseif($_GET['do'] == 'edit-ban') {
                    		echo '<p>On this page you can edit the current bans.</p>';
                    		
							if($_SERVER['REQUEST_METHOD'] == "POST") {
								$fails = 0;
								$i = 0;
								while(count($_POST['ban_id']) > $i) {
									//echo "\n ID: " . $_POST['announcement_id'][$i] . "\nTYPE: " .$_POST['announcement_type'][$i] . "\n TEXT: " . $_POST['announcement_text'][$i++] . "\n";
									
									if(!$ban->updateBan($_POST['ban_id'][$i], $_POST['ban_ip'][$i], $_POST['ban_reason'][$i++])) {
										$fails++;
									}
									
								}
								if($fails == 0) {
									echo '<p class="notification green">You have successfully updated the bans.</p>';
									$log->addLog($_SESSION['loggedIn']['id'], "Updated bans.");
									$hideForm = true;
								}
								else {
									echo '<p class="notification red">Something went wrong while updating the bans.</p>';
								}
							}
							if($hideForm !== true) {
						?>
                        <form action="" method="post" class="styled">
                        <table width="100%">
                        	<tr>
	                        	<th width="20px">#</th>
	                        	<th width="150px">IP</th>
	                            <th>Reason</th>
	                            <th width="150px">When</th>
                           	</tr>
                        <?php
						$dataArr = $ban->getBans();
						if($dataArr == false) {
							echo '<tr><td colspan="4"><p class="notification red minimal">There are no bans that can be edited.</p>';
						}
						else {
							$i = 0;
							foreach($dataArr as $data) {
								echo '<tr>';
								echo '<td>' . ++$i . '<input type="hidden" name="ban_id[]" value="' . $data['id'] . '" /></td>';
								echo '</select></td>';
								echo '<td><input style="width: 125px;" type="text" name="ban_ip[]" value="' . $data['ip'] . '" /></td>';
								echo '<td><input style="width: 340px;" type="text" name="ban_reason[]" value="' . $data['reason'] . '" /></td>';
								echo '<td>' . $data['dtime'] . '</td>';
								echo '</tr>';
							}
						}
						if($dataArr !== false) {
						?>
                        <tr><td colspan="4"><input style="margin-left: 5px;" type="submit" value="Update Bans" /></td></tr>
                        <?php } ?>
                        </table>
                        </form>
                        <?php
							}

                    }
                    elseif($_GET['do'] == 'delete-ban') {
                    	?>
                    <p>On this page you can edit bans.</p>
                    <?php

						if(!empty($_GET['delete']) && is_numeric($_GET['delete'])) {
							if($ban->checkValueExists("id", $_GET['delete'])) {
								if($ban->deleteBan($_GET['delete'])) {
									echo '<p class="notification green">You have successfully deleted a ban.</p>';
									$log->addLog($_SESSION['loggedIn']['id'], "Deleted a ban.");
								}
								
							}
							else {
								echo '<p class="notification red">There was no such ban record found with this ID.</p>';
							}
						}

                    ?>
                    <table width="100%">
                    	<tr>
                        	<th width="20px">#</th>
                        	<th width="150px">IP</th>
                            <th>Reason</th>
                            <th width="150px">When</th>
                            <th width="50px">Delete</th>
                       	</tr>
                    	<?php

						$dataArr = $ban->getBans();

						if($dataArr == false) {
							echo '<tr><td colspan="4"><p class="notification red minimal">There are no bans yet.</p>';
						}
						else {
							$i = 0;
							foreach($dataArr as $data) {
								echo '<tr>';
								echo '<td>' . ++$i . '</td>';
								echo '<td>' . $data['ip'] . '</td>';
								echo '<td>' . stripslashes($data['reason']) . '</td>';
								echo '<td>' . $data['dtime'] . '</td>';
								echo '<td style="text-align: center;"><a class="image" href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;delete=' . $data['id'] . '">';
								echo '<img src="design/img/delete.png" alt="delete" /></a></td>';
							}
						}
						
						
					echo '</table>';
                    	
                    }
                    echo '</div></div>';

				break;

				case "logs":
					$log = new Log();
					$websiteProperty = new WebsiteProperty();
					?>
                    <p>On this page you can see last performed actions by admins.</p>
                    <table width="100%">
                    	<tr>
                        	<th width="20px">#</th>
                        	<th width="150px">User</th>
                            <th>Action</th>
                            <th width="150px">When</th>
                       	</tr>
                        
                    <?php


	$pid = $_GET['page-id'];
	
	if(empty($pid)) { $pid = 1; }
	
	$lastPage = ceil($log->getTotalRecordCount()/$websiteProperty->getProperty('max_log_entries_per_page'));
	
	if($pid < 1) { 
		$pid = 1;
	}
	elseif($pid > $lastPage) {
		$pid = $lastPage;
	} 
		

					
						$dataArr = $log->getLogs($websiteProperty->getProperty('max_log_entries_per_page'),
								   ($pid - 1) * $websiteProperty->getProperty('max_log_entries_per_page'));

						if($dataArr == false) {
							echo '<tr><td colspan="4"><p class="notification red minimal">There are no logs yet.</p>';
						}
						else {
							$i = (($pid - 1) * $websiteProperty->getProperty('max_log_entries_per_page'));
							foreach($dataArr as $data) {
								echo '<tr>';
								echo '<td>' . ++$i . '</td>';
								echo '<td>' . $admin->getUsernameByID($data['user_id']) . '</td>';
								echo '<td>' . stripslashes($data['action']) . '</td>';
								echo '<td>' . $data['dtime'] . '</td>';
							}
						}
						
						
					echo '</table>';

	echo "<div id='pagination'>";
		
		if ($pid > 1) 

 {

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=1'>&lt;&lt;</a> ";

 echo " ";

 $previous = $pid-1;

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$previous'>&lt;</a> ";

 } 


			if(($pid - 3) > 0) {
				$i = $pid - 3;
			}
			else {
				$i = 1;
			}
			if(($pid + 3) <= $lastPage) {
				$mPage = $pid + 3;
			}
			else {
				$mPage = $lastPage;
			}
			while($i <= $mPage) {
				echo '<a href="?page=' . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . '&amp;page-id=' . $i . '"';
				if($pid == $i) {
					echo ' class="current"';
				}
				echo '>' . $i . '</a>';
				$i++;
			}


 //This does the same as above, only checking if we are on the last page, and then generating the Next and Last links

 if ($pid < $lastPage) 

 {

 $next = $pid+1;

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$next'>&gt;</a> ";

 echo " ";

 echo " <a href='?page=" . $_GET['page'] . '&amp;action=' . $_GET['action'] . '&amp;do=' . $_GET['do'] . "&amp;page-id=$lastPage'>&gt;&gt;</a>";

 } 
   echo ' </div>';
				break;
			}
		}
	}
	else {
		echo '<h1>Admin Panel</h1>';
		
		if($_SERVER['REQUEST_METHOD'] == "POST") {
			if(empty($_POST['username']) || empty($_POST['password'])) {
				echo '<p id="response" class="notification red">Please fill in all the fields.</p>';
			}
			else {
				if($admin->login($_POST['username'], $_POST['password'])) {
					echo '<p id="response" class="notification green">You have successfully logged in. Please wait until the page reloads.</p>';
					$loggedIn = true;
					$log->addLog($_SESSION['loggedIn']['id'], "Logged in.");
					redirect("", "3");
				}
				else {
					echo '<p id="response" class="notification red">The user / password combination was incorrect. Please try again.</p>';
				}
			}
		}
		if($loggedIn !== true) {
	?>
	<form class="styled" action="" method="post">
		<label>Username</label>
	
		<input type="text" name="username" value="" />
		<br />
		
		
		<label>Password</label>
		<input type="password" name="password" />
		<br />
		
		<input type="submit" value="Log me in!" />			
	</form>
    <p class="notification yellow">This is for administrators only, if you're not an administrator you have nothing to search here.</p>
<?php
		}
	}
?>