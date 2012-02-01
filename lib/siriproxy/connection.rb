require 'cfpropertylist'
require 'siriproxy/interpret_siri'
require 'pony'

class SiriProxy::Connection < EventMachine::Connection
  include EventMachine::Protocols::LineText2
  
  attr_accessor :other_connection, :name, :ssled, :output_buffer, :input_buffer, :processed_headers, :unzip_stream, :zip_stream, :consumed_ace, :unzipped_input, :unzipped_output, :last_ref_id, :plugin_manager,:is_4S, :sessionValidationData, :speechId, :assistantId, :aceId, :speechId_avail, :assistantId_avail, :validationData_avail, :key
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
    puts "[Info - SiriProxy] Created a connection!" 		
    ##Checks For avalible keys before any object is loaded
    available_keys=$keyDao.listkeys().count
    if available_keys > 0
      self.validationData_avail = true      
    else 
      self.validationData_avail = false
    end     
  end
  def sendemail()
    #Lets also send an email comming soon
    if $APP_CONFIG.send_email=='ON' or $APP_CONFIG.send_email=='no'
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
      @key=Key.new
      @available_keys=$keyDao.listkeys().count      
      if (@available_keys) > 0
        puts "[Key - SiriProxy] Keys available [#{@available_keys}]"
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
        puts "[Key - SiriProxy] No keys available in database"
        self.validationData_avail = false
      end     
    rescue SystemCallError,NoMethodError
      puts "[ERROR - SiriProxy] Error opening the sessionValidationData  file. Connect an iPhone4S first or create them manually!"
    end
	end  
  #Revomed the getassistant and speechid 
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
    
    puts "[Info - #{self.name}] SSL completed for #{self.name}" if $LOG_LEVEL > 1
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
			if line.match(/iPhone4,1;/)
        puts "[RollEyes - Siri*-*Proxy]" 
				puts "[Info - SiriProxy] iPhone 4S connected"        
        puts "[RollEyes - Siri*-*Proxy]" 
				self.is_4S = true
			elsif  line.match(/iPhone3,1;/)
				#if its iphone4,etc	 			
				self.is_4S = false	
        puts "[Info - SiriProxy] GSM iPhone 4 connected"
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
				line["iPhone3,1"] = "iPhone4,1"
				puts "[Info - SiriProxy] Changed header to iphone4s] "
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
			elsif  line.match(/iPhone3,3;/)
				self.is_4S = false				
        puts "[Info - SiriProxy] CDMA iPhone 4 connected"
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
				line["iPhone3,3"] = "iPhone4,1"
				puts "[Info - SiriProxy] Changed header to iphone4s] "
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
      elsif line.match(/iPad2,1;/)	
				self.is_4S = false				
        puts "[Info - SiriProxy] iPad2 Wifi Only connected"						
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
				line["iPad/iPad2,1"] = "iPhone/iPhone4,1"
				puts "[Info - SiriProxy] Changed header to iphone4s] "
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2				
			elsif line.match(/iPad2,2;/)	
				self.is_4S = false				
        puts "[Info - SiriProxy] iPad2 GSM connected"						
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
				line["iPad/iPad2,2"] = "iPhone/iPhone4,1"
				puts "[Info - SiriProxy] Changed header to iphone4s] "
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2				
			elsif line.match(/iPad2,3;/)	
				self.is_4S = false				
        puts "[Info - SiriProxy] iPad2 CDMA connected"						
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
				line["iPad/iPad2,3"] = "iPhone/iPhone4,1"
				puts "[Info - SiriProxy] Changed header to iphone4s] "
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2				
			elsif line.match(/iPad1,1;/)		
				self.is_4S = false				
        puts "[Info - SiriProxy] iPad 1st generation connected"						
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
				line["iPad/iPad1,1"] = "iPhone/iPhone4,1"
				puts "[Info - SiriProxy] Changed header to iphone4s] "
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2				
      elsif line.match(/iPod4,1;/)		
				self.is_4S = false	
        puts "[Info - SiriProxy] iPod touch 4th generation connected"					
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
				line["iPod touch/iPod4,1"] = "iPhone/iPhone4,1"
				puts "[Info - SiriProxy] Changed header to iphone4s] "
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
			else
        #Everithing else like android devices, computer apps etc        
        #Change unknown to iPhone to make sure everything works..
        puts "[Info - SiriProxy] Unknow Device Connected"	
				self.is_4S = false
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
				line = "User-Agent: Assistant(iPhone/iPhone4,1; iPhone OS/5.0.1/9A405) Ace/1.0"
				puts "[Info - SiriProxy] Changed header to iphone4s] "
				puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2				
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
    return false if unzipped_input.empty? #empty
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
    info = unpacked.match(/^0(.)(.{8})$/)
    
    if(info[1] == "3" || info[1] == "4") #Ping or pong -- just get these out of the way (and log them for good measure)
      object = unzipped_input[0...5]
      self.unzipped_output << object
      
      type = (info[1] == "3") ? "Ping" : "Pong"      
      puts "[#{type} - #{self.name}] (#{info[2].to_i(16)})" if $LOG_LEVEL > 3
      self.unzipped_input = unzipped_input[5..-1]
      
      flush_unzipped_output()
      return nil
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
      puts "expired"      
      # I think I fixed it via self.otherconnetction and instance attributes
      #------------@thpryrchn ----------------
      #Please read this and help if you can. 
      #this is a big issue. Sometimes When the key changes and the validation expires then the new key is marked as expired
      #Somehow we must use instance keys @key or track the key that has been used for injection of validation data to the object ref id that,
      #has class SessionValidationFailed other_connection.last_ref_id
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
          if  object["class"]=="AssistantCreated" and self.other_connection.key != nil       
            @assistant=Assistant.new
            @assistant.assistantid=object["properties"]["assistantId"]
            @assistant.speechid=object["properties"]["speechId"]
            @assistant.key_id=self.other_connection.key.id
            if  $assistantDao.check_duplicate(@assistant) #Should never  find a duplicate i think so
              puts "[Info - SiriProxy] Duplicate Assistand ID. Assistant NOT saved"
            else
              $assistantDao.createassistant(@assistant)
              puts "[Info - SiriProxy] Created Assistantid #{object["properties"]["assistantId"]} using key [#{self.other_connection.key.id}]"              
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
      if object["class"]=="FinishSpeech" 
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