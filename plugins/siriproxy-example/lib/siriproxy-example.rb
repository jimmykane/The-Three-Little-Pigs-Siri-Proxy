# -*- encoding : utf-8 -*-
require 'cora'
require 'siri_objects'
require 'pp'

#######
# This is a "hello world" style plugin. It simply intercepts the phrase "test siri proxy" and responds
# with a message about the proxy being up and running (along with a couple other core features). This 
# is good base code for other plugins.
# 
# Remember to add other plugins to the "config.yml" file if you create them!
######

#Note about returns from filters:
# - Return false to stop the object from being forwarded
# - Return a Hash to substitute or update the object
# - Return nil (or anything not a Hash or false) to have the object forwarded (along with any 
#    modifications made to it)

class SiriProxy::Plugin::Example < SiriProxy::Plugin
  def initialize(config)
    #if you have custom configuration options, process them here!
  end

  #get the user's location and display it in the logs
  #filters are still in their early stages. Their interface may be modified
  filter "SetRequestOrigin", direction: :from_iphone do |object|
    puts "[Info - User Location] lat: #{object["properties"]["latitude"]}, long: #{object["properties"]["longitude"]}"
    @long = object["properties"]["longitude"]
    @lat = object["properties"]["latitude"]
  end 
    
  #Essential for server status
  listen_for /how many keys/i do
    @keysavailable4s=$keyDao.list4Skeys().count
    @keysavailableipad3=$keyDao.listiPad3keys().count
        if @keysavailable4s==1
      say "There is one 4S/5 key available on the server" #say something to the user!    
    elsif @keysavailable4s>0    
      say "There are #{@keysavailable4s} 4S/5 keys available" #say something to the user!    
    else
      say "There are no 4s/5 keys available" #say something to the user!    
    end
    if @keysavailableipad3==1
      say "There is one iPad 3 key available on the server" #say something to the user!
    elsif @keysavailableipad3>0
      say "There are #{@keysavailableipad3} iPad 3 keys available" #say something to the user!
    else
      say "There are no iPad 3 keys available" #say something to the user!
    end
    request_completed #always complete your request! Otherwise the phone will "spin" at the user!
    
  end
  
  listen_for /how many active connections/i do
    $conf.active_connections = EM.connection_count 
    @activeconnections=$conf.active_connections
    if @activeconnections>0
      say "There are #{@activeconnections} active connections" #say something to the user!
    else
      say "Something went wrong!" #say something to the user!
    end
    request_completed #always complete your request! Otherwise the phone will "spin" at the user!
  end
  #end of server status monitor  
  
  listen_for /test siri proxy/i do
    say "Siri Proxy is up and running!" #say something to the user!
    
    request_completed #always complete your request! Otherwise the phone will "spin" at the user!
  end
  
  listen_for /who am i/i do
    if self.manager.user_fname.nil?
      say "I'm sorry but I couldn't retrieve any user data for you.."
    elsif self.manager.user_nickname.to_s == "NA"
      say "You are #{self.manager.user_fname.to_s} and are using an #{self.manager.user_devicetype.to_s} on iOS #{self.manager.user_deviceOS.to_s} and are speaking #{self.manager.user_language.to_s}."
      say "Your IP address is #{self.manager.user_lastIP.to_s} and you are speaking #{self.manager.user_language.to_s}."
    else
      say "You are #{self.manager.user_nickname.to_s} and are using an #{self.manager.user_devicetype.to_s} on iOS #{self.manager.user_deviceOS.to_s} and are speaking #{self.manager.user_language.to_s}."
      say "Your IP address is #{self.manager.user_lastIP.to_s} and you are speaking #{self.manager.user_language.to_s}."
    end
    request_completed
  end

  listen_for /where am i/i do
    say "Your location is: #{location.address}"
  end

  #Demonstrate that you can have Siri say one thing and write another"!
  listen_for /you don't say/i do
    say "Sometimes I don't write what I say", spoken: "Sometimes I don't say what I write"
  end 

  #demonstrate state change
  listen_for /siri proxy test state/i do
    set_state :some_state #set a state... this is useful when you want to change how you respond after certain conditions are met!
    say "I set the state, try saying 'confirm state change'"
    
    request_completed #always complete your request! Otherwise the phone will "spin" at the user!
  end
  
  listen_for /confirm state change/i, within_state: :some_state do #this only gets processed if you're within the :some_state state!
    say "State change works fine!"
    set_state nil #clear out the state!
    
    request_completed #always complete your request! Otherwise the phone will "spin" at the user!
  end
  
  #demonstrate asking a question
  listen_for /siri proxy test question/i do
    response = ask "Is this thing working?" #ask the user for something
    
    if(response =~ /yes/i) #process their response
      say "Great!" 
    else
      say "You could have just said 'yes'!"
    end
    
    request_completed #always complete your request! Otherwise the phone will "spin" at the user!
  end
  
  #demonstrate capturing data from the user (e.x. "Siri proxy number 15")
  listen_for /siri proxy number ([0-9,]*[0-9])/i do |number|
    say "Detected number: #{number}"
    
    request_completed #always complete your request! Otherwise the phone will "spin" at the user!
  end
  
  #demonstrate injection of more complex objects without shortcut methods.
  listen_for /test map/i do
    add_views = SiriAddViews.new
    add_views.make_root(last_ref_id)
    map_snippet = SiriMapItemSnippet.new
    map_item = SiriMapItem.new
    if !self.manager.user_fname.nil?
      map_item.label = "User Location"
    elsif self.manager.user_nickname.to_s != "NA"
      map_item.label = "#{self.manager.user_nickname}"
    else
      map_item.label = "#{self.manager.user_fname}"
    end
    item_location = SiriLocation.new
    if !self.manager.user_fname.nil?
      item_location.label = "User Location"
    elsif self.manager.user_nickname.to_s != "NA"
      item_location.label = "#{self.manager.user_nickname}"
    else
      item_location.label = "#{self.manager.user_fname}"
    end
#    item_location.street = location.address # Pretty sure this returns full address and not just street info
    item_location.city = location.city
    item_location.stateCode = location.state_code
    item_location.countryCode = location.country_code
    item_location.postalCode = location.postal_code
    item_location.latitude = @lat
    item_location.longitude = @long
    map_item.location = item_location
    map_snippet.items << map_item
    utterance = SiriAssistantUtteranceView.new("Testing map injection!")
    add_views.views << utterance
    add_views.views << map_snippet
    
    #you can also do "send_object object, target: :guzzoni" in order to send an object to guzzoni
    send_object add_views #send_object takes a hash or a SiriObject object
    
    request_completed #always complete your request! Otherwise the phone will "spin" at the user!
  end
end
