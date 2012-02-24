<h1>Guestbook</h1>

<?php


$websiteProperty = new WebsiteProperty();
#####################################
## © 2008 Wouter De Schuyter
## <info@paradox-productions.net>
## Guestbook V1.0
#####################################

// SET VARIABLES
//////////////////

$minName = 2; // minimum lenght name
$maxName = 32; // maximum lenght name
$minEmail = 8; // minimum lenght email
$maxEmail = 256; // maximum lenght email
$minMessage = 8; // minimum lenght message
$maxMessage = 2560; // maximum lenght message

///////////////////

$act = $_GET['action'];

// UBB CODE
function ubb($string) {
    $array1 = array(
                    '[b]',
                    '[/b]',
                    '[u]',
                    '[/u]',
                    '[center]',
                    '[/center]',
                    '[i]',
                    '[/i]'
                    );
    $array2 = array(
                    '<b>',
                    '</b>',
                    '<u>',
                    '</u>',
                    '<center>',
                    '</center>',
                    '<i>',
                    '</i>'
                    );
    $output = str_replace($array1, $array2, $string);
    return $output;
}

// VALID
function valid($string) {
    $array1 = array(
                    '<br>',
                    '<noscript>'
                    );
    $array2 = array(
                    '<br />',
                    '*noscript*'
                    );
    $output = str_replace($array1, $array2, $string);
    return $output;
}

