# -*- encoding : utf-8 -*-
#!/usr/bin/env ruby
require 'mysql'
require_relative 'db_connection'
if dbh=db_connect()
  puts "DATABASE FOUND"
else 
  puts "Could not connect to database"
end

dbh.query("DROP TABLE IF EXISTS `keys`;")
puts "Table keys Droped"

dbh.query("CREATE TABLE `keys` (
  `id` int(100) unsigned NOT NULL AUTO_INCREMENT,
  `assistantid` longtext NOT NULL,
  `speechid` longtext NOT NULL,
  `sessionValidation` longtext NOT NULL,
  `expired` enum('False','True') NOT NULL DEFAULT 'False',
  `keyload` int(255) unsigned NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;")  
puts "Created Table keys"


dbh.query("DROP TABLE IF EXISTS `config`;")
puts "Table config Droped"


dbh.query("CREATE TABLE `config` (
  `id` int(2) NOT NULL,
  `max_threads` int(5) unsigned NOT NULL DEFAULT '20',
  `max_connections` int(5) unsigned NOT NULL DEFAULT '100',
  `active_connections` int(5) unsigned NOT NULL DEFAULT '0',
  `max_keyload` int(5) unsigned NOT NULL DEFAULT '1000',
  `keyload_dropdown` int(5) unsigned NOT NULL,
  `keyload_dropdown_interval` int(5) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;")
puts "Created Table config"

dbh.query("INSERT INTO `config` VALUES ('1', '20', '50', '7', '500', '50', '900');")
puts "Added Default setting in Table config"
   