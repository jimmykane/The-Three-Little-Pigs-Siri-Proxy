<h1>The Three Little Pigs, what is it?</h1>

<p>This server uses a very simple but clever and precocious tactic to server a larger number of devices and at the same time protects the key.
I editing and forked @plamoni's and  @kmsbueromoebel 's server just  because Apple followed a very bad tactic. That was Siri assistant  software restriction to older devices, done only for marketing reason (my humble opinion).</p>

<h1>So lets get to the point how does this work?</h1>

<p><b>Step 1:</b></p>

<p>Lets say two Siri Capable Devices (currently only iPhone4s) connect to server. The server saves the keys on the Database. 
Each key has assistantid,speechid, sessionValidationData,expired,keyload and date_added columns. Lets see what they are about:</p>
<ul>
    
<li><b>assistantid</b> - The assistant identifier. Unique for each device. In other words this defines your Siri and how it responds. eg. If I gave you my assistantid then Siri would get confused and sometimes respond to you with my nickname. Ugly.
Although it's stored on the database its not used. Each older device that connects on the server creates a new assistantid via using the sessionValidationData.</li>
<li><b>speechid</b> - The speech identifier. Again unique for each device like above.</li>
<li><b>sessionValidationData</b> - The most importand field and the only thing needed for using siri on older devices. These validation data is a big string that gets generated every 24 hours on Siri Capable Devices via FairPlayed. These validation data allow only a small number of assistant's to be created and are somehow linked to the original Siri Capable Device</li>
<li><b>expired</b> - It changed to true when the sessionValidation Data (mentioned above) expire. Default value="False" as enum</li>
<li><b>keyload</b> - How much the key has been used. The default values is 0 and the maximum value is 1000. Each time a device connects and makes a session the keyload increases by 10. So each key can serve up to 100 connection sessions until it gets overloaded and pauses up for a period of time</li>
</ul>
        <p><b>Ok the keys are saved what now?</b></p>

<ul>
    
<li>Each time a older device connects, the server finds the key with the least keyload and uses that to forge the packets with the sessionValidationData in order to be accepted by guzzoni.apple.com (Apple's siri servers)</li>
<li>Each older device that connects uses the sessionValidationData to create a assistant.</li>
<li>After the assistant is created it can use siri via the proxy for speech  recognition and views creation.</li>
<li>When one of the keys are overloaded (reaches the keyload max values) the server then does not forge the packets thus stops misusement of  the key.</li>
<li>After 15 minutes of  the overloaded key drops -100 lets another 10 sessions to be created and so on until the key expires.</li>
<li>This helps a lot to pretend that its a normal everyday use (with some finetuning to the above values and limits) and spoofing/securing the Siri Capable Devices.</li>
<li>Also a webgui can help you monitor the active concurrent connections, and all the above in realtime. You can forget ssh'ing to your VPS and using screen to view how the server is doing.</li>
</ul>    
    
<p><b>The source code is available on <a href="https://github.com/jimmykane">git-hub</a></p>

<p><strong>Credits, greetings and big thanks to all the following.. RESPECT</strong> (in line of order)
<br />
<ol>
  <li><a href="http://twitter.com/plamoni">@plamoni</a></li>  
  <li><a href="http://ketchup-mayo-senf.de/blog/">@kmsbueromoebel</a></li>
  <li><a href="http://twitter.com/WouterDS">@WouterDS</a></li>
  <li><a href="https://twitter.com/thpryrchn">@thpryrchn</a></li>
  <li><a href="https://twitter.com/chpwn">@Grant Paul (chpwn)</a></li>
  <li><a href="https://twitter.com/pod2g">@Pod2g</a></li>
  <li><a href="https://twitter.com/iH8sn0w">@iH8sn0w</a></li>
  <li><a href="https://twitter.com/MuscleNerd">@MuscleNerd</a></li>
  <li><a href="https://twitter.com/comex">@comex</a></li>
  <li><a href="https://twitter.com/HisyamNasir">@HisyamNasir</a></li>
  <li><a href="https://twitter.com/ChristopoulosZ">@Zach Christopoulos</a></li>
  <li><a href="https://twitter.com/StanHutcheon">@Stan Hutcheon</a></li>
  <li><a href="https://twitter.com/THiZIZMiZZ">@THiZIZMiZZ</a></li>
  <li><a href="https://twitter.com/iP1neapple">@iP1neapple</a></li>
</ol></p>

<br />

<h2>Contribute to help us to keep the server up and for further development.</h2>
<div style="overflow: hidden;">
    <div style="float: left; width: 600px;">
    	<p>If you like this server, please help us paying for the server costs.<br />Donations are welcome in order to keep me developing this project at these hard times. </p>
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