# -*- encoding : utf-8 -*-
#!/usr/bin/env ruby
require 'rubygems'
require 'eventmachine'
require 'zlib'
require 'cfpropertylist'
require 'pp'
require 'mysql'
require 'singleton'
require_relative 'tweakSiri'
require_relative 'interpretSiri'
require_relative 'db_classes.rb'
require_relative 'db_connection'
require_relative 'functions'

#LOG LEVEL
LOG_LEVEL = 0

class String
	def to_hex(seperator=" ")
		self.bytes.to_a.map{|i| i.to_s(16).rjust(2, '0')}.join(seperator)
	end
end


class SiriProxyConnection < EventMachine::Connection
	include EventMachine::Protocols::LineText2
	
	attr_accessor :otherConnection, :name, :ssled, :outputBuffer, :inputBuffer, :processedHeaders, :unzipStream, :zipStream, :consumedAce, :unzippedInput, :unzippedOutput, :lastRefId, :pluginManager, :is_4S, :sessionValidationData, :speechId, :assistantId, :aceId, :speechId_avail, :assistantId_avail, :validationData_avail,:key

	def lastRefId=(refId)
		@lastRefId = refId
		self.otherConnection.lastRefId = refId if self.otherConnection.lastRefId != refId
	end
	
	#######################
	#ReadSavedData
	def get_speechId
    begin
      #File.open("../keys/shared/speechId", "r") {|file| self.speechId = file.read}				
      @@key.availablekeys=$keyDao.listkeys().count  
      if @@key.availablekeys > 0
        self.speechId=@@key.speechid		
        self.speechId_avail = true
        puts "[Keys - SiriProy] Key Loaded from Database for SpeechId "
      else
        self.speechId_avail = true
      end
    rescue SystemCallError,NoMethodError
      puts "[ERROR - SiriProy] Error opening the speechId file. Connect an iPhone4S first or create them manually!"
    end
	end

	def get_assistantId
    begin
      #File.open("../keys/shared/assistantId", "r") {|file| self.assistantId = file.read}
      #puts self.keylist[0].assistantid
      @@key.availablekeys=$keyDao.listkeys().count  
      if @@key.availablekeys > 0
        self.assistantId=@@key.assistantid				
        self.assistantId_avail = true
        puts "[Keys - SiriProy] Key Loaded from Database for AssistantId "
      else
        self.assistantId_avail = false
      end
    rescue SystemCallError,NoMethodError
      puts "[ERROR - SiriProxy] Error opening the assistantId file. Connect an iPhone4S first or create them manually!"
    end
	end

	def get_validationData
    begin
      #File.open("../keys/shared/sessionValidationData", "rb") {|file| self.sessionValidationData = file.read}
      #puts self.keylist[0].assistantid		
      @@key.availablekeys=$keyDao.listkeys().count     
      if @@key.availablekeys > 0
        self.sessionValidationData= @@key.sessionValidation	
        self.validationData_avail = true
      
        puts "[Keys - SiriProy] Key Loaded from Database for Validation Data"
      else 
        self.validationData_avail = false
      end
    
      
    rescue SystemCallError,NoMethodError
      puts "[ERROR - SiriProxy] Error opening the sessionValidationData  file. Connect an iPhone4S first or create them manually!"
    end
	end  

	def initialize
		super
		self.processedHeaders = false
		self.outputBuffer = ""
		self.inputBuffer = ""
		self.unzippedInput = ""
		self.unzippedOutput = ""
		self.unzipStream = Zlib::Inflate.new
		self.zipStream = Zlib::Deflate.new
		self.consumedAce = false
		self.is_4S = false 			#bool if its iPhone 4S
		self.sessionValidationData = nil	#validationData
		self.speechId = nil			#speechID
		self.assistantId = nil			#assistantID
		self.speechId_avail = false		#speechID available
		self.assistantId_avail = false		#assistantId available
		self.validationData_avail = false	#validationData available		
		puts "[Info - SiriProxy] Got a inbound Connection!" 		
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
		
		puts "[Info - #{self.name}] SSL completed for #{self.name}" if LOG_LEVEL > 1
	end
	
	def receive_line(line) #Process header
		puts "[Header - #{self.name}] #{line}" if LOG_LEVEL > 2
		
		if(line == "") #empty line indicates end of headers
			puts "[Debug - #{self.name}] Found end of headers" if LOG_LEVEL > 3
			self.set_binary_mode
			self.processedHeaders = true
      ##############
      #A Device has connected!!!
      #Check for User Agent and replace correctly
      
		elsif line.match(/^User-Agent:/)   
      #if its and iphone4s
			if line.match(/iPhone4,1;/)
				puts "[Info - SiriProxy] iPhone 4S connected"        
				self.is_4S = true
			elsif  line.match(/iPhone3,1;/)
				#if its iphone4,etc					
        @@key=Key.instance
        @@key.availablekeys=$keyDao.listkeys().count      
        if (@@key.availablekeys)>0          
          @@key=$keyDao.next_available()
          @@key.availablekeys=$keyDao.listkeys().count  
          @@oldkeyload=@@key.keyload
          @@key.keyload=@@key.keyload+10      
          $keyDao.setkeyload(@@key)
          puts "[Key - SiriProxy] Next Key with id=[#{@@key.id}] and increasing keyload from [#{@@oldkeyload}] to [#{@@key.keyload}]"
          puts "[Key - SiriProxy] Keys available [#{@@key.availablekeys}]"
        else
          puts "[Key - SiriProxy] No keys available in database"
        end
     
				if @@key==nil
          puts "[Key - SiriProxy] - No Key Iniialized"
        else 
          puts "[Info - SiriProxy] - iPhone 4th generation connected. Using saved keys"
				end				
				self.is_4S = false				
				line["iPhone3,1"] = "iPhone4,1"
				puts "[Info - changed header to iphone4s] " + line
			elsif line.match(/iPad1,1;/)				
				#older Devices Supported				
        @@key=Key.instance
        @@key.availablekeys=$keyDao.listkeys().count      
        if (@@key.availablekeys)>0
          @@key=$keyDao.next_available()
          @@key.availablekeys=$keyDao.listkeys().count  
          @@oldkeyload=@@key.keyload
          @@key.keyload=@@key.keyload+10  
          $keyDao.setkeyload(@@key)
          puts "[Key - SiriProxy] Next Key with id=[#{@@key.id}] and increasing keyload from [#{@@oldkeyload}] to [#{@@key.keyload}]"
          puts "[Key - SiriProxy] Keys available [#{@@key.availablekeys}]"
        else
          puts "[Key - SiriProxy] No keys available in database"
        end
				if @@key==nil
					puts "[Key - SiriProxy] - No Key Available right now ;("
        else 
          puts "[Info - SiriProxy] - iPad 1  generation connected. Using saved keys"						
				end				
				self.is_4S = false				
				line["iPad/iPad1,1"] = "iPhone/iPhone4,1"
				puts "[Info - changed header to iphone4s] " + line
      elsif line.match(/iPod4,1;/)				
				#older Devices Supported				
        @@key=Key.instance
        @@key.availablekeys=$keyDao.listkeys().count      
        if (@@key.availablekeys)>0
          @@key=$keyDao.next_available()
          @@key.availablekeys=$keyDao.listkeys().count  
          @@oldkeyload=@@key.keyload
          @@key.keyload=@@key.keyload+10  
          $keyDao.setkeyload(@@key)
          puts "[Key - SiriProxy] Next Key with id=[#{@@key.id}] and increasing keyload from [#{@@oldkeyload}] to [#{@@key.keyload}]"
          puts "[Key - SiriProxy] Keys available [#{@@key.availablekeys}]"
        else
          puts "[Key - SiriProxy] No keys available in database"
        end
				if @@key==nil
					puts "[Key - SiriProxy] - No Key Available right now ;("
        else 
          puts "[Info - SiriProxy] - iPod touch 4th generation connected. Using saved keys"						
				end				
				self.is_4S = false				
				line["iPod touch/iPod4,1"] = "iPhone/iPhone4,1"
				puts "[Info - changed header to iphone4s] " + line
			else
        #Everithing else like android devices, computer apps etc
        @@key=Key.instance
        @@key.availablekeys=$keyDao.listkeys().count      
        if (@@key.availablekeys)>0
          @@key=$keyDao.next_available()
          @@key.availablekeys=$keyDao.listkeys().count  
          @@oldkeyload=@@key.keyload
          @@key.keyload=@@key.keyload+10  
          $keyDao.setkeyload(@@key)
          puts "[Key - SiriProxy] Next Key with id=[#{@@key.id}] and increasing keyload from [#{@@oldkeyload}] to [#{@@key.keyload}]"
          puts "[Key - SiriProxy] Keys available [#{@@key.availablekeys}]"
        else
          puts "[Key - SiriProxy] No keys available in database"
        end
				if @@key==nil          
					puts "[Key - SiriProxy] - No Key Available right now ;("
        else 
          puts "[Info - SiriProxy] - Unknow Device Connected. Using saved keys"				
				end
        #do not change header for uknown device due to error not predicting header
				puts "[Info - SiriProxy] - Unknow Device Connected. Using saved keys"+line
				self.is_4S = false
			end
		end
		
		self.outputBuffer << (line + "\x0d\x0a") #Restore the CR-LF to the end of the line
		flush_output_buffer()
    
	end

	def receive_binary_data(data)
		self.inputBuffer << data		
		#Consume the "0xAACCEE02" data at the start of the stream if necessary (by forwarding it to the output buffer)
		if(self.consumedAce == false)
			self.outputBuffer << self.inputBuffer[0..3]
			self.inputBuffer = self.inputBuffer[4..-1]
			self.consumedAce = true;
		end
		
		process_compressed_data()
		flush_output_buffer()
    
	end
	
	def flush_output_buffer
		return if self.outputBuffer.empty?
	
		if(self.otherConnection.ssled)
			puts "[Debug - #{self.name}] Forwarding #{self.outputBuffer.length} bytes of data to #{self.otherConnection.name}" if LOG_LEVEL > 5
			#puts  self.outputBuffer.to_hex if LOG_LEVEL > 5
			self.otherConnection.send_data(self.outputBuffer)
			self.outputBuffer = ""
		else
			puts "[Debug - #{self.name}] Buffering some data for later (#{self.outputBuffer.length} bytes buffered)" if LOG_LEVEL > 5
			#puts  self.outputBuffer.to_hex if LOG_LEVEL > 5
		end
	end

  def checkHave4SData
    if self.speechId != nil and self.assistantId != nil and self.sessionValidationData != nil
      #Writing keys to Database
      key4s=Key4S.instance
      key4s.assistantid=self.assistantId
      key4s.speechid=self.speechId
      key4s.sessionValidation=self.sessionValidationData
      key4s.expired='False'
      if $keyDao.check_duplicate(key4s)
        puts "[Info - SiriProxy] Duplicate Validation Data. Key NOT saved"
      else
        $keyDao.insert(key4s)
        puts "[Info - SiriProxy] Keys written to Database"
        
      end
    end
  end

	def process_compressed_data		
		self.unzippedInput << self.unzipStream.inflate(self.inputBuffer)
		self.inputBuffer = ""
		puts "========UNZIPPED DATA (from #{self.name} =========" if LOG_LEVEL > 5
		puts self.unzippedInput.to_hex if LOG_LEVEL > 5
		puts "==================================================" if LOG_LEVEL > 5
		
		while(self.has_next_object?)
			object = read_next_object_from_unzipped()
			
			if(object != nil) #will be nil if the next object is a ping/pong
				new_object = prep_received_object(object) #give the world a chance to mess with folks
				inject_object_to_output_stream(new_object) if new_object != nil #might be nil if "the world" decides to rid us of the object
			end
		end
	end

	def has_next_object?
		return false if self.unzippedInput.empty? #empty
		unpacked = self.unzippedInput[0...5].unpack('H*').first
		return true if(unpacked.match(/^0[34]/)) #Ping or pong
		
		if unpacked.match(/^[0-9][15-9]/)
      puts "ROGUE PACKET!!! WHAT IS IT?! TELL US!!! IN IRC!! COPY THE STUFF FROM BELOW"
      puts unpacked.to_hex
    end 
    
		objectLength = unpacked.match(/^0200(.{6})/)[1].to_i(16)
		return ((objectLength + 5) < self.unzippedInput.length) #determine if the length of the next object (plus its prefix) is less than the input buffer
	end

	def read_next_object_from_unzipped
		unpacked = self.unzippedInput[0...5].unpack('H*').first
    #if info!=nil Watch another bug here
		info = unpacked.match(/^0(.)(.{8})$/)
		
		if(info[1] == "3" || info[1] == "4") #Ping or pong -- just get these out of the way (and log them for good measure)
			object = self.unzippedInput[0...5]
			self.unzippedOutput << object
			
			type = (info[1] == "3") ? "Ping" : "Pong"			
			puts "[#{type} - #{self.name}] (#{info[2].to_i(16)})" if LOG_LEVEL > 3
			self.unzippedInput = self.unzippedInput[5..-1]
			
			flush_unzipped_output()
			return nil
		end
	
		object_size = info[2].to_i(16)
		prefix = self.unzippedInput[0...5]
		object_data = self.unzippedInput[5...object_size+5]
		self.unzippedInput = self.unzippedInput[object_size+5..-1]
		parse_object(object_data)
	end	
	
	def parse_object(object_data)
		plist = CFPropertyList::List.new(:data => object_data)		
		object = CFPropertyList.native_types(plist.value)		
		object
	end
	
	def inject_object_to_output_stream(object)
		self.lastRefId = object["refId"] if object["refId"] != nil && !object["refId"].empty?
		object_data = object.to_plist(:plist_format => CFPropertyList::List::FORMAT_BINARY)
		#Recalculate the size in case the object gets modified. If new size is 0, then remove the object from the stream entirely
		obj_len = object_data.length
		
		if(obj_len > 0)
			prefix = [(0x0200000000 + obj_len).to_s(16).rjust(10, '0')].pack('H*')
			self.unzippedOutput << prefix + object_data
		end		
		flush_unzipped_output()
	end
	
	def flush_unzipped_output
		self.zipStream << self.unzippedOutput
		self.unzippedOutput = ""
		self.outputBuffer << self.zipStream.flush		
		flush_output_buffer()
	end
	##################
	#prepare the recieved object with our data
	def prep_received_object(object)		
    if object["class"]=="SessionValidationFailed"
      get_validationData	      
      if self.validationData_avail
        puts "[Warning - SiriProxy] The session Validation Expired"          
        $keyDao.validation_expired(@@key)          
        puts "[Warning - SiriProxy] The key Marked as Expired"       
        @@key.availablekeys=$keyDao.listkeys().count  
        puts @@key.availablekeys
        if @@key.availablekeys >= 1          
          @@key=$keyDao.next_available()            
          puts "[Key - SiriProxy] Changed Key" 
        elsif @@key.availablekeys <1          
          puts "[Keys - SiriProxy] Available Keys in Database: [#{@@key.availablekeys}]"
          puts "[Key - No keys found in Database Available :(] " 									
        end        
      else 
        puts "[Key - No Validation Data AND No Key Available :(] " 									
      end 
    end
		if object["properties"] != nil

			if object["properties"]["validationData"] !=nil #&& !object["properties"]["validationData"].empty?
				if self.is_4S
          puts "[Info - SiriProxy] using iPhone 4S validationData and saving it"
					self.sessionValidationData = object["properties"]["validationData"].unpack('H*').join("")
					checkHave4SData
        else
          get_validationData
          if self.validationData_avail
            puts "[Info - SiriProxy] using saved validationData"
            object["properties"]["validationData"] = plist_blob(self.sessionValidationData)
          else
            puts "[Info - SiriProxy] no validationData available :("
          end
				end
			end
			if object["properties"]["sessionValidationData"] !=nil #&& !object["properties"]["sessionValidationData"].empty?
				if self.is_4S
          puts "[Info -  SiriProxy] using iPhone 4S validationData and saving it"
          self.sessionValidationData = object["properties"]["sessionValidationData"].unpack('H*').join("")
          checkHave4SData
        else
          get_validationData
          if  self.validationData_avail
            puts "[Info - SiriProxy] using saved validationData"
            object["properties"]["sessionValidationData"] = plist_blob(self.sessionValidationData)
          else
            puts "[Info - SiriProxy] no validationData available :("
          end
        end
			end
			if object["properties"]["speechId"] !=nil #&& !object["properties"]["speechId"].empty?
				if self.is_4S
					puts "[Info - SiriProxy] using iPhone 4S speechID and saving it"
          self.speechId = object["properties"]["speechId"]
          checkHave4SData
				else
					if object["properties"]["speechId"].empty?
						get_speechId
						if speechId_avail
							puts "[Info - SiriProxy] using saved speechID:  #{self.speechId}"
              object["properties"]["speechId"] = self.speechId
            else
              puts "[Info - SiriProxy] no speechId available :("
            end
          else
            puts "[Info - SiriProxy] using speechID sent by iPhone: #{object["properties"]["speechId"]}"
          end
        end
			end
			if object["properties"]["assistantId"] !=nil #&& !object["properties"]["assistantId"].empty?
				if self.is_4S
					puts "[Info - SiriProxy] using iPhone 4S  assistantId and saving it"
					self.assistantId = object["properties"]["assistantId"]
					checkHave4SData
        else
          if object["properties"]["assistantId"].empty?
            get_assistantId
            if assistantId_avail
              puts "[Info - SiriProxy] using saved assistantID - #{self.assistantId}"
              object["properties"]["assistantId"] = self.assistantId
            else
              puts "[Info - SiriProxy] no assistantId available :("
            end
          else
            puts "[Info - SiriProxy] using assistantID sent by iPhone: #{object["properties"]["assistantId"]}"
          end
				end
			end    
     
		end
    
    
    #Logging to terminal procedure default is 1
		puts "[Info - #{self.name}] Object: #{object["class"]}" if LOG_LEVEL == 1
		puts "[Info - #{self.name}] Object: #{object["class"]} (group: #{object["group"]})" if LOG_LEVEL == 2
		puts "[Info - #{self.name}] Object: #{object["class"]} (group: #{object["group"]}, refId: #{object["refId"]}, aceId: #{object["aceId"]})" if LOG_LEVEL > 2
		pp object if LOG_LEVEL > 3
	
		object = received_object(object)		
		new_obj = object
		object = new_obj if ((new_obj = Interpret.unknown_intent(object, self, self.pluginManager.method(:unknown_command))) != false)		
		object = new_obj if ((new_obj = Interpret.speech_recognized(object, self, self.pluginManager.method(:speech_recognized))) != false)		
		object
         
	end	
	
	#Stub -- override in subclass
	def received_object(object)	
		object
	end 

