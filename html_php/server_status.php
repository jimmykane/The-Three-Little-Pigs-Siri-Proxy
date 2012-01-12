<?php

require_once("functions.php");
$page_title = 'Spire Proxy Server Status';
$page_id = 2;
require_once("top.php");
$keys = getkeys();
$config = getconfig();
$count = 0;
?>
<h1>Server Status and Statistics</h1>
<p><b>Legent</b></p>
<ul>
    <li><b>Available Keys: </b>Shows how many <b>not</b> expired keys  are available on the database.</li>
    <li><b>Maximum connections:</b> The maximum number of <b>concurrent</b> connections the server can handle. When this number is reached the server stops for protecting the keys</li>
    <li><b>Active Connections</b> The current number of concurrent connections. Refreshed every 2 sec</li>
    <li><b>Maximum Keyload</b> The maximum keyload threshold. When this value is reached the key pauses</li>
    <li><b>Keyload Dropdown</b> How much the keyload will drops after the keyload interval has passed </li>
    <li><b>Keyload Dropdown Interval</b> How often the agent will check for an overloaded key and dropdown the keyload (see above)</li>
</ul>
<p>&nbsp;</p>
<table border="1" >
    <tr>
        <td> 
            <p> 
                <b>  Server Status </b>
            </p>
        </td>
        <td>
            <p >
                <b>ON</b>
            </p>
        </td>
    </tr>
    <tr>
        <td>
            <p>
                <b> Available Keys </b>
            </p>
        </td>
        <td>
            <p>
                <b>(<?= $keys[0]['availablekeys'] ?>)</b>
            </p>
        </td>
    </tr>
    <tr>
        <td>
            <p>
                <b>Maximum Connections</b>
            </p>
        </td>

        <td>
            <p>
                <b><?= $config['max_connections'] ?></b>
            </p>
        </td>
    </tr>
    <tr>
        <td>
            <p>
                <b>Active Connections</b>
            </p>
        </td>

        <td>
            <p>
                <b><?= $config['active_connections'] - 2 #-2 because 1 for guzzoni and 1 for server    ?></b>
            </p>
        </td>
    </tr>
    <tr>
        <td>
            <p>
                <b>Max Keyload</b>
            </p>
        </td>

        <td>
            <p>
                <b><?= $config['max_keyload'] ?></b>
            </p>
        </td>
    </tr>
    <tr>
        <td>
            <p>
                <b>Keyload dropdown</b>
            </p>
        </td>

        <td>
            <p>
                <b><?= $config['keyload_dropdown'] ?></b>
            </p>
        </td>
    </tr>
    <tr>
        <td>
            <p>
                <b>Key load check interval (second)</b>
            </p>
        </td>

        <td>
            <p>
                <b><?= $config['keyload_dropdown_interval'] ?> sec</b>
            </p>
        </td>
    </tr>
</table>
<p>&nbsp;</p>
<h1>Available keys</h1>
<p><b>Legent:</b></p>
<ul>
    <li><b>Speechid</b> The speech identifier send from the iphone4s</li>
    <li><b>Asiistantid</b> The assistant identifier from the iphone4s</li>
    <li><b>Validation Data</b> The validation data from the iphone4s</li>
    <li><b>Keyload</b> The current load on each key. When the limit is reached the key gets "HOT", and therefore pauses for a short of time (throttles)</li>
    <li><b>Date Added</b> The date and time that the key was added!</li>        
</ul>
<p>&nbsp;</p>
<?php if ($keys != false && $keys[0]['availablekeys'] > 0) { ?>
    <table width="auto" border="1">
        <tr>
            <td>
                <p>
                    <b>id</b>
                </p>
            </td>
            <td>
                <b>SpeechId</b>
            </td>
            <td>
                <b>AssistantId</b>
            </td>
            <td>
                <b>Validation Data (24h)</b>
            </td>
            <td>
                <b>Keyload</b>
            </td>
            <td>
                <b>Date Added</b>
            </td>
        </tr>
        <?php
        foreach ($keys as $key) {
            $count++;
            ?>
            <tr>
                <td>
                    <?= $count ?>
                </td>
                <td>
                    spoofed
                </td>
                <td>
                    spoofed
                </td>
                <td>-------

                </td>
                <td>
                    <?php if ($key['keyload'] >= $config['max_keyload']) { ?>
                        <p style="color: red;"> <b>Overloaded</b></p>

                    <?php } else { ?>

                        <?= $key['keyload'] ?> / <?= $config['max_keyload'] ?>
                        <?php
                    }
                    ?>


                </td>
                <td>
                    <?= $key['date_added'] ?>
                </td>
            </tr>

        <?php } ?>
    </table>

<?php } else { ?>
    <p>No key Available right now.... Feed the piggy!</p>

<?php } ?>
<div class="left">
    <h2>Donations</h2>
    <p>Donations are welcome in order to keep me developing this project at these hard times. </p>
    <!-- Paypal button -->
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
               ">
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
    </form>
    <!-- / Paypal button -->
</div>
<?php
require_once("footer.php");
?>