// WHEN ACTION IS "Add Comment"
if($act == "addComment") {
    
    echo "<h2>Add Comment | <a href='?page=guestbook'>View comments</a></h2>\n";
    
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        $name = addslashes(ucfirst(trim($_POST['name']))); // NAME
        $email = addslashes($_POST['email']); // EMAIL
        $showEmail = $_POST['showEmail']; // SHOW/HIDE EMAIL
        $emoticons = $_POST['emoticons']; // ENABLE/DISABLE EMOTICONS
        $message = addslashes(ucfirst(trim($_POST['message']))); // MESSAGE
        $captcha = $_POST['captcha']; // CAPTCHA
        $captchaVer = $_SESSION['captcha']; // CAPTCHA CHECK
        $time = date("Y/m/d H:i:s"); // TIME
        $ip = $_SERVER['REMOTE_ADDR']; // IP
        $regexp    = "/^[a-z0-9_]+([_\\.-][a-z0-9_]+)*@([a-z0-9_]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i"; // EMAIL CHECK
        
        // GENERAL FIELD CHECK
        if(strlen($name) < 1 or strlen($email) < 1 or strlen($message) < 1 or strlen($captcha) < 1) {
            echo "<p class='notification red'>Make sure all the required fields are correctly filled in!</p>\n";
            $generalError = true;
        }
        elseif($generalError !== true) {
            // CHECK NAME LENGHT
            if(strlen($name) < $minName) {
                echo "<p class='notification red'>Your name must contain at least " . $minName . " characters! Please enter a longer name.</p>\n";
                $lenghtError = true;
            }
            elseif(strlen($name) > $maxName) {
                echo "<p class='notification red'>Your name can not contain more than " . $maxName . " characters! Please enter a shorter name.</p>\n";
                $lenghtError = true;
            }
            
            // CHECK EMAIL LENGHT
            if(strlen($email) < $minEmail) {
                echo "<p class='notification red'>Your email must contain at least " . $minEmail . " characters! Please enter a longer email.</p>\n";
                $lenghtError = true;
            }
            elseif(strlen($email) > $maxEmail) {
                echo "<p class='notification red'>Your email can not contain more than " . $maxEmail . " characters! Please enter a shorter email.</p>\n";
                $lenghtError = true;
            }
            
            // CHECK MESSAGE LENGHT
            if(strlen($message) < $minMessage) {
                echo "<p class='notification red'>Your message must contain at least " . $minMessage . " characters! Please enter a longer message.</p>\n";
                $lenghtError = true;
            }
            elseif(strlen($message) > $maxMessage) {
                echo "<p>Your message can not contain more than " . $maxMessage . " characters! Please enter a shorter message.</p>\n";
                $lenghtError = true;
            }
            
            // CHECK CAPTCHA LENGHT
            if(strlen($captcha) !== 4) {
                echo "<p class='notification red' class='notification red'>Your captcha verefication can not contain more or less than 4 characters!</p>\n";
                $lenghtError = true;
            }
            
            if($lenghtError !== true) {
                
                // VALID EMAIL ?
                if(!preg_match($regexp, $email)) {
                    echo "<p class='notification red'>Your email is not valid! Please try again.</p>\n";
                    $error = true;
                }
                
                // CAPTCHA CORRECT?
                if($captcha !== $captchaVer) {
                    echo "<p class='notification red'>Your captcha verefication code was inccorect! Please try again.</p>\n";
                    $error = true;
                }
                
                if($error !== true) {
                    $insertQuery = "INSERT INTO `guestbook` (`name`, `email`, `showEmail`, `enableEmoticons`, `message`, `time`, `ip`) VALUES ('" . $name . "', '" . $email . "', '" . $showEmail . "', '0', '" . $message . "', '" . $time . "', '" . $ip . "')";
                    $insert = mysql_query($insertQuery);
                    if($insert) {
                        echo "<p class='notification green'>Your message was successfully stored into the guestbook database!<br /><a href=\"?page=guestbook&amp;action=viewComments\">Click Here</a> to view the guestbook entries.</p>\n";
                        $success = true;
                    }
                    else {
                        echo "<p class='notification red'>Error<br />" . mysql_error() . "</p>\n";
                    }
                }
            }
        }
    }
    if($success !== true) {
?>
                <form class="styled" action="?page=<?php echo $_GET['page']; ?>&amp;action=<?php echo $act; ?>" method="post">
                	<label>Name</label>
                    <input type="text" name="name" maxlength="<?php echo $maxName; ?>" value="<?php echo stripslashes($name); ?>" />
                    <br />
                    
                    <label>Email</label>
                    <input type="text" name="email" maxlength="<?php echo $maxEmail; ?>" value="<?php echo stripslashes($email); ?>" />
                    <br />
                    
                    <label>Show Email</label>
                    <select name="showEmail">
                    	<option value="0"<?php if($showEmail == 0) { echo " selected=\"selected\""; } ?>>No, don't show my email.</option>
                        <option value="1"<?php if($showEmail == 1) { echo " selected=\"selected\""; } ?>>Yes, show my email!</option>
                   	</select>
                    <br />
                    
                    <label>Message</label>
                    <textarea name="message" rows="4" cols="30"><?php echo stripslashes($message); ?></textarea>
                    <br />
                    
                    <label><img src="inc/captcha.inc.php" alt="Are You Human?" style="padding-top: 2px;" /></label>
                    <input type="text" name="captcha" maxlength="4" size="6" style="width: 75px;" />
                    <br />
                    
                    <input type="submit" value="Save Message" />
                    <br /><br />
                    <p class="notification yellow">Simple UBB code such as [b][/b], [u][/u], [i][/i] is allowed.</p>
                </form>
<?php
    }
}
// WHEN ACTION IS "View Comment(s)"
else {
	$pid = $_GET['page-id'];
	
	if(empty($pid)) { $pid = 1; }
	
    $sql = "SELECT `name`, `email`, `showEmail`, `enableEmoticons`, `message`, `time` FROM guestbook ORDER BY `id` DESC ";
    $dataQuery = mysql_query($sql);
	$rowsQuery = mysql_num_rows($dataQuery);
	
	
	
    echo "<h2 style='margin-bottom: 25px;'>View Comments (" . $rowsQuery . ") &nbsp; | &nbsp; <a href='?page=guestbook&amp;action=addComment'>Sign the guestbook too!</a></h2>\n";
	echo '';
	
	
    if($rowsQuery == 0) {
        echo "<p>There are currently no guestbook entries. <a href=\"?page=guestbook&amp;action=addComment\">Be first to make one!</a></p>\n";
    }
    else {
		


    $lastPage = ceil($rowsQuery/$websiteProperty->getProperty('max_gb_entries_per_page'));
    
    if($pid < 1) { 
        $pid = 1;
    }
    elseif($pid > $lastPage) {
        $pid = $lastPage;
    }
    
    $max = 'LIMIT ' . (($pid - 1) * $websiteProperty->getProperty('max_gb_entries_per_page')) .',' .$websiteProperty->getProperty('max_gb_entries_per_page'); 
    
    
    $sql = "SELECT `name`, `email`, `showEmail`, `enableEmoticons`, `message`, `time` FROM guestbook ORDER BY `id` DESC " . $max;
    $dataQuery = mysql_query($sql) or die(mysql_error());
    
    
        
		
		echo "<div id='pagination'>";
		
		if ($pid > 1) 

 {

 echo " <a href='?page=" . $_GET['page'] . "&amp;page-id=1#pagination'>&lt;&lt;</a> ";

 echo " ";

 $previous = $pid-1;

 echo " <a href='?page=" . $_GET['page'] . "&amp;page-id=$previous#pagination'>&lt;</a> ";

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
				echo '<a href="?page=' . $_GET['page'] . '&amp;page-id=' . $i . '#pagination"';
				if($pid == $i) {
					echo ' class="current"';
				}
				echo '>' . $i . '</a>';
				$i++;
			}


 //This does the same as above, only checking if we are on the last page, and then generating the Next and Last links

 if ($pid !== $lastPage) 

 {

 $next = $pid+1;

 echo " <a href='?page=" . $_GET['page'] . "&amp;page-id=$next#pagination'>&gt;</a> ";

 echo " ";

 echo " <a href='?page=" . $_GET['page'] . "&amp;page-id=$lastPage#pagination'>&gt;&gt;</a>";

 } 
   
   echo ' </div>';
   
		echo '<div id="guestbook">';
        while($data = mysql_fetch_assoc($dataQuery)) {
    ?>
    <div class="entry">
    <div class="title">
	<?php if($data['showEmail'] == 1) { ?><a href="mailto:<?php echo $data['email']; ?>"><?php } echo stripslashes($data['name']); if($data['showEmail'] == 1) { ?></a><?php } ?> wrote <?php $date = new DateTime($data['time']);
	echo 'on ' . $date->format("l") . " the " . $date->format("jS") . " around " . $date->format("h:s a") . "</div>";
            $message = ubb(nl2br(stripslashes(htmlentities(valid($data['message'])))));
			echo '<div class="content">';
                if($data['enableEmoticons'] == 1) {
                    echo emoticons($message);                
                }
                if($data['enableEmoticons'] == 0) {
                    echo $message;                
                }
				echo '</div>';
            ?>
   	</div>
    <?php
        }
		echo '</div>';
		
		
		echo "<div id='pagination'>";
		
		if ($pid > 1) 

 {

 echo " <a href='?page=" . $_GET['page'] . "&amp;page-id=1#pagination'>&lt;&lt;</a> ";

 echo " ";

 $previous = $pid-1;

 echo " <a href='?page=" . $_GET['page'] . "&amp;page-id=$previous#pagination'>&lt;</a> ";

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
				echo '<a href="?page=' . $_GET['page'] . '&amp;page-id=' . $i . '#pagination"';
				if($pid == $i) {
					echo ' class="current"';
				}
				echo '>' . $i . '</a>';
				$i++;
			}


 //This does the same as above, only checking if we are on the last page, and then generating the Next and Last links

 if ($pid !== $lastPage) 

 {

 $next = $pid+1;

 echo " <a href='?page=" . $_GET['page'] . "&amp;page-id=$next#pagination'>&gt;</a> ";

 echo " ";

 echo " <a href='?page=" . $_GET['page'] . "&amp;page-id=$lastPage#pagination'>&gt;&gt;</a>";

 } 
   
   echo ' </div>';
	}}
?>