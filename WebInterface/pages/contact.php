<?php
	/******************************************************************
	 * Project: The Three Little Pigs - Siri Proxy | Web Interface
	 * Project start date: 21-01-2012
	 * Author: Wouter De Schuyter
	 * Website: www.wouterds.be
	 * E: info[@]wouterds[.]be
	 * T: www.twitter.com/wouterds
	 *
	 * File: contact.php
     * Last update: 22-02-2012
	******************************************************************/

	echo '<h1>Contact</h1>';
	
			$websiteProperty = new WebsiteProperty();
				if($_SERVER['REQUEST_METHOD'] == "POST") {
					if(empty($_POST['firstName']) || empty($_POST['lastName']) || empty($_POST['email']) || empty($_POST['subject']) || empty($_POST['message']) || empty($_POST['captcha'])) {
						echo '<p id="response" class="notification red">All fields except website are required to be filled in. Please correct the errors and try again.</p>';
					}
					else {
						if(@eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST['email'])) {
							if($_POST['captcha'] !== $_SESSION['captcha']) {
								echo '<p id="response" class="notification red">The captcha code is not correct. Please correct the errors and try again.</p>';
							}
							else {
								
								$headers  = "From: The Three Little Pigs <no-reply@paradox-productions.net>\r\n";
								$headers .= "MIME-Version: 1.0\r\n";
								$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
								$headers .= "Reply-To: " .  $_POST['firstName'] . " " . $_POST['lastName'] . " <" . $_POST['email'] . ">\r\n";
								$to = $websiteProperty->getProperty('contact_name') . " <" . $websiteProperty->getProperty('contact_email') . ">\r\n";
								$subject = "[The Three Little Pigs :: Contact] " . $_POST['subject'];
							
								$message = '
							<html>
							  <head>
							  <style type="text/css">
							  * { margin: 0; padding: 0; }
							  body {
								  font-family: Tahoma, Verdana, Arial, sans-serif;
								  font-size: 14px;
								  line-height: 24px;
								  color: #333;
								  background: #FFF;
							  }
							  h1 {
								  margin: 5px 0 25px 0;
							  }
							  p {
								  margin: 10px 0;
							  }
							  #top {
								  height: 175px;
								  background: #EEE url("http://paradox-productions.net/img/topMailLogo.png") center left no-repeat;
								  border-bottom: 10px solid #DDD;
							  }
							  #middle {
								  padding: 25px 15px;
								  min-height: 300px;
							  }
							  #bottom {
								  background: #EEE;
								  boder-top: 1px solid #DDD;
								  color: #555;
								  padding: 15px 5px;
								  border-top: 10px solid #DDD;
							  }
							  </style>
							  </head>
							  <body>
							  <div id="top"></div>
							  <div id="middle">
							  <h1>The Three Little Pigs :: Contact</h1>
							  <p><b>Name:</b> ' . $_POST['firstName'] . " " . $_POST['lastName'] . '<br />
							  	 <b>Email:</b> ' . $_POST['email'] . '<br />
								 <b>Subject:</b> ' . $_POST['subject'] . '<br />
								 <b>Website:</b> ' . $_POST['website'] . '<br />
								 <b>IP:</b> ' . $_SERVER['REMOTE_ADDR'] . '<br />
								 <b>Host:</b> ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . '<br /><br />
								 <b>Message:</b><p>
								 <p>' . nl2br($_POST['message']) . '</p>
							   </div>
							   <div id="bottom">Email sent on ' . date("l") . " the " . date("jS") . " around " . date("h:s a") . '. <br />By <a href="http://twitter.com/wouterds">@WouterDS</a></div>
							  </body>
							</html>';
							
								if(mail($to, $subject, $message, $headers)) {
									echo '<p id="response" class="notification green">Your message has been sent successfully. If it is a serious message, it will be answered within the 48 hours.</p>';
									$success = true;
								}
								else {
									echo '<p id="response" class="notification red">Something went wrong while sending the email to the administrator, try contacting <a href="http://twitter.com/wouterds">@WouterDS</a> on Twitter.</p>';
								}
							}
						}
						else {
							echo '<p id="response" class="notification red">Not a valid email filled in. Please correct the errors and try again.</p>';
						}
					}
				}
				if($success !== true) {
			?>

   			<form class="styled" action="#response" method="post">
   				<label>First name</label>
   				<input type="text" name="firstName" value="<?php echo $_POST['firstName']; ?>" />
   				<br />
   				
   				
   				<label>Last name</label>
   				<input type="text" name="lastName" value="<?php echo $_POST['lastName']; ?>" />
   				<br />
   				
   				<label>Email</label>
   				<input type="text" name="email" value="<?php echo $_POST['email']; ?>" />
   				<br />
   				
   				<label>Website</label>
   				<input type="text" name="website" value="<?php echo $_POST['website']; ?>" />
   				<br />
   				
   				<label>Subject</label>
   				<input type="text" name="subject" value="<?php echo $_POST['subject']; ?>" />
   				<br />
   				
   				<label>Message</label>
   				<textarea name="message" cols="" rows=""><?php echo $_POST['message']; ?></textarea>
   				<br />
   				
   				<label><img src="inc/captcha.inc.php" alt="Captcha" style="padding-top: 2px;" /></label>
   				<input type="text" name="captcha" style="width: 75px;" />
   				<br />
   				
   				<input type="submit" value="Send form" />			
   			</form>
            <?php } ?>