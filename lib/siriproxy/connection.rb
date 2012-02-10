require 'cfpropertylist'
require 'siriproxy/interpret_siri'
require 'pony'
require 'socket'


class SiriProxy::Connection < EventMachine::Connection
  include EventMachine::Protocols::LineText2

  attr_accessor :other_connection, :name, :ssled, :output_buffer, :input_buffer, :processed_headers, :unzip_stream, :zip_stream, :consumed_ace, :unzipped_input, :unzipped_output, :last_ref_id, :plugin_manager,:is_4S, :sessionValidationData, :speechId, :assistantId, :aceId, :speechId_avail, :assistantId_avail, :validationData_avail, :key, :clientip, :clientport,:client,:createassistant,:loadedassistant,:loadedspeechid,:devicetype
  def last_ref_id=(ref_id)
    @last_ref_id = ref_id
    self.other_connection.last_ref_id = ref_id if other_connection.last_ref_id != ref_id
  end
  
  def initialize
    super
    self.processed_headers = false
    self.output_buffer = ""
    self.input_buffer = ""
    self.unzipped_input = ""
    self.unzipped_output = ""
    self.unzip_stream = Zlib::Inflate.new
    self.zip_stream = Zlib::Deflate.new
    self.consumed_ace = false	
    self.is_4S = false 			#bool if its iPhone 4S
    self.sessionValidationData = nil	#validationData
    self.speechId = nil			#speechID
    self.assistantId = nil			#assistantID
    self.speechId_avail = false		#speechID available
    self.assistantId_avail = false		#assistantId available
    self.client=nil
    @createassistant=false
    @loadedassistant=nil
    @loadedspeechid=nil
    @devicetype=nil
    puts "[Info - SiriProxy] Created a connection!" 
    
    #self.pending_connect_timeout=5
    #puts pending_connect_timeout()    
    self.comm_inactivity_timeout=240 #very important and also depends on how many people connect!!!
    ##Checks For avalible keys before any object is loaded
    available_keys=$keyDao.listkeys().count
    if available_keys > 0
      self.validationData_avail = true      
    else 
      self.validationData_avail = false
    end     
  end
  
  #send email function
  def sendemail()
    #Lets also send an email comming soon
    if $APP_CONFIG.send_email=='ON' or $APP_CONFIG.send_email=='on'
      begin
        Pony.mail(
          :to => $APP_CONFIG.email_to, 
          :from => $APP_CONFIG.email_from,
          :subject => $APP_CONFIG.email_subject,
          :html_body => $APP_CONFIG.email_message
        )
        puts "[Email - SiriProxy] Expired key email sent to [#{$APP_CONFIG.email_to}]"
      rescue 
        puts "[Email - SiriProxy] Warning Cannot send mail. Check your ~/.siriproxy/config.yml"            
      end
    end        
    #Done with email
  end
  
  #Changes
  def checkHave4SData(object) 
    #changed the way 4s validation are saved. Now the get the values via the object etc.    
    @sessionValidationData = object["properties"]["validationData"].unpack('H*').join("") if object["properties"]["validationData"] !=nil
    @sessionValidationData = object["properties"]["sessionValidationData"].unpack('H*').join("") if object["properties"]["sessionValidationData"] !=nil
      
    if @sessionValidationData != nil #removed checking of assistantid etc
      #Writing keys to Database     
      key4s=Key4S.instance       
      key4s.sessionValidation=@sessionValidationData      
      #checking for 4s assistant and speechid      
      if object["properties"]["assistantId"] !=nil 
        key4s.assistantid=object["properties"]["assistantId"] 
      else
        key4s.assistantid="no assistant"
      end      
      if object["properties"]["speechId"] !=nil 
        key4s.speechid = object["properties"]["speechId"] 
      else
        key4s.speechid="no speech"      
      end    
      key4s.banned='False'
      key4s.expired='False'
      if $keyDao.check_duplicate(key4s)
        puts "[Info - SiriProxy] Duplicate Validation Data. Key NOT saved"
      else
        $keyDao.insert(key4s)
        puts "[Info - SiriProxy] Keys written to Database"        
      end
    else
      puts "[Info - SiriProxy] Something went wrong. Please file this bug. Key NOT saved!"
    end
    
  end  
  #this method validation data now is the one that defines the keyload. 
  #This way KeyLoad gets the meaning of request. Its updated via request of assistantload/create
  def get_validationData(object)  
    begin      
      if object["class"]=="CreateAssistant" # now separates initial request to Loadassistant and Create Assistant
        @createassistant=true 
        @key=Key.new     
        @available_keys=$keyDao.list_keys_for_new_assistant().count
        if (@available_keys) > 0
          puts "[Key - SiriProxy] Keys available for NEW Clients!!! [#{@available_keys}]"
          @key=$keyDao.next_available_for_new_assistant()
          puts "[Keys - SiriProy] Key [#{@key.id}] Loaded from Database for Validation Data" 
          puts "[Keys - SiriProy] Key [#{@key.id}] Loaded from Database for Validation Data For Object with aceid [#{object["aceId"]}] and class #{object["class"]}" if $LOG_LEVEL > 2
          @oldkeyload=@key.keyload          
          @key.keyload=@key.keyload+10  
          $keyDao.setkeyload(@key) 
          puts "[Key - SiriProxy] Key with id[#{@key.id}] increased it's keyload from [#{@oldkeyload}] to [#{@key.keyload}]" 
          self.sessionValidationData= @key.sessionValidation	
          self.validationData_avail = true       
          #hmmmmm
        else 
          puts "[Key - SiriProxy] No keys available in database Closing connections"
          
          self.validationData_avail = false
          self.close_connection() #close connections
          self.other_connection.close_connection() #close other
          
        end
      else 
        @createassistant=false
        #grab assistant
        if object["class"]=="LoadAssistant" and object["properties"]["assistantId"] !=nil
          @loadedassistant=object["properties"]["assistantId"]
          @loadedspeechid=object["properties"]["speechId"]
          puts @loadedassistant
          puts @loadedspeechid
        end
        @key=Key.new
        @available_keys=$keyDao.listkeys().count      
        if (@available_keys) > 0
          puts "[Key - SiriProxy] Keys available for Registered Only clients [#{@available_keys}]"
          @key=$keyDao.next_available() 
          puts "[Keys - SiriProy] Key [#{@key.id}] Loaded from Database for Validation Data" 
          puts "[Keys - SiriProy] Key [#{@key.id}] Loaded from Database for Validation Data For Object with aceid [#{object["aceId"]}] and class #{object["class"]}" if $LOG_LEVEL > 2
          @oldkeyload=@key.keyload          
          @key.keyload=@key.keyload+10  
          $keyDao.setkeyload(@key) 
          puts "[Key - SiriProxy] Key with id[#{@key.id}] increased it's keyload from [#{@oldkeyload}] to [#{@key.keyload}]" 
          self.sessionValidationData= @key.sessionValidation	
          self.validationData_avail = true       
          #hmmmmm
        else 
          puts "[Key - SiriProxy] No keys available in database Closing connections"
          self.validationData_avail = false
          self.close_connection() #close connections
          self.other_connection.close_connection() #close other
        end
      end
    rescue SystemCallError,NoMethodError
      puts "[ERROR - SiriProxy] Error opening the sessionValidationData  file. Connect an iPhone4S first or create them manually!"
    end
	end  
  
  
  def plist_blob(string)
    string = [string].pack('H*')
    #string = [string]
    string.blob = true
    string
	end
  def post_init
    self.ssled = false
  end

  def ssl_handshake_completed
    self.ssled = true        
  end
  
  def receive_line(line) #Process header    
    puts "[Header - #{self.name}] #{line}" if $LOG_LEVEL > 2
    if(line == "") #empty line indicates end of headers
      puts "[Debug - #{self.name}] Found end of headers" if $LOG_LEVEL > 3
      set_binary_mode
      self.processed_headers = true
      ##############
      #A Device has connected!!!
      #Check for User Agent and replace correctly
      
		elsif line.match(/^Host:/)
      line = "Host: guzzoni.apple.com"  #Keeps Apple from instantly knowing that
      #this is a Proxy Server.
		elsif line.match(/^User-Agent:/)   
      #if its and iphone4s
      self.clientport, self.clientip = Socket.unpack_sockaddr_in(get_peername) 
			if line.match(/iPhone4,1;/)
        puts "[RollEyes - Siri*-*Proxy]" 
        puts "[Info - SiriProxy] iPhone 4S connected from IP #{self.clientip}"        
        puts "[RollEyes - Siri*-*Proxy]" 
				self.is_4S = true
        @devicetype="iPhone4S"
      else # now seperates anything else exept 4s
        #we can close connections here .... and we can count them here       
        puts "[Info - Siriproxy] Curent connections [#{$conf.active_connections}]"
        #Some code in order connections to depend on the evailable keys
        #if no keys then maximize the connections in order to prevent max connection reach and 4s not be able to connect
        #
        @max_connections=$conf.max_connections
        @keysavailable=$keyDao.listkeys().count   
        
        if @keysavailable==0  #this is not needed anymore! will be removed
          @max_connections=700#max mem 
        elsif @keysavailable>0
          @max_connections=$conf.max_connections * @keysavailable
        end
        
        if $conf.active_connections>=@max_connections 
          self.close_connection() #close connections
          self.other_connection.close_connection() #close other          
          puts "[Warning - Siriproxy] Max Connections reached! Connections Closed...."
        end
        if  line.match(/iPhone3,1;/)
          #if its iphone4,etc	 			
          self.is_4S = false	
          @devicetype="GSM iPhone4"
          puts "[Info - SiriProxy] GSM iPhone 4 connected from IP #{self.clientip}"
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPhone3,1"] = "iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s] "
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2      
        elsif  line.match(/iPhone3,3;/)
          self.is_4S = false				
          @devicetype="CDMA iPhone4"
          puts "[Info - SiriProxy] CDMA iPhone 4 connected from IP #{self.clientip}"
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPhone3,3"] = "iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s] "
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
        elsif line.match(/iPad2,1;/)	
          self.is_4S = false				
          @devicetype="iPad2 Wifi Only"
          puts "[Info - SiriProxy] iPad2 Wifi Only connected from IP #{self.clientip}"						
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPad/iPad2,1"] = "iPhone/iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s] "
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2				
        elsif line.match(/iPad2,2;/)	
          self.is_4S = false				
          @devicetype="iPad2 GSM"
          puts "[Info - SiriProxy] iPad2 GSM connected from IP #{self.clientip}"						
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPad/iPad2,2"] = "iPhone/iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s] "
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2				
        elsif line.match(/iPad2,3;/)	
          self.is_4S = false				
          @devicetype="iPad2 CDMA"
          puts "[Info - SiriProxy] iPad2 CDMA connected from IP #{self.clientip}"						
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPad/iPad2,3"] = "iPhone/iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s] "
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2				
        elsif line.match(/iPad1,1;/)		
          self.is_4S = false		
          @devicetype="iPad 1st generation"
          puts "[Info - SiriProxy] iPad 1st generation connected from IP #{self.clientip}"						
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPad/iPad1,1"] = "iPhone/iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s] "
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2				
        elsif line.match(/iPod4,1;/)		
          self.is_4S = false	
          @devicetype="iPod touch 4th generation"
          puts "[Info - SiriProxy] iPod touch 4th generation connected from IP #{self.clientip}"					
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPod touch/iPod4,1"] = "iPhone/iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s] "
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
        else
          #Everithing else like android devices, computer apps etc        
          #Change unknown to iPhone to make sure everything works..
          puts "[Info - SiriProxy] Unknow Device Connected from IP #{self.clientip}"	
          self.is_4S = false
          @devicetype="Unknown Device"
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line = "User-Agent: Assistant(iPhone/iPhone4,1; iPhone OS/5.0.1/9A405) Ace/1.0"
          puts "[Info - SiriProxy] Changed header to iphone4s] "
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2				
        end
      end
    end    
    
    self.output_buffer << (line + "\x0d\x0a") #Restore the CR-LF to the end of the line
    
    flush_output_buffer()
  end

  def receive_binary_data(data)
    self.input_buffer << data
    
    ##Consume the "0xAACCEE02" data at the start of the stream if necessary (by forwarding it to the output buffer)
    if(self.consumed_ace == false)
      self.output_buffer << input_buffer[0..3]
      self.input_buffer = input_buffer[4..-1]
      self.consumed_ace = true;
    end
    
    process_compressed_data()
    
    flush_output_buffer()
  end
  
  def flush_output_buffer
    return if output_buffer.empty?
  
    if other_connection.ssled
      puts "[Debug - #{self.name}] Forwarding #{self.output_buffer.length} bytes of data to #{other_connection.name}" if $LOG_LEVEL > 5
      #puts  self.output_buffer.to_hex if $LOG_LEVEL > 5
      other_connection.send_data(output_buffer)
      self.output_buffer = ""
    else
      puts "[Debug - #{self.name}] Buffering some data for later (#{self.output_buffer.length} bytes buffered)" if $LOG_LEVEL > 5
      #puts  self.output_buffer.to_hex if $LOG_LEVEL > 5
    end
  end

  def process_compressed_data    
    begin
      self.unzipped_input << unzip_stream.inflate(self.input_buffer)
    rescue	
      puts "[Warning - SiriProxy] Curruped Data!!! Clearing buffer!"
      self.unzipped_input = ""
    end
    self.input_buffer = ""
    puts "========UNZIPPED DATA (from #{self.name} =========" if $LOG_LEVEL > 5
    puts unzipped_input.to_hex if $LOG_LEVEL > 5
    puts "==================================================" if $LOG_LEVEL > 5
    
    while(self.has_next_object?)
      object = read_next_object_from_unzipped()
      
      if(object != nil) #will be nil if the next object is a ping/pong
        new_object = prep_received_object(object) #give the world a chance to mess with folks
    
        inject_object_to_output_stream(new_object) if new_object != nil #might be nil if "the world" decides to rid us of the object
      end
    end
  end

  def has_next_object?
    return false if unzipped_input==nil or unzipped_input.empty?  #empty
    unpacked = unzipped_input[0...5].unpack('H*').first
    return true if(unpacked.match(/^0[34]/)) #Ping or pong
    begin
      if unpacked.match(/^[0-9][15-9]/)
        puts "ROGUE PACKET!!! WHAT IS IT?! TELL US!!! IN IRC!! COPY THE STUFF FROM BELOW"
        puts unpacked.to_hex
      end 
      objectLength = unpacked.match(/^0200(.{6})/)[1].to_i(16)
      return ((objectLength + 5) < unzipped_input.length) #determine if the length of the next object (plus its prefix) is less than the input buffer
    rescue 
      puts "[Bug - SiriProxy] Please contact me about this"
    end
  end

  def read_next_object_from_unzipped
   
    unpacked = unzipped_input[0...5].unpack('H*').first
    #the problem here is that the packet is now complete or something unknown for the match!
    #if first character is 0
    
    unpacked="0400000001" if !unpacked.match(/^0(.)(.{8})$/) # its the value that couses the bug! Will treat it as ping pong!!! Hope this resolves this
    #fingers crossed    
    info = unpacked.match(/^0(.)(.{8})$/) #some times this doesnt match! needs 10 chars !!!
   
    if unpacked==nil
      $stderr.puts "bug flash on unpacked"     
    end
    
    if info==nil
      $stderr.puts "bug flash on info"      #here lies the stupid bug!!!!!!!!!!!!!!!
      $stderr.puts unpacked
      
      #object=nil
      #return object
    end
    if info!=nil #lets hope for the magic fix
      if(info[1] == "3" || info[1] == "4"  ) #Ping or pong -- just get these out of the way (and log them for good measure)
        puts "Ping Pong #{unpacked}"
        object = unzipped_input[0...5]
      
        #debug
        if object==nil
          $stderr.puts "bug flash on object"         
        end
      
      
        self.unzipped_output << object
      
        type = (info[1] == "3") ? "Ping" : "Pong"      
        puts "[#{type} - #{self.name}] (#{info[2].to_i(16)})" if $LOG_LEVEL > 3
        self.unzipped_input = unzipped_input[5..-1]
      
        flush_unzipped_output()
        return nil
      end
    end
    
    object_size = info[2].to_i(16)
    prefix = unzipped_input[0...5]
    object_data = unzipped_input[5...object_size+5]
    self.unzipped_input = unzipped_input[object_size+5..-1]    
    parse_object(object_data)
    
   
    
  end
  
  
  def parse_object(object_data)
    plist = CFPropertyList::List.new(:data => object_data)    
    object = CFPropertyList.native_types(plist.value)
    
    object
  end
  
  def inject_object_to_output_stream(object)    
    if object["refId"] != nil && !object["refId"].empty?
      @block_rest_of_session = false if @block_rest_of_session && self.last_ref_id != object["refId"] #new session
      self.last_ref_id = object["refId"] 
    end
    
    puts "[Info - Forwarding object to #{self.other_connection.name}] #{object["class"]}" if $LOG_LEVEL > 1
    
    object_data = object.to_plist(:plist_format => CFPropertyList::List::FORMAT_BINARY)

    #Recalculate the size in case the object gets modified. If new size is 0, then remove the object from the stream entirely
    obj_len = object_data.length
    
    if(obj_len > 0)
      prefix = [(0x0200000000 + obj_len).to_s(16).rjust(10, '0')].pack('H*')
      self.unzipped_output << prefix + object_data
    end
    
    flush_unzipped_output()
  end
  
  def flush_unzipped_output
    self.zip_stream << self.unzipped_output
    self.unzipped_output = ""
    self.output_buffer << zip_stream.flush
    
    flush_output_buffer()
  end
  
  def prep_received_object(object)
    if object["refId"] == self.last_ref_id && @block_rest_of_session
      puts "[Info - Dropping Object from Guzzoni] #{object["class"]}" if $LOG_LEVEL > 1
      pp object if $LOG_LEVEL > 3
      return nil
    end    
    
    #Check if Validations has Expired
    if object["class"]=="SessionValidationFailed"  
      puts "[Warning - SiriProxy] The session Validation Expired!"
      puts  "[Warning - SiriProxy] Validation Data injected to first object witch had ace_id[#{object["refId"]}] and my ace is [#{object["aceId"]}]" if $LOG_LEVEL > 2               
      if self.other_connection.key!=nil #may happen if the other is a 4s
        $keyDao.validation_expired(self.other_connection.key)           
        puts "[Warning - SiriProxy] The key [#{self.other_connection.key.id}] and Keyload #{self.other_connection.key.keyload} Marked as Expired"             
        sendemail            
      end
    end
    
    #inject Validation- Grab Validation
    if object["properties"] != nil 
      #also maybe better use this insidde the object properties not nil
      #Check if the key cannot create any more assistants and set it as banned 
      #ADDED Option in config file for this
      if $APP_CONFIG.enable_auto_key_ban=='ON' or $APP_CONFIG.enable_auto_key_ban=='on'
        if object["class"]=="CommandFailed"
          puts "[Warning - SiriProxy] Command Failed refid #{object["refId"]} and Creating? #{self.other_connection.createassistant}"
        end
        if object["class"]=="CommandFailed" and self.other_connection.createassistant and self.other_connection.key!=nil #lets check if a key got banned!
          $keyDao.key_banned(self.other_connection.key)       
          puts "[Warning - SiriProxy] The key [#{self.other_connection.key.id}] Marked as Banned! Still serving with validation..." 
        end
      end
      
      #=begin      
      #Lets capture the unique ids for every appleid
      if object["class"]=="SetAssistantData" and self.validationData_avail==true #check this against validation 
        #this changes by language change also. Please consider re code
        pp object
        #work to be done here
        @client=Client.new        
        if object["properties"]["meCards"]!=nil
          if object["properties"]["meCards"][0]["properties"]["firstName"]!=nil 
            @client.fname=object["properties"]["meCards"][0]["properties"]["firstName"] 
          else
            @client.fname="NA"
          end
          if object["properties"]["meCards"][0]["properties"]["nickName"]!=nil 
            @client.nickname=object["properties"]["meCards"][0]["properties"]["nickName"]
          else
            @client.nickname="NA"
          end
          if object["properties"]["meCards"][0]["properties"]["identifier"]!=nil 
            @client.appleDBid=object["properties"]["meCards"][0]["properties"]["identifier"]
          else
            @client.appleDBid="NA"
          end
        else
          @client.fname="NA"
          @client.nickname="NA"
          @client.appleDBid="NA"
        end
        
        if object["properties"]["abSources"][0]["properties"]["accountIdentifier"]!=nil and object["properties"]["abSources"]!=nil and object["properties"]!=nil
          @client.appleAccountid=object["properties"]["abSources"][0]["properties"]["accountIdentifier"]
        else
          @client.appleAccountid="NA"
        end
        
        @client.valid="True"        
        #pp @client    
        
        #changing and connecting
        if  @client!=nil and @loadedassistant!=nil and @loadedassistant!=""#will not enter here if creating!
          #need to get in here if changing upon creation is needed
          puts "passed"
          pp object
          @oldclient=$clientsDao.check_duplicate(@client)
          pp @oldclient
          if @oldclient==nil        
           
            $clientsDao.insert(@client)
            puts "[Client - SiriProxy] NEW Client changed settings [#{@client.appleAccountid}] With Assistantid [#{@loadedassistant}]"              
                
          else
            
            @oldclient.fname=@client.fname
            @oldclient.nickname=@client.nickname #in case he changes this            
            $clientsDao.update(@oldclient)
            puts "[Client - SiriProxy] OLD Client changed settings [#{@oldclient.appleAccountid}] With Assistantid [#{@loadedassistant}]"              
            @client=@oldclient #hehe
          end
          
          @assistant=Assistant.new
          @assistant.assistantid=@loadedassistant
          @assistant.speechid=@loadedspeechid
          @assistant.client_apple_account_id=@client.appleAccountid
          @assistant.key_id=@key.id #suspicius
          @assistant.devicetype=@devicetype
          if  $assistantDao.check_duplicate(@assistant) #Should never  find a duplicate i think so
            puts "[Info - SiriProxy] Duplicate Assistand ID. Assistant NOT saved"
          else
            $assistantDao.createassistant(@assistant)
            puts "[Info - SiriProxy] Inserted Assistant ID #{@assistant.assistantid} for client #{@client}"  
          end    
          #hehe
        end
        
      end      
      #end of setting
      #=end
     
      #inject validation
      if object["properties"]["validationData"] !=nil #&& !object["properties"]["validationData"].empty?
        if self.is_4S
          puts "[Info - SiriProxy] Saving iPhone 4S validation Data"          
          checkHave4SData(object)
        else
          get_validationData(object)
          if self.validationData_avail
            puts "[Info - SiriProxy] using saved validationData"            
            object["properties"]["validationData"] = plist_blob(self.sessionValidationData)
          else
            puts "[Info - SiriProxy] no validationData available :("            
          end
        end
      end
      if object["properties"]["sessionValidationData"] !=nil #&& !object["properties"]["sessionValidationData"].empty? I was wrong both are needed
        if self.is_4S
          puts "[Info -  SiriProxy] using iPhone 4S validationData and saving it"         
          checkHave4SData(object)
        else
          get_validationData(object)
          if  self.validationData_avail
            puts "[Info - SiriProxy] using saved sessionvalidationData"
            object["properties"]["sessionValidationData"] = plist_blob(self.sessionValidationData)  
          else
            puts "[Info - SiriProxy] no validationData available :("           
          end
        end
      end
      if object["properties"]["speechId"] !=nil&&object["properties"]["assistantId"] !=nil
        #revomed saving speechid 
        if object["properties"]["speechId"].empty? || object["properties"]["assistantId"].empty? #warning this is not usual maybe a device got banned
          puts "[Warning - SiriProxy] This device is Not setup!"				
          self.speechId_avail=false #useless     
          self.assistantId_avail=false #useless
        else
          self.speechId_avail=true #useless
          self.assistantId_avail=true #useless
          if object["class"]=="LoadAssistant"
            puts "[Info - SiriProxy] Device has assistantId: #{object["properties"]["assistantId"]}"
            puts "[Info - SiriProxy] Device has speechID: #{object["properties"]["speechId"]}" 
          end                    
          #Lets record the assistants. 
          if  object["class"]=="AssistantCreated" and self.other_connection.key != nil   and self.other_connection.client!=nil and self.other_connection.createassistant==true
            puts "Creating new Assistant"
            @assistant=Assistant.new
            @assistant.assistantid=object["properties"]["assistantId"]
            @assistant.speechid=object["properties"]["speechId"]
            @assistant.key_id=self.other_connection.key.id               
            @assistant.devicetype=self.other_connection.devicetype
            pp self.other_connection.client
            
            if  $assistantDao.check_duplicate(@assistant) #Should never  find a duplicate i think so
              
              puts "[Info - SiriProxy] Duplicate Assistand ID. Assistant NOT saved"
              
            else
              
              #  $assistantDao.createassistant(@assistant)
              #puts "[Info - SiriProxy] Created Assistantid #{@assistant.assistantid} using key [#{self.other_connection.key.id}]"              
              @oldclient=$clientsDao.check_duplicate(self.other_connection.client)
              #pp @oldclient
              if @oldclient==nil
                
                # pp self.other_connection.client 
                $clientsDao.insert(self.other_connection.client)
                @assistant.client_apple_account_id=self.other_connection.client.appleAccountid
                $assistantDao.createassistant(@assistant)
                puts "[Client - SiriProxy] Created Assistant ID  #{@assistant.assistantid} using key [#{self.other_connection.key.id}]"              
                puts "[Client - SiriProxy] NEW Client [#{self.other_connection.client.appleAccountid}] created Assistantid [#{@assistant.assistantid}]"              
                
              else               
                $clientsDao.update(@oldclient)
                @assistant.client_apple_account_id=@oldclient.appleAccountid
                $assistantDao.createassistant(@assistant)
                puts "[Client - SiriProxy] Created Assistant ID #{@assistant.assistantid} using key [#{self.other_connection.key.id}]"              
                puts "[Client - SiriProxy] OLD Client [#{self.other_connection.client.appleAccountid}] created Assistantid [#{@assistant.assistantid}]"              
              end
            end
          end
          
        end
      end      

    end
    #end of injection        
    puts "[Info - #{self.name}] Received Object: #{object["class"]}" if $LOG_LEVEL == 1
    puts "[Info - #{self.name}] Received Object: #{object["class"]} (group: #{object["group"]})" if $LOG_LEVEL == 2
    puts "[Info - #{self.name}] Received Object: #{object["class"]} (group: #{object["group"]}, ref_id: #{object["refId"]},ace_id: #{object["aceId"]})" if $LOG_LEVEL > 2    
    puts "[Key -  #{self.name}] Recieved Object Using: Key id [#{@key.id}] and Instance Keyload[#{@key.keyload}]  " if @key!=nil &&self.validationData_avail!=false && $LOG_LEVEL >1 
    pp object if $LOG_LEVEL > 3
   
    
    #keeping this for filters
    new_obj = received_object(object)
    #puts self.name
    if self.validationData_avail==false and self.name=='iPhone' and self.is_4S==false 
      puts "[Protection - Siriproxy] Dropping Object from #{self.name}] #{object["class"]} due to no validation available" if $LOG_LEVEL >= 1      
      puts '[Protection - Siriproxy] Closing both connections...'
      self.close_connection()
      self.other_connection.close_connection()      
      puts '[Protection - Siriproxy] Closed both connections!!!'
      if object["class"]=="FinishSpeech" #will not get here
        #return object     
      end
      pp object if $LOG_LEVEL > 3
      return nil
    end    
    
    if new_obj == nil 
      puts "[Info - Dropping Object from #{self.name}] #{object["class"]}" if $LOG_LEVEL > 1
      pp object if $LOG_LEVEL > 3
      return nil
    end

    #block the rest of the session if a plugin claims ownership
    speech = SiriProxy::Interpret.speech_recognized(object)
    if speech != nil
      inject_object_to_output_stream(object)
      block_rest_of_session if plugin_manager.process(speech) 
      return nil
    end
    
    
    #object = new_obj if ((new_obj = SiriProxy::Interpret.unknown_intent(object, self, plugin_manager.method(:unknown_command))) != false)    
    #object = new_obj if ((new_obj = SiriProxy::Interpret.speech_recognized(object, self, plugin_manager.method(:speech_recognized))) != false)
    
    object
  end  
  
  #Stub -- override in subclass
  def received_object(object)
    
    object
  end 

end