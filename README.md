The Three Little Pigs - Siri Proxy by Jimmy Kane :-)
==========

About
-----

Author: Jimmy Kane - Thpryrchn 
Design/WebInterface: Wouter DS

Blog: quartzcoding.blogspot.com

Twitter: http://twitter.com/JimmyKane9

Google+: http://gplus.to/jimmykane


The Three Little Pigs siri proxy server is an intelligent server with key throttling and database connection. It uses Keys from iPhone 4S, iPhone 5, and the iPad 3 (AKA the New iPad)

**A little info upon public/semipublic servers**

So you got a server and you are afraid if apple will ban your device? Lets get things straight then

1. The Three Little Pigs DO NOT send any XACE-HOST, assistantid,speechid to Apple servers. They also get right of the host header. 

2. Apple doesnt ban by UUID (as far as now!)

3. Apple bans by IP! So use proxychains/Tor/tsocks/torsocks/torify etc to fake the ip and DNS

4. Also keep in mind that there is a limit on how many assistants you can create per key per day. I am not sure but 30 is an average. Please correct me if I am mistaken. There will be a new release soon where there will be a limit on that and hopefully this issue will be resolved.

To the point: You got a iPhone 4S, iPhone 5, or iPad 3, you get commandFailed and/or cannot create assistantid? 

Solution 1: Change IP/Network

Solution 2: Delete the /var/mobile/Library/Preferences/com.apple.assistant.plist on the phone (jailbreak req). You can do this by putting `cydia://package/mobi.macciti.assistantdelete` in safari on your iDevice, or just search `assistantdelete` in cydia.

Solution 3: Use other iPhone4s DATA



__if you or someone you know has a jailbroken iPhone 4S then just add http://cydia.myrepospace.com/thpryrchn/ to your Cydia Sources and install `Spire 4S` on your iPhone 4S. Then you can point it to your server just like you do with any other iPhone. This will work on all iOS 5 versions. Also works with non Spire GUI's__

__NOTE: It only adds the Spire in the settings app and will not work without a proper Siri GUI.__




Features
--------

* Authentication 

* Ban protection

* Improved statistics

* User device logging

* User logging

* Improved connection limits 

* Improved Grabber

* Plugins api and config capable (NEW)

* Email Notifications when the key expires (NEW) - Don't forget to setup your email on the config.yml

* MySql Database connection support: Supports MySQL database connection for storing configuration,keys and runtime statistics. Now you can edit and build that (NEW)

* Multiple key support: You can connect more than 1 iPhone 4S, iPhone 5, and the iPad 3 and store even more keys. The more the keys, the more the clients!

* Key Throttling: Each client uses a different key, if more than one Keys are available. The throttler makes sure that each Key is throttled thus enabling several client registration and assistant object creation.

* KeyLoad Safeguard: Never worry about how many people use your iPhone 4S, iPhone 5, and the iPad 3 key. Each Key has a maximum keyload. Even when the key is still valid, if the keyload limit is exceeded, the safeguard disables the key and protects the iPhone4S from getting banned.

* KeyLoad Aware: Checks what key is not "Hot" anymore and periodically decreases the load, thus re-enabling Safeguarded Keys

* Web interface and monitoring: Always know what is happening without a CLI! With a web interface you can check statistics such as active connections, valid keys, server load, keyload etc.

* One certificate for all devices: Both Siri Capable devices (currently only iPhone 4s, iPhone 5, and the iPad 3) and older devices are using the same certificate and the same port (443 default for SSL)

* One instance of the server: Due to one certificate you can run only one instance of the server.

* Bug Free (I hope...) :-) Never worry if the server has crashed. Most of the bugs that were causing the server to crash are fixed now.



Version 0.11.3 
---------------------

* Now supports iOS 6

* Now Supports iPad 3 keys! Even if they are still on iOS 5, the keys now function the same as 4S!

* After updating, make sure you backup your Database (With something like phpMyAdmin), and do a `siriproxy gentables`.


Notice About Plugins
--------------------

We recently changed the way plugins work very significantly. That being the case, your old plugins won't work. 

