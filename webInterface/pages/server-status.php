<?php
$keys = getkeys();
$config = getconfig();
$count = 0;
$server_running = checkServer();
?>
<h1>Server Status and Statistics <?php if (isAdministrativeUser())
    echo ' - Administrator'; ?></h1>
<h2>Legend</h2>
<p><ul>
    <li><b>Available Keys:</b> Shows how many <b>not</b> expired keys  are available on the database.</li>
    <li><b>Maximum connections  Per Key (New):</b> The maximum number of <b>concurrent</b> connections the server can handle for <b>each key</b>. When this number is reached the server stops for protecting the keys</li>
    <li><b>Active Connections:</b> The current number of concurrent connections. Refreshed every 10 sec. <b>When the server has no Validation Data or the key is overloaded then this is set to 999 for allowing a 4s to connect</b></li>
    <li><b>Maximum Keyload:</b> The maximum Requests based upon Session. When this value is reached the key pauses</li>
    <li><b>Keyload Dropdown:</b> How much the keyload will drops after the keyload interval has passed </li>
    <li><b>Keyload Dropdown Interval:</b> How often the agent will check for an overloaded key and dropdown the keyload (see above)</li>
</ul></p>
<br />
<table>
    <tr>
        <th>Server status</th>
        <td>
            <?php
            if ($server_running == true)
                echo '<p class="notification green">ON</p>';
            else
                echo '<p class="notification red">OFF</p>';
            ?>
        </td>        
    </tr>
    <tr>
        <th>Available keys</th>
        <td><?php
            if ($keys == false)
                echo '<p class="notification red">0</p>';
            else
                echo '<p class="notification green">' . $keys[0]['availablekeys'] . '</p>';
            ?>
        </td>
    </tr>
    <tr>
        <th>Active connections</th>
        <td><?php
            if ($config['active_connections'] >= $config['max_connections'])
                echo '<p class="notification red">' . $config['active_connections'] . '</p>';
            else
                echo '<p class="notification green">' . $config['active_connections'] . '</p>';
            ?>
        </td>

    </tr>
    <tr>
        <th>Maximum connections Per Key</th>
        <?php if (isAdministrativeUser()) { ?>
            <td>
                <form name="ConfigUpdate" id="ConfigUpdate" method="post" action="inc/config_update_db.php" onsubmit="return ValidateFormMaxConnections(); ">
                    <input size="6" type="text" name="MaxConnections" id="MaxConnections" maxlength="4" value="<?php echo $config['max_connections'] ?>"/>
                    <input title="Update Max Connections" type="image" SRC="design/img/refresh.png" HEIGHT="32" WIDTH="32" BORDER="0" ALT="Submit Form"/>
                </form>
            </td>
        <?php } else {
            ?>
            <td><?php echo $config['max_connections'] ?></td>
            <?php
        }
        ?>
    </tr>

    <tr>
        <th>Max KeyLoad</th>
        <?php if (isAdministrativeUser()) { ?>        
            <td>
                <form name="ConfigUpdate" id="ConfigUpdate" method="post" action="inc/config_update_db.php" onsubmit="return ValidateFormMaxKeyload(); ">
                    <input size="6" type="text" name="MaxKeyload" id="MaxKeyload" maxlength="4" value="<?php echo $config['max_keyload'] ?>"/>
                    <input title="Update Max Keyload" type="image" SRC="design/img/refresh.png" HEIGHT="32" WIDTH="32" BORDER="0" ALT="Submit Form"/>
                </form>
            </td>
        <?php } else { ?>
            <td><?php echo $config['max_keyload'] ?></td>
            <?php
        }
        ?>

    </tr>
    <tr>
        <th>Keyload Dropdown</th>
        <?php if (isAdministrativeUser()) { ?>
            <td>
                <form name="ConfigUpdate" id="ConfigUpdate" method="post" action="inc/config_update_db.php" onsubmit="return ValidateFormKeyloadDropdown(); ">
                    <input size="6" type="text" name="KeyloadDropdown" id="KeyloadDropdown" maxlength="4" value="<?php echo $config['keyload_dropdown'] ?>"/>
                    <input title="Update Keyload Dropdown" type="image" SRC="design/img/refresh.png" HEIGHT="32" WIDTH="32" BORDER="0" ALT="Submit Form"/>
                </form>
            </td>
        <?php } else {
            ?>
            <td><?php echo $config['keyload_dropdown'] ?></td>
            <?php
        }
        ?>

    </tr>
    <tr>
        <th>Keyload Check Interval (sec)</th>
        <?php if (isAdministrativeUser()) { ?>
            <td>
                <form name="ConfigUpdate" id="ConfigUpdate" method="post" action="inc/config_update_db.php" onsubmit="return ValidateFormKeyloadCheckInterval(); ">
                    <input size="6" type="text" name="KeyloadCheckInterval" id="KeyloadCheckInterval" maxlength="4" value="<?php echo $config['keyload_dropdown_interval'] ?>"/>
                    <input title="Update Check Interval" type="image" SRC="design/img/refresh.png" HEIGHT="32" WIDTH="32" BORDER="0" ALT="Submit Form"/>
                </form>
            </td>
        <?php } else { ?>
            <td><?php echo $config['keyload_dropdown_interval'] ?></td>
            <?php
        }
        ?>       
    </tr>