end

#####
# This is the connection to the iPhone
#####
class SiriIPhoneConnection < SiriProxyConnection
	def initialize
		super
		self.name = "iPhone"
	end

	def post_init
		super
		start_tls(:cert_chain_file => "server.passless.crt",
      :private_key_file => "server.passless.key",
      :verify_peer => false)
	end

	def ssl_handshake_completed
		super
		self.otherConnection = EventMachine.connect('guzzoni.apple.com', 443, SiriGuzzoniConnection)
		self.otherConnection.otherConnection = self #hehe
		self.otherConnection.pluginManager = self.pluginManager
	end
	
	def received_object(object)
		self.pluginManager.object_from_client(object, self)
	end
end

#####
# This is the connection to the Guzzoni (the Siri server backend)
#####
class SiriGuzzoniConnection < SiriProxyConnection
	def initialize
		super
		self.name = "Guzzoni"
	end

	def connection_completed
		super
		start_tls(:verify_peer => false)
	end
	
	def received_object(object)		
		self.pluginManager.object_from_guzzoni(object, self)
	end
end

class SiriProxy
	def initialize(pluginClasses=[])
		puts "Initializing Proxy..."
    
    #Initialization of event machine variables overider +epoll mode on by default    		
		EM.epoll
    
    #Database connection
    $my_db=db_connect()    
    
    #initialize config
    $conf=ConfigProxy.instance
    $confDao=ConfigDao.instance
    $confDao.connect_to_db($my_db)       
    $conf=$confDao.getsettings
    $conf.active_connections=0 
    $confDao.update($conf)
    #end of config
    EM.threadpool_size=$conf.max_threads
    
    #initialize key controller
    @@key=Key.instance
    @@key.keyload=0
		$keyDao=KeyDao.instance#instansize Dao object controller
		$keyDao.connect_to_db($my_db)		
    
    if ($keyDao.listkeys().count)>0      
      @@key.availablekeys=$keyDao.listkeys().count      
      puts "[Keys - SiriProxy] Available Keys in Database: [#{@@key.availablekeys}]"
    else
      puts "[Keys - SiriProxy] Warning starting Server with no key in Database!!! Key count= 0"
      @@key.availablekeys=0
    end
    
		EventMachine.run do
			EventMachine::start_server('0.0.0.0',443, SiriIPhoneConnection) { |conn|
				conn.pluginManager = SiriPluginManager.new(
					pluginClasses
				)
			}
      puts "Server is Up and Running"
      EventMachine::PeriodicTimer.new(10){
        $conf.active_connections = EM.connection_count          
        $confDao.update($conf)
        puts "[Info - SiriProxy] Active connections [#{$conf.active_connections}] Max connections [#{$conf.max_connections}]"
        if $conf.active_connections>=$conf.max_connections 
          EventMachine.stop
        end
      }
      EventMachine::PeriodicTimer.new($conf.keyload_dropdown_interval){
        @@overloaded_keys_count=$keyDao.findoverloaded().count
        if (@@overloaded_keys_count>0)
          @@overloaded_keys=$keyDao.findoverloaded()     
          for i in 0..(@@overloaded_keys_count-1)            
            @@oldkeyload=@@overloaded_keys[i].keyload   
            @@overloaded_keys[i].keyload=@@overloaded_keys[i].keyload-$conf.keyload_dropdown
            $keyDao.setkeyload(@@overloaded_keys[i])
            puts "[Keys - SiriProxy] Found overloaded Key with id=[#{@@overloaded_keys[i].id}] and Decreasing keyload from [#{@@oldkeyload}] to [#{@@overloaded_keys[i].keyload}]"
          end
        end
      }
    end         
	end
       
end

Interpret = InterpretSiri.new
