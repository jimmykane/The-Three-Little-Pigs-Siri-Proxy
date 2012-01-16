<?php
	$keys = getkeys();
	$config = getconfig();
	$count = 0;      
?>
<h1>Server Status and Statistics</h1>
<h2>Legend</h2>
<p><ul>
    <li><b>Available Keys:</b> Shows how many <b>not</b> expired keys  are available on the database.</li>
    <li><b>Maximum connections:</b> The maximum number of <b>concurrent</b> connections the server can handle. When this number is reached the server stops for protecting the keys</li>
    <li><b>Active Connections</b> The current number of concurrent connections. Refreshed every 2 sec</li>
    <li><b>Maximum Keyload</b> The maximum keyload threshold. When this value is reached the key pauses</li>
    <li><b>Keyload Dropdown</b> How much the keyload will drops after the keyload interval has passed </li>
    <li><b>Keyload Dropdown Interval:</b> How often the agent will check for an overloaded key and dropdown the keyload (see above)</li>
</ul></p>
<br />
<table>
	<tr>
    	<th>Server status</th>
        <td>ON</td>
   	</tr>
    <tr>
        <th>Available keys</th>
        <td><?php echo $keys[0]['availablekeys'] ?></td>
   	</tr>
    <tr>
        <th>Maximum connections</th>
        <td><?php echo $config['max_connections'] ?></td>
   	</tr>
    <tr>
        <th>Active connections</th>
        <td><?php
				if($config['active_connections'] >= 2) {
        			echo $config['active_connections'] - 2; #-2 because 1 for guzzoni and 1 for server
				}
				else {
					echo $config['active_connections'];
				}
			?></td>

   	</tr>
    <tr>
        <th>Max keyload</th>
        <td><?php echo $config['max_keyload'] ?></td>
   	</tr>
    <tr>
        <th>Keyload dropdown</th>
        <td><?php echo  $config['keyload_dropdown'] ?></td>
   	</tr>
    <tr>
        <th>Keyload check interval (sec)</th>
        <td><?php echo  $config['keyload_dropdown_interval'] ?></td>
   	</tr>
</table>
<br />
<h1>Available keys</h1>
<h2>Legend</h2>
<p>
<ul>
    <li><b>Speechid</b> The speech identifier send from the iphone4s</li>
    <li><b>Asiistantid</b> The assistant identifier from the iphone4s</li>
    <li><b>Validation Data</b> The validation data from the iphone4s</li>
    <li><b>Keyload</b> The current load on each key. When the limit is reached the key gets "HOT", and therefore pauses for a short of time (throttles)</li>
    <li><b>Date Added</b> The date and time that the key was added!</li>        
</ul>
</p>
<br />
<?php if ($keys!=false&&$keys[0]['availablekeys'] > 0) { ?>
<table>
	<tr>
    	<th>ID</th>
        <th>Speech ID</th>
        <th>Assistant ID</th>
        <th>Validation Data (24h)</th>
        <th>Keyload</th>
        <th>Date Added</th>
   	</tr>
        <?php
        foreach ($keys as $key) {
            $count++;
            ?>
            <tr> 
                <td><?php echo $key['id'] ?></td>
                <td><?php echo $key['speechid'] ?></td>
                <td><?php echo $key['assistantid'] ?></td>
                <td>-------</td>
                <td>
					<?php if($key['keyload'] >= $config['max_keyload']) { ?>
                        <p class="notification red">Overloaded</p>
                    <?php } else {
						echo $key['keyload'] .' / ' . $config['max_keyload'];
                    }
                    ?>
                </td>
                <td>
        			<?php echo $key['date_added'] ?>
                </td>
            </tr>
    <?php } ?>
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