</table>
<br />
<h1>Available keys</h1>
<h2>Legend</h2>
<p>
<ul>
    <li><b>Speechid</b> The speech identifier send from the iphone4s</li>
    <li><b>Assistantid</b> The assistant identifier from the iphone4s</li>
    <li><b>Validation Data</b> The validation data from the iphone4s</li>
    <li><b>Keyload</b> The current load on each key. When the limit is reached the key gets "HOT", and therefore pauses for a short of time (throttles)</li>
    <li><b>Date Added</b> The date and time that the key was added!</li>        
</ul>
</p>
<br />
<?php if ($keys != false && $keys[0]['availablekeys'] > 0) { ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Speech ID</th>
            <th>Assistant ID</th>
            <th>Validation Data (24h)</th>
            <th>Keyload</th>
            <th>Date Added</th>
            <?php 
            if (isAdministrativeUser()){
                echo '<th>Update</th>';
            }
            ?>
        </tr>
        <?php
        foreach ($keys as $key) {
            $count++;
            if (isAdministrativeUser()){ ?>
         
                <tr>
                    <form name="KeyUpdate" id="KeyUpdate" method="post" action="inc/key_update_db.php" onsubmit="return ValidateFormKeyUpdate(); ">
                        <td>
                            <?php echo $key['id'] ?>
                            <input size="20" type="hidden" name="KeyId" id="KeyId"  value="<?php echo $key['id'] ?>"/> 
                        </td>
                    <td>
                       <input size="20" type="text" name="SpeechId" id="SpeechId"  value="<?php echo $key['speechid'] ?>"/> 
                    </td>
                    <td>
                       <input size="20" type="text" name="AssistantId" id="AssistantId"  value="<?php echo $key['assistantid'] ?>"/> 
                    </td>
                    <td>
                       <input size="20" type="text" name="ValidationData" id="ValidationData"  value="<?php echo $key['sessionValidation'] ?>"/> 
                    </td>
                    <td>
                       <input size="8" type="text" name="KeyLoad" id="KeyLoad"  maxlength="4" value="<?php echo $key['keyload'] ?>"/> /  <?php echo $config['max_keyload'] ?>
                    </td>
                    <td>
                     <?php echo $key['date_added'] ?>
                    </td>
                    <td>
                          <input title="Update Key Data" type="image" SRC="design/img/refresh.png" HEIGHT="32" WIDTH="32" BORDER="0" ALT="Submit Form"/>
                    </td>
                    </form>
                </tr>
         
                
                
            <?php    
            }else{
               
            ?>
            <tr> 
                <td><?php echo $key['id'] ?></td>
                <td><?php echo '****' . substr($key['speechid'], -9); ?></td>
                <td><?php echo '****' . substr($key['assistantid'], -9); ?></td>
                <td>-------</td>
                <td>
                    <?php if ($key['keyload'] >= $config['max_keyload']) { ?>
                        <p class="notification red">Overloaded</p>
                        <?php
                    } else {
                        echo $key['keyload'] . ' / ' . $config['max_keyload'];
                    }
                    ?>
                </td>
                <td>
                    <?php echo $key['date_added'] ?>
                </td>
            </tr>
            
        <?php 
            }
        } 
        ?>
    </table>

<?php } else { ?>
    <p class="notification red">There are no keys available right now, feed the piggies!</p>

<?php } ?>
<br />

<h2>Contribute for further development.</h2>
<div style="overflow: hidden;">
    <div style="float: left; width: 600px;">
        <p>Contribute for further development<br />Donations are welcome in order to keep me developing this project at these hard times. </p>
    </div>
    <div style="float: right; width: 300px; padding-top: 20px;">
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
        </form>
    </div>
</div>

<script type="text/javascript" language="javascript">
    function ValidateFormMaxConnections(){    
        if ((document.getElementById('MaxConnections').value=="")) {
            alert("Warning\n You havent filled the Maximum Connection");
            return false;
        }
        else
            return true;
    }
    function ValidateFormMaxKeyload(){
        if ((document.getElementById('MaxKeyload').value=="")) {
            alert("Warning\n You havent filled the Maximum Keyload");
            return false;
        }
        else
            return true;
    }
    function ValidateFormKeyloadDropdown(){
        if ((document.getElementById('KeyloadDropdown').value=="")) {
            alert("Warning\n You havent filled the Keyload Dropdown");
            return false;
        }
        else
            return true;
    }
    function ValidateFormKeyloadCheckInterval(){
        if ((document.getElementById('KeyloadCheckInterval').value=="")) {
            alert("Warning\n You havent filled the Keyload Check Interval");
            return false;
        }
        else
            return true;
    }
    
    function ValidateFormKeyUpdate(){
        if ((document.getElementById('UserNameLogin').value=="")
            || (document.getElementById('LoginPasswordLogin').value =="")) {
            alert("Warning\n You havent filled all the required fields");
            return false;
        }
        else
            return true;
    }
</script>

<script type="text/javascript"><!--
    google_ad_client = "ca-pub-6472702431228368";
    /* The Three Little Pigs */
    google_ad_slot = "4196627207";
    google_ad_width = 728;
    google_ad_height = 90;
    //-->
</script>
<script type="text/javascript"
        src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>