New plugins should be independent Gems. Take a look at the included [example plugin](https://github.com/plamoni/SiriProxy/tree/master/plugins/siriproxy-example) for some inspiration. We will try to keep that file up to date with the latest features. 


Set-up Instructions
-------------------


**Tutorial for Ubuntu is now here**

Thanks to am3yrus we have a tutorial for ubuntu lovers: [http://www.am3yrus.com/](http://www.am3yrus.com/)

Also the above site holds different setup instructions.
If you go for the above tutorial then there is no need to follow any instructions below. 



**Set up DNS**

* __Not needed if you Jailbrake an iPhone 4S. Just add http://cydia.myrepospace.com/thpryrchn/ to your cydia Sources, and install `Spire 4S` on your 4S. Then you can point it to your server just like you do with any other iPhone 4.__

Before you can use SiriProxy, you must set up a DNS server on your network to forward requests for guzzoni.apple.com to the computer running the proxy (make sure that computer is not using your DNS server!). I recommend dnsmasq for this purpose. It's easy to get running and can easily handle this sort of behavior. ([http://www.youtube.com/watch?v=a9gO4L0U59s](http://www.youtube.com/watch?v=a9gO4L0U59s))
Also if you dont have static ip you can use this dns python server. ([https://github.com/jimmykane/Roque-Dns-Server])


**Set up RVM and Ruby 1.9.3**

If you don't already have Ruby 1.9.3 installed through RVM, please do so in order to make sure you can follow the steps later. Experts can ignore this. If you're unsure, follow these directions carefully:

1. Download and install RVM (if you don't have it already):

	* Download/install RVM:  

		`curl -L https://get.rvm.io | bash -s stable`  

	* Activate RVM:  

		`source "$HOME/.rvm/scripts/rvm"`  

2. Install, use and make default ruby 1.9.3:   

	`rvm requirements` # read and follow instructions, make sure to support gcc-4.2
	`rvm use 1.9.3 --default --install`


**Setup MySQL**

Install MySQL on your system and create a database called siri or whatever you like. 

1. Connect to mysql 
    
	`mysql -h localhost -u root -p `

2. Create the Database

	`CREATE DATABASE siri;`

	
**Set up The Three Little Pigs**

Clone this repo locally, then navigate into the The-Three-Little-Pigs directory (the root of the repo). Then follow these instructions carefully. Note that nothing needs to be (or should be) done as root until you launch the server:

1. Clone the repo

	`git clone https://github.com/jimmykane/The-Three-Little-Pigs-Siri-Proxy`

2. Change path to it

	`cd The-Three-Little-Pigs-Siri-Proxy`
    
3. Install Rake and Bundler:  

	`gem install rake bundler`  

4. Install SiriProxy gem (do this from your SiriProxy directory):  

	`rake install`  

5. Make .siriproxy directory:  

	`mkdir ~/.siriproxy`  

6. Move default config file to .siriproxy (if you need to make configuration changes, do that now by editing the config.yml):  

	`cp ./config.example.yml ~/.siriproxy/config.yml`  

7. Edit `~/.siriproxy/config.yml` and put your database info

        db_host: 'localhost'
        db_user: 'root'
        db_pass: 'yourpassword'
        db_database: 'siri'

8. Edit your `~/.siriproxy/config.yml` and put your server info for certs

        ca_name: 'SiriProxyCA'
        server1: 'guzzoni.apple.com'
        server2: 'your.siri.proxy.server.com'

9. Generate certificates.   

	`siriproxy gencerts`



10. Install `~/.siriproxy/ca.pem` on all your devices including iphone4s etc. This can easily be done by emailing the file to yourself and clicking on it in the iPhone email app. Follow the prompts.

11. Bundle SiriProxy (this should be done every time you change the config.yml):  

	`siriproxy bundle`

12. Create the tables needed for the database. You will only need to do this once. Keep in mind that this will delete all DATA on the tables such as keys and config data

	`siriproxy gentables`

13. Start SiriProxy (must start as root because it uses a port < 1024):  

	`rvmsudo siriproxy server`

	You can also start the server by a re-open script that ensures to restart the server if it crashes

	`./siriproxy-restarter`

14. Test that the server is running by saying "Test Siri Proxy" to your phone.


Note: on some machines, rvmsudo changes "`~`" to "`/root/`". This means that you may need to symlink your "`.siriproxy`" directory to "`/root/`" in order to get the application to work:  

	sudo ln -s ~/.siriproxy /root/.siriproxy


**Installing the Web Interface**

Make sure you have apache2,mysql,php,php-mysql in common words LAMP setup up and ready.

A documentation on how to do this on Ubuntu is here. ([https://help.ubuntu.com/community/ApacheMySQLPHP])

1. Edit the `webInterface/inc/config.inc.php` and enter the database connection info and your dns info admin pass etc

2. Create the certificate folder `mkdir webInterface/certificates`

3. Copy the certificate from `~/.siriproxy/ca.pem` to `webInterface/certificates/ca.pem`

4. Copy all files under webInterface to your apache html docs folder

5. (Optional) Open `pages` folder on your htlm folder path and delete whatever page you dont want! Also edit `pages/pages.xml` with the stuff you need!

    

**Updating SiriProxy**

Once you're up and running, if you modify the code, or you want to grab the latest code from GitHub, you can do that easily using the "siriproxy update" command. Here's a couple of examples:

`siriproxy update`  
	
Installs the latest code from the [master] branch on GitHub.
	
`siriproxy update /path/to/SiriProxy`  

Installs the code from /path/to/SiriProxy
	
	
Snow Leopard & Lion Set-up Instructions
---------------------------------------


**This has only been tested on Snow Leopard and Lion**



**Set up DNS**

* __Not needed if you Jailbrake an iPhone 4S. Just install Spire on your 4S. Then you can point it to your server just like you do with any other iPhone 4.__

Before you can use SiriProxy, you must set up a DNS server on your network to forward requests for guzzoni.apple.com to the computer running the proxy (make sure that computer is not using your DNS server!). I recommend dnsmasq for this purpose. It's easy to get running and can easily handle this sort of behavior. ([http://www.youtube.com/watch?v=a9gO4L0U59s](http://www.youtube.com/watch?v=a9gO4L0U59s))
Also if you dont have static ip you can use this dns python server. ([https://github.com/jimmykane/Roque-Dns-Server])



**Let's make sure everything is up to date**

These instructions assume you have installed Xcode, and Macports already.

   * Update makeports `sudo port selfupdate`

   * Update Outdated Ports `sudo port upgrade outdated`

   * Install Required Ports `sudo port install git-core mono libksba`

**Install MySQL**

   * Download DMG from ([mysql.com](http://dev.mysql.com/downloads/mysql/5.1.html#macosx-dmg)) Use 64bit (Unless you have a old intel core due)

   * Install everything in the package in this order: mysql, the startup item, the preference pane.

   * Start MySQL in the preference pane.

   * Secure your MySQL server

	`/usr/local/mysql/bin/mysqladmin -u root password [your password goes here]`

   * Install it in your path (optional, but can make things easier)

		sudo -s
		echo "/usr/local/mysql/bin" >> /etc/paths
		exit

   * Make everything compatible with PHP and stuff..

		sudo mkdir /var/mysql
		sudo ln -s /tmp/mysql.sock /var/mysql/mysql.sock


**Set up RVM and Ruby 1.9.3**

If you don't already have Ruby 1.9.3 installed through RVM, please do so in order to make sure you can follow the steps later. Experts can ignore this. If you're unsure, follow these directions carefully:

1. Download and install RVM (if you don't have it already):

	* Download/install RVM:  

		`curl -L https://get.rvm.io | bash -s stable`  

	* Activate RVM:  

		`source $HOME/.rvm/scripts/rvm`  


2. Install, use and make default ruby 1.9.3:   

	`rvm requirements` # read and follow instructions, make sure to support gcc-4.2
	`rvm use 1.9.3 --default --install`


**Setup MySQL**

Install MySQL on your system and create a database called siri or whatever you like. 

1. Connect to mysql 
    
	`/usr/local/mysql/bin/mysql -h localhost -u root -p `

2. Create the Database

	`CREATE DATABASE siri;` then `quit` to exit

3. Setup MySql Gem

	`gem install mysql -- --with-mysql-config=/usr/local/mysql/bin/mysql_config`
	
**Set up The Three Little Pigs**

Clone this repo locally, then navigate into the The-Three-Little-Pigs directory (the root of the repo). Then follow these instructions carefully. Note that nothing needs to be (or should be) done as root until you launch the server:

1. Clone the repo

	`git clone https://github.com/jimmykane/The-Three-Little-Pigs-Siri-Proxy`

2. Change path to it

	`cd The-Three-Little-Pigs-Siri-Proxy`

	Answer yes to "Do you wish to trust this .rvmrc file?"
    
	* _If you miss it, just type in `rvm reload` and you can do it again._
        * Alternatively you can force trusting with: `rvm rvmrc trust .`


3. Install Rake and Bundler:  

	`gem install rake bundler`  

4. Install mysql & SiriProxy gem (do this from your SiriProxy directory):  

	`gem install mysql -- --with-mysql-config=/usr/local/mysql/bin/mysql_config`

	`rake install`  

5. Make .siriproxy directory:  

	`mkdir ~/.siriproxy`  

6. Move default config file to .siriproxy (if you need to make configuration changes, do that now by editing the config.yml):  

	`cp ./config.example.yml ~/.siriproxy/config.yml`  

7. Edit `~/.siriproxy/config.yml` and put your database info

        db_host: 'localhost'
        db_user: 'root'
        db_pass: 'yourpassword'
        db_database: 'siri'

6. Edit your `~/.siriproxy/config.yml` and put your server info for certs

        ca_name: 'SiriProxyCA'
        server1: 'guzzoni.apple.com'
        server2: 'your.siri.proxy.server.com'

7. Generate certificates.   

	`siriproxy gencerts`



8. Install `~/.siriproxy/ca.pem` on all your devices including iphone4s etc. This can easily be done by emailing the file to yourself and clicking on it in the iPhone email app. Follow the prompts.

9. Bundle SiriProxy (this should be done every time you change the config.yml):  

	`siriproxy bundle`

10. Create the tables needed for the database. You will only need to do this once. Keep in mind that this will delete all DATA on the tables such as keys and config data

        siriproxy gentables


** Starting your SiriProxy server **

1. Go to the directory where it was cloned to. Usually this:

	`cd ~/The-Three-Little-Pigs-Siri-Proxy/`


	Start SiriProxy 

	* If you are using port less than 1024 (must start as root because it uses a port < 1024):  

		`rvmsudo siriproxy server`

	* 1025 or higher

		`siriproxy server`


    You can also start the server by a re-open script that ensures to restart the server if it crashes

     * Port 1024 or less

		`rvmsudo ./siriproxy-restarter`

	* Port 1025 or higher

		`./siriproxy-restarter`


2. Test that the server is running by saying "Test Siri Proxy" to your phone.


**Installing the Web Interface on Mac**

Make sure you enable Web Sharing in System Preferences.

1. Edit the `webInterface/inc/config.inc.php` and enter the database connection info and your dns info admin pass etc

2. Create the certificate folder `mkdir ~/The-Three-Little-Pigs-Siri-Proxy/webInterface/certificates`

3. Copy the certificate from `~/.siriproxy/ca.pem` to `webInterface/certificates/ca.pem`

	`cp ~/.siriproxy/ca.pem ~/The-Three-Little-Pigs-Siri-Proxy/webInterface/certificates/ca.pem`

4. Copy all files under webInterface to your /Library/WebServer/Documents/ folder

5. (Optional) Open `pages` folder on your htlm folder path and delete whatever page you dont want! Also edit `pages/pages.xml` with the stuff you need!

6. Enable PHP because it isn't by default on a mac

	`sudo nano /etc/apache2/httpd.conf` 

	around line 111 uncomment this line: 

	`LoadModule php5_module libexec/apache2/libphp5.so`

7. Setup PHP with it's defaults.

		sudo cp -p /etc/php.ini.default /etc/php.ini
		sudo chmod 666 /etc/php.ini

8. Restart Apache

	`sudo apachectl restart`

9. In your browser, try out your server!

	`http://localhost`
    

**Updating SiriProxy**

Once you're up and running, if you modify the code, or you want to grab the latest code from GitHub, you can do that easily using the "siriproxy update" command. Here's a couple of examples:

	`siriproxy update`  
	
Installs the latest code from the [master] branch on GitHub.
	
	`siriproxy update /path/to/SiriProxy`  

Installs the code from /path/to/SiriProxy
	
	

FAQ
---

**Will this let me run Siri on my iPhone 4, iPod Touch, iPhone 3G, Microwave, etc?**

Yes. If you have Grabbed the keys

**How do I set up a DNS server to forward Guzzoni.apple.com traffic to my computer?**

Check out  this: 

[http://www.youtube.com/watch?v=a9gO4L0U59s](http://www.youtube.com/watch?v=a9gO4L0U59s)

**Problems with DNS and Iphone4S not connecting? (Not showing in command line)**

If you are using ubuntu please consider that many Ubuntu installations run the named DNS server. To use the DNSMASQ server do the dollowing at each reboot:

	sudo /etc/init.d/dnsmasq stop -> To stop DNS Server
	sudo killall named -> to stopped stupid named service (another dns server)
	sudo /et/init,d/dnsmasq start -> to restart dnsserver

**My Siri isn't working on my device, but it does connect to my Siriproxy, and I have 4S keyes**

Try Deleting your com.apple.assistant.plist. You can do this by putting `cydia://package/mobi.macciti.assistantdelete` in safari on your iDevice, or just search `assistantdelete` in cydia.

**Gem is not installing?**

0. Type `rvm requirements `and install all packages that RVM proposes!

1. Review the error output and run the manual install as suggested.

2. Notice the missing libraries. My usual missing ones are mysql-devel,zlib,zlib-devel,libxml2.



**How do I remove the certificate from my iPhone when I'm done?**

Just go into your phone's Settings app, then go to "General->Profiles." Your CA will probably be the only thing listed under "Configuration Profiles." It will be listed as "SiriProxyCA" Just click it and click "Remove" and it will be removed. (Thanks to [@tidegu](http://www.twitter.com/tidegu) for asking!)



Licensing
---------

Re-use of my code is fine under a Creative Commons 3.0 [Non-commercial, Attribution, Share-Alike](http://creativecommons.org/licenses/by-nc-sa/3.0/) license. In short, this means that you can use my code, modify it, do anything you want. Just don't sell it and make sure to give me a shout-out. Also, you must license your derivatives under a compatible license (sorry, no closed-source derivatives). If you would like to purchase a more permissive license (for a closed-source and/or commercial license), please contact me directly. See the Creative Commons site for more information.

Acknowledgements
---------------

**Credits, greetings and big thanks to all the following.. RESPECT**

Mr. Nirodimos for helping with the iphone4s 

  @Appdium For explaining the Siri protocol and cracking it!

  @plamoni ([http://twitter.com/plamoni])

  @kmsbueromoebel ([http://ketchup-mayo-senf.de/blog/])

  @WouterDS ([http://twitter.com/WouterDS])

  @thpryrchn ([[https://twitter.com/thpryrchn])

  @Grant Paul (chpwn) ([https://twitter.com/chpwn])

  @Pod2g ([https://twitter.com/pod2g])

  @iH8sn0w ([https://twitter.com/iH8sn0w])

  @MuscleNerd ([https://twitter.com/MuscleNerd ])

  @comex ([https://twitter.com/comex])

  @HisyamNasir ([https://twitter.com/HisyamNasir])

  @Zach Christopoulos ([https://twitter.com/ChristopoulosZ])

  @Stan Hutcheon ([https://twitter.com/StanHutcheon])

  @THiZIZMiZZ ([https://twitter.com/THiZIZMiZZ])

  @iP1neapple ([https://twitter.com/iP1neapple])
  


Disclaimer
----------

**Warning**
I'm not affiliated with Apple in any way. They don't endorse this application. They own all the rights to Siri (and all associated trademarks). 
This software is provided as-is with no warranty whatsoever. Apple could do things to block this kind of behavior if they want. Also, if you cause problems (by sending lots of trash to the Guzzoni servers or anything), I fully don't support Apple's right to ban your UDID (making your phone unable to use Siri). They can, and I wouldn't blame them if they do.

**End**
