The Three Little Pigs - Siri Proxy by Jimmy Kane :-)
==========

About
-----

Author: Jimmy Kane - thpryrchn - Wouter DS

Twitter: http://twitter.com/JimmyKane9

Google+: http://gplus.to/jimmykane


The Three Little Pigs siri proxy server is an intelligent server with key throttling and database connection.



Version 0.9a
-----------

-Added premature admin login and settings change via web. See webInterface/inc/config.inc.php
-Added max connections per key!
-Changed method about the keyload increases. Now its based upon session utilization!
-Fixed a bug where when a key expired and a client established a connection the wrong key was marked expired
-Bug fixes





Version 0.8b
-----------

Fixed several bugs, added email notifications, key protection and much more


**Updating from v.0.7**

There is no need to do anything more (create db etc) than these steps

1. Run `siriproxy update`

2. Edit ` ~/.siriproxy/config.yml` with the new email setup lines from `config.example.yml` !

3. Start the server `siriproxy server`

4. (Optional) Update to the new webinterface. Just copy all files from `webInterface/` to your html/docs and dont forget to edit the database connection info in `inc/connection.inc.php`

    



Features
--------

-Plugins api and config capable (NEW)

-Email Notifications when the key expires (NEW) - Dont forget to setup your email on the config.yml

-MySql Database connection support: Supports MySQL database connection for storing configuration,keys and runtime statistics. Now you can edit and build that (NEW)

-Multiple key support: You can connect more than 1 iPhone4S and store even more keys. The more the keys, the more the clients!

-Key Throttling: Each client uses a different key, if more than one Keys are available. The throttler makes sure that each Key is throttled thus enabling several client registration and assistant object creation.

-KeyLoad Safeguard: Never worry about how many people use your iPhone4S key. Each Key has a maximum keyload. Even when the key is still valid, if the keyload limit is exceeded, the safeguard disables the key and protects the iPhone4S from getting banned.

-KeyLoad Aware: Checks what key is not "Hot" anymore and periodically decreases the load, thus re-enabling Safeguarded Keys

-Web interface and monitoring: Always know what is happening without a CLI! With a web interface you can check statistics such as active connections, valid keys, server load, keyload etc.

-One certificate for all devices: Both Siri Capable devices (currently only iPhone4s) and older devices are using the same certificate and the same port (443 default for SSL)

-One instance of the server: Due to one certificate you can run only one instance of the server.

-Bug Free (I hope...):-) Never worry if the server has crashed. Most of the bugs that were causing the server to crash are fixed now.


Notice About Plugins
--------------------

We recently changed the way plugins work very significantly. That being the case, your old plugins won't work. 

New plugins should be independent Gems. Take a look at the included [example plugin](https://github.com/plamoni/SiriProxy/tree/master/plugins/siriproxy-example) for some inspiration. We will try to keep that file up to date with the latest features. 


Set-up Instructions
-------------------


**Tutorial for Ubuntu is now here**

Thanks to am3yrus we have a tutorial for ubuntu lovers: [http://am3yrus.over-blog.com/]

If you go for the above tutorial then there is no need to follow any instructions below. 



**Set up DNS**

Before you can use SiriProxy, you must set up a DNS server on your network to forward requests for guzzoni.apple.com to the computer running the proxy (make sure that computer is not using your DNS server!). I recommend dnsmasq for this purpose. It's easy to get running and can easily handle this sort of behavior. ([http://www.youtube.com/watch?v=a9gO4L0U59s](http://www.youtube.com/watch?v=a9gO4L0U59s))
Also if you dont have static ip you can use this dns python server. ([https://github.com/jimmykane/Roque-Dns-Server])


**Set up RVM and Ruby 1.9.3**

If you don't already have Ruby 1.9.3 installed through RVM, please do so in order to make sure you can follow the steps later. Experts can ignore this. If you're unsure, follow these directions carefully:

1. Download and install RVM (if you don't have it already):

	* Download/install RVM:  

		`bash < <(curl -s https://raw.github.com/wayneeseguin/rvm/master/binscripts/rvm-installer)`  

	* Activate RVM:  

		`[[ -s "$HOME/.rvm/scripts/rvm" ]] && . "$HOME/.rvm/scripts/rvm"`  

	* (optional, but useful) Add RVM to your .bash_profile:  

		`echo '[[ -s "$HOME/.rvm/scripts/rvm" ]] && . "$HOME/.rvm/scripts/rvm" # Load RVM function' >> ~/.bash_profile`   

2. Install Ruby 1.9.3 (if you don't have it already):   

	`rvm install 1.9.3`  

3. Set RVM to use/default to 1.9.3:   

	`rvm use 1.9.3 --default`


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

	`sudo gem install rake bundler`  

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

11. Start SiriProxy (must start as root because it uses a port < 1024):  

	`sudo siriproxy server`

        You can also start the server by a re-open script that ensures to restart the server if it crashes

        `./reopen.sh`

12. Test that the server is running by saying "Test Siri Proxy" to your phone.


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
	
	`siriproxy update -b gemify` 

Installs the latest code from the [gemify] branch on GitHub
	

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


**How do I remove the certificate from my iPhone when I'm done?**

Just go into your phone's Settings app, then go to "General->Profiles." Your CA will probably be the only thing listed under "Configuration Profiles." It will be listed as "SiriProxyCA" Just click it and click "Remove" and it will be removed. (Thanks to [@tidegu](http://www.twitter.com/tidegu) for asking!)



Licensing
---------

Re-use of my code is fine under a Creative Commons 3.0 [Non-commercial, Attribution, Share-Alike](http://creativecommons.org/licenses/by-nc-sa/3.0/) license. In short, this means that you can use my code, modify it, do anything you want. Just don't sell it and make sure to give me a shout-out. Also, you must license your derivatives under a compatible license (sorry, no closed-source derivatives). If you would like to purchase a more permissive license (for a closed-source and/or commercial license), please contact me directly. See the Creative Commons site for more information.

Acknowledgements
---------------

**Credits, greetings and big thanks to all the following.. RESPECT**

Mr. Nirodimos for helping with the iphone4s 

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
