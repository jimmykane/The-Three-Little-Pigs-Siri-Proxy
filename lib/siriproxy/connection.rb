require 'cfpropertylist'
require 'siriproxy/interpret_siri'
require 'socket'
require "siriproxy/functions"
require 'cora'
class SiriProxy::Connection < EventMachine::Connection
  include EventMachine::Protocols::LineText2
  
  attr_accessor :other_connection, :name, :ssled, :output_buffer, :input_buffer, :processed_headers, :unzip_stream, :zip_stream, :consumed_ace, :unzipped_input, :unzipped_output, :last_ref_id, :plugin_manager, :is_4S, :is_iPad3, :sessionValidationData, :speechId, :assistantId, :aceId, :speechId_avail, :assistantId_avail, :validationData_avail, :key, :clientip, :clientport, :client, :oldclient, :createassistant, :loadedassistant, :loadedspeechid, :devicetype, :deviceOS, :activation_token_recieved, :activation_token, :assistant_found, :connectionfromapple, :commandFailed, :finishspeech, :GetSessionCertificateResponse, :iOS, :host, :usedkey

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
    self.is_iPad3 = false		#bool if its iPad3
    self.sessionValidationData = nil	#validationData
    self.speechId = nil			#speechID
    self.assistantId = nil			#assistantID
    self.speechId_avail = false		#speechID available
    self.assistantId_avail = false		#assistantId available
    self.client=nil
    self.oldclient=nil
    self.iOS=nil
    self.host=nil
    self.usedkey=nil
    @createassistant=false
    @loadedassistant=nil
    @loadedspeechid=nil
    @devicetype=nil
    @deviceOS=nil
    @activation_token_recieved=false
    @assistant_found=false
    @connectionfromapple=false
    @commandFailed=false
    @finishspeech=false
    @GetSessionCertificateResponse=false #send only by apple or server
    puts "[Info - SiriProxy] Created a connection!"

    #self.pending_connect_timeout=5
    #puts pending_connect_timeout()
    self.comm_inactivity_timeout=240 #very important and also depends on how many people connect!!!
    ##Checks For avalible keys before any object is loaded
    if $APP_CONFIG.try_iPad3==true
      available_keys=($keyDao.list4Skeys().count + $keyDao.listiPad3keys().count + $keyDao.listiPad3Dictationkeys().count)
    else
      available_keys=($keyDao.list4Skeys().count + $keyDao.listiPad3keys().count)
    end
    if available_keys > 0
      self.validationData_avail = true
    else
      self.validationData_avail = false
    end
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
      key4s.iPad3='False'
      key4s.client_apple_account_id="This is not working."
      self.validationData_avail = true
      if $keyDao.check_duplicate(key4s)
        puts "[Info - SiriProxy] Duplicate Validation Data. Key NOT saved"
      else
        $keyDao.insert(key4s)
        puts "[Info - SiriProxy] Keys written to Database"
        #also unban all keys available.
        if $APP_CONFIG.private_server.to_s.upcase == "ON"
          $keyDao.unban_keys #unBan because a key was inserted! should spoof enough
          puts "[Info - SiriProxy] New 4S Key added and keys set to unbanned"
        end
      end
    else
      puts "[Info - SiriProxy] Something went wrong. Please file this bug. Key NOT saved!"
    end
    ##This now allows 4S phones to be entered into client database.
    begin
      @key=Key.new
      @key.id=0

      if object["class"]=="CreateAssistant" # now separates initial request to LoadAssistant and Create Assistant
        @createassistant=true
        @assistant_found=false

      elsif object["class"]=="LoadAssistant" and object["properties"]["assistantId"] !=nil and object["properties"]["speechId"] !=nil

        @createassistant=false
        @assistant_found=true
        #grab assistant
        if object["properties"]["activationToken"] != nil
          @usedkey = $keyDao.list_keys_for_stats()
          puts '[Info - SiriProxy] Recieved token'
          @activation_token_recieved=true
          @activation_token=ActivationToken.new
          @activation_token.aceid=object["aceId"]
          @activation_token.data=object["properties"]["activationToken"]
          pp @activation_token if $LOG_LEVEL > 2
          @keystats=$keystatisticsDao.get_key_stats(@usedkey)
          if @keystats==nil
            $keystatisticsDao.insert(@usedkey)
            @keystats=$keystatisticsDao.get_key_stats(@usedkey)
          end
          @keystats.total_tokens_recieved+=1
          $keystatisticsDao.save_key_stats(@keystats)
          pp @keystats
        end
        @loadedassistant=object["properties"]["assistantId"]
        @loadedspeechid=object["properties"]["speechId"]
        @userassistant=Assistant.new
        @userassistant.assistantid=@loadedassistant
        @userassistant.speechid=@loadedspeechid
        @userassistant.last_ip=@clientip
        @userassistant=$assistantDao.check_duplicate(@userassistant)  #check if there is a registerd assistant

        if  @userassistant!=nil #If there is one then

          puts "[Authentification - SiriProxy] Registered Assistant Found"
          @user=$clientsDao.find_by_assistant(@userassistant) #find the user with that assistant
          if @user==nil #Incase this user doesnt exist!!!!!!! Bug or not complete transaction
            puts "[Authentification - SiriProxy] No client for Assistant [#{@loadedassistant}]  Found :-("
          elsif @user.valid=='False' # Shouldn't ever be invalid on a 4S or iPad3
            @user.valid='True'
          elsif @user.valid=='True' #if its valid!!!
            $assistantDao.updateassistant(@userassistant)
            puts "[Authentification - SiriProxy] Access Granted! -> Client name:[#{@user.fname}] nickname[#{@user.nickname}] appleid[#{@user.appleAccountid}] Connected "
            plugin_manager.user_assistant = @loadedassistant
            plugin_manager.user_appleid = @user.appleAccountid
            plugin_manager.user_fname = @user.fname
            plugin_manager.user_nickname = @user.nickname
            plugin_manager.user_language = object["language"]
            plugin_manager.user_devicetype = @user.devicetype
            plugin_manager.user_deviceOS = @user.deviceOS
            plugin_manager.user_lastIP = @user.last_ip
            plugin_manager.user_last_login = @user.last_login
          end
        end


      end

      #rescue SystemCallError,NoMethodError
    rescue SystemCallError
      puts "[ERROR - SiriProxy] Something went wrong with the 4S session..."
    end

  end

  def checkHaveiPad3Data(object)
    #changed the way validation are saved. Now the get the values via the object etc.
    @sessionValidationData = object["properties"]["validationData"].unpack('H*').join("") if object["properties"]["validationData"] !=nil
    @sessionValidationData = object["properties"]["sessionValidationData"].unpack('H*').join("") if object["properties"]["sessionValidationData"] !=nil

    if @sessionValidationData != nil #removed checking of assistantid etc
      #Writing keys to Database
      keyiPad3=KeyiPad3.instance
      keyiPad3.sessionValidation=@sessionValidationData
      #checking for ipad3 assistant and speechid
      if object["properties"]["assistantId"] !=nil
        keyiPad3.assistantid=object["properties"]["assistantId"]
      else
        keyiPad3.assistantid="no assistant"
      end
      if object["properties"]["speechId"] !=nil
        keyiPad3.speechid = object["properties"]["speechId"]
      else
        keyiPad3.speechid="no speech"
      end
      keyiPad3.banned='False'
      keyiPad3.expired='False'
      if self.iOS < 6
        keyiPad3.iPad3='True'
      elsif self.iOS >= 6
        keyiPad3.iPad3='Sorta'
      else #In case getting the iOS version fails for some reason
        keyiPad3.iPad3='True'
      end
      keyiPad3.client_apple_account_id="This is not working."
      if $keyDao.check_duplicate(keyiPad3)
        puts "[Info - SiriProxy] Duplicate Validation Data. Key NOT saved"
      else
        $keyDao.insert(keyiPad3)
        puts "[Info - SiriProxy] Keys written to Database"
        #also unban all keys available.
        if $APP_CONFIG.private_server.to_s.upcase == "ON" and keyiPad3.iPad3=="Sorta" #unBan is key is usable for more than dictation
          $keyDao.unban_keys #unBan because a key was inserted! should spoof enough
          puts "[Info - SiriProxy] New iPad 3 Key added and keys set to unbanned"
        end
      end
    else
      puts "[Info - SiriProxy] Something went wrong. Please file this bug. Key NOT saved!"
    end
    get_validationData(object) if self.iOS < 6 and $APP_CONFIG.try_iPad3!=true
    if  self.validationData_avail
      puts "[Info - SiriProxy] using saved sessionvalidationData"
      object["properties"]["sessionValidationData"] = plist_blob(self.sessionValidationData)
    else
      puts "[Info - SiriProxy] no validationData available :("
    end

  end
  #this method validation data now is the one that defines the keyload.
  #This way KeyLoad gets the meaning of request. Its updated via request of assistantload/create
  def get_validationData(object)
    begin

      if object["class"]=="CreateAssistant" # now separates initial request to Loadassistant and Create Assistant
        @createassistant=true
        @assistant_found=false
        @key=Key.new
        @available_keys=$keyDao.list_keys_for_new_assistant().count

        if (@available_keys) > 0 and $keyDao.next_available_for_new_assistant()!=nil

          puts "[Key - SiriProxy] Keys available for Creation of Assistants [#{@available_keys}]"
          @key=$keyDao.next_available_for_new_assistant()
          puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data"
          puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data For Object with aceid [#{object["aceId"]}] and class #{object["class"]}" if $LOG_LEVEL > 2
          @oldkeyload=@key.keyload
          @key.keyload=@key.keyload+20
          $keyDao.setkeyload(@key)
          puts "[Key - SiriProxy] Key with id[#{@key.id}] increased it's keyload from [#{@oldkeyload}] to [#{@key.keyload}]"
          self.sessionValidationData= @key.sessionValidation
          self.validationData_avail = true

        else

          puts "[Key - SiriProxy] No keys available in database Closing connections"
          self.validationData_avail = false
          self.close_connection() #close connections
          self.other_connection.close_connection() #close other
          return false

        end

      elsif object["class"]=="LoadAssistant" and object["properties"]["assistantId"] !=nil and object["properties"]["speechId"] !=nil

        @createassistant=false
        @assistant_found=true
        #grab assistant
        if object["properties"]["activationToken"] != nil
          @usedkey = $keyDao.list_keys_for_stats()
          puts '[Info - SiriProxy] Recieved token'
          @activation_token_recieved=true
          @activation_token=ActivationToken.new
          @activation_token.aceid=object["aceId"]
          @activation_token.data=object["properties"]["activationToken"]
          pp @activation_token if $LOG_LEVEL > 2
          @keystats=$keystatisticsDao.get_key_stats(@usedkey)
          if @keystats==nil
            $keystatisticsDao.insert(@usedkey)
            @keystats=$keystatisticsDao.get_key_stats(@usedkey)
          end
          @keystats.total_tokens_recieved+=1
          $keystatisticsDao.save_key_stats(@keystats)
          pp @keystats
        end
        @loadedassistant=object["properties"]["assistantId"]
        @loadedspeechid=object["properties"]["speechId"]

        #Lets put some auth here!!!
        #if the assistant that the client is trying to load is not registered (was not created on this server)
        #or if the client is not valid then protection gets on the way
        @userassistant=Assistant.new
        @userassistant.assistantid=@loadedassistant
        @userassistant.speechid=@loadedspeechid
        @userassistant.last_ip=@clientip
        @userassistant=$assistantDao.check_duplicate(@userassistant)  #check if there is a registered assistant

        if  @userassistant!=nil #If there is one then

          puts "[Authentification - SiriProxy] Registered Assistant Found :-)"
          @user=$clientsDao.find_by_assistant(@userassistant) #find the user with that assistant

          if @user==nil #Incase this user doesnt exist!!!!!!! Bug or not complete transaction

            puts "[Authentification - SiriProxy] No client for Assistant [#{@loadedassistant}]  Found :-("
            self.validationData_avail = false
            self.close_connection() #close connections
            self.other_connection.close_connection() #close other
            return false

          elsif @user.valid=='False'

            $assistantDao.updateassistant(@userassistant) # to update with last login and ip
            puts "[Authentification - SiriProxy] Access Denied!! -> Client name:[#{@user.fname}] nickname[#{@user.nickname}] appleid[#{@user.appleAccountid}] Connected "
            self.validationData_avail = false
            self.close_connection() #close connections
            self.other_connection.close_connection() #close other Here needs fake mode
            return false

          elsif @user.valid=='True' #if its valid!!!
            
            plugin_manager.user_assistant = @loadedassistant
            plugin_manager.user_appleid = @user.appleAccountid
            plugin_manager.user_fname = @user.fname
            plugin_manager.user_nickname = @user.nickname
            plugin_manager.user_language = object["language"]
            plugin_manager.user_devicetype = @user.devicetype
            plugin_manager.user_deviceOS = @user.deviceOS
            plugin_manager.user_lastIP = @user.last_ip
            plugin_manager.user_last_login = @user.last_login

            @key=Key.new
            @available_keys=$keyDao.list4Skeys().count + $keyDao.listiPad3keys().count

            if (@available_keys) > 0
              puts "[Key - SiriProxy] Keys available for Registered Only clients [#{@available_keys}]"
              @key=$keyDao.next_available()
              puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data"
              puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data For Object with aceid [#{object["aceId"]}] and class #{object["class"]}" if $LOG_LEVEL > 2
              @oldkeyload=@key.keyload
              @key.keyload=@key.keyload+10
              $keyDao.setkeyload(@key)
              puts "[Key - SiriProxy] Key with id[#{@key.id}] increased it's keyload from [#{@oldkeyload}] to [#{@key.keyload}]"
              self.sessionValidationData= @key.sessionValidation
              self.validationData_avail = true

            else
              @available_keys=$keyDao.listiPad3Dictationkeys().count
              if $APP_CONFIG.try_iPad3==true and (@available_keys) > 0
                puts "[Key - SiriProxy] iPad 3 Dictation Keys available for Registered Only clients [#{@available_keys}]"
                @key=$keyDao.next_available_Dictation()
                puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data"
                puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data For Object with aceid [#{object["aceId"]}] and class #{object["class"]}" if $LOG_LEVEL > 2
                @oldkeyload=@key.keyload
                @key.keyload=@key.keyload+10
                $keyDao.setkeyload(@key)
                puts "[Key - SiriProxy] Key with id[#{@key.id}] increased it's keyload from [#{@oldkeyload}] to [#{@key.keyload}]"
                self.sessionValidationData= @key.sessionValidation
                self.validationData_avail = true
              else
                puts "[Key - SiriProxy] No 4S keys available in database Closing connections"
                self.validationData_avail = false
                self.close_connection() #close connections
                self.other_connection.close_connection() #close other
                return false
              end
            end

            $assistantDao.updateassistant(@userassistant)
            puts "[Authentification - SiriProxy] Access Granted! -> Client name:[#{@user.fname}] nickname[#{@user.nickname}] appleid[#{@user.appleAccountid}] Connected "

          end

        else #if no assistant registed found

          if $APP_CONFIG.private_server.to_s.upcase=="ON" and self.is_4S!=true and self.is_iPad3!=true

            puts "[Authentification - SiriProxy] Assistant [#{@loadedassistant}] is not registered. Banning Connection :-("
            self.validationData_avail = false
            self.close_connection() #close connections
            self.other_connection.close_connection() #close other
            return false

          elsif $APP_CONFIG.clients_must_be_in_database==true and self.is_4S!=true and self.is_iPad3!=true

            puts "[Authentification - SiriProxy] Assistant [#{@loadedassistant}] is not registered!! :-O"
            @checkclient=$clientsDao.find_by_assistant(@loadedassistant) # In case client exists, but assistant data was not generated or something

            if @checkclient==nil

              puts "[Authentification - SiriProxy] Couldn't find client with assistant [#{@loadedassistant}]!! Banning Connection :-("
              self.validationData_avail = false
              self.close_connection() #close connections
              self.other_connection.close_connection() #close other
              return false

            else

              puts "[Authentification - SiriProxy] Found client with assistant [#{@loadedassistant}]! Allowing connection... :-)"
              @key=Key.new
              @available_keys=$keyDao.list4Skeys().count + $keyDao.listiPad3keys().count

              if (@available_keys) > 0

                puts "[Key - SiriProxy] Keys available [#{@available_keys}]"
                @key=$keyDao.next_available()
                puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data"
                puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data For Object with aceid [#{object["aceId"]}] and class #{object["class"]}" if $LOG_LEVEL > 2
                @oldkeyload=@key.keyload
                @key.keyload=@key.keyload+10
                $keyDao.setkeyload(@key)
                puts "[Key - SiriProxy] Key with id[#{@key.id}] increased it's keyload from [#{@oldkeyload}] to [#{@key.keyload}]"
                self.sessionValidationData= @key.sessionValidation
                self.validationData_avail = true

              else
                @available_keys=$keyDao.listiPad3Dictationkeys().count
                if $APP_CONFIG.try_iPad3==true and (@available_keys) > 0
                  puts "[Key - SiriProxy] iPad 3 Dictation Keys available to clients [#{@available_keys}]"
                  @key=$keyDao.next_available_Dictation()
                  puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data"
                  puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data For Object with aceid [#{object["aceId"]}] and class #{object["class"]}" if $LOG_LEVEL > 2
                  @oldkeyload=@key.keyload
                  @key.keyload=@key.keyload+10
                  $keyDao.setkeyload(@key)
                  puts "[Key - SiriProxy] Key with id[#{@key.id}] increased it's keyload from [#{@oldkeyload}] to [#{@key.keyload}]"
                  self.sessionValidationData= @key.sessionValidation
                  self.validationData_avail = true
                else
                  puts "[Key - SiriProxy] No keys available in database Closing connections"
                  self.validationData_avail = false
                  self.close_connection() #close connections
                  self.other_connection.close_connection() #close other
                  return false
                end
              end

            end

          else # if its a public server

            @key=Key.new
            @available_keys=$keyDao.list4Skeys().count + $keyDao.listiPad3keys().count

            if (@available_keys) > 0

              puts "[Key - SiriProxy] Keys available for Public  [#{@available_keys}]"
              @key=$keyDao.next_available()
              puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data"
              puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data For Object with aceid [#{object["aceId"]}] and class #{object["class"]}" if $LOG_LEVEL > 2
              @oldkeyload=@key.keyload
              @key.keyload=@key.keyload+10
              $keyDao.setkeyload(@key)
              puts "[Key - SiriProxy] Key with id[#{@key.id}] increased it's keyload from [#{@oldkeyload}] to [#{@key.keyload}]"
              self.sessionValidationData= @key.sessionValidation
              self.validationData_avail = true

            else
              @available_keys=$keyDao.listiPad3Dictationkeys().count
              if $APP_CONFIG.try_iPad3==true and (@available_keys) > 0
                puts "[Key - SiriProxy] iPad 3 Dictation Keys available to clients [#{@available_keys}]"
                @key=$keyDao.next_available_Dictation()
                puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data"
                puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data For Object with aceid [#{object["aceId"]}] and class #{object["class"]}" if $LOG_LEVEL > 2
                @oldkeyload=@key.keyload
                @key.keyload=@key.keyload+10
                $keyDao.setkeyload(@key)
                puts "[Key - SiriProxy] Key with id[#{@key.id}] increased it's keyload from [#{@oldkeyload}] to [#{@key.keyload}]"
                self.sessionValidationData= @key.sessionValidation
                self.validationData_avail = true
              else
                puts "[Key - SiriProxy] No keys available in database Closing connections"
                self.validationData_avail = false
                self.close_connection() #close connections
                self.other_connection.close_connection() #close other
                return false
              end
            end

          end

        end


      else  #here now goes anything except these 2 objects let's let them pass the validation

        @key=Key.new
        @available_keys=$keyDao.list4Skeys().count + $keyDao.listiPad3keys().count

        if (@available_keys) > 0

          puts "[Key - SiriProxy] Keys available for Public and Other than load and create assistant [#{@available_keys}]"
          @key=$keyDao.next_available()
          puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data"
          puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data For Object with aceid [#{object["aceId"]}] and class #{object["class"]}" if $LOG_LEVEL > 2
          @oldkeyload=@key.keyload
          @key.keyload=@key.keyload+10
          $keyDao.setkeyload(@key)
          puts "[Key - SiriProxy] Key with id[#{@key.id}] increased it's keyload from [#{@oldkeyload}] to [#{@key.keyload}]"
          self.sessionValidationData= @key.sessionValidation
          self.validationData_avail = true

        else
          @available_keys=$keyDao.listiPad3Dictationkeys().count
          if $APP_CONFIG.try_iPad3==true and (@available_keys) > 0
            puts "[Key - SiriProxy] iPad 3 Keys available clients [#{@available_keys}]"
            @key=$keyDao.next_available_Dictation()
            puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data"
            puts "[Keys - SiriProxy] Key [#{@key.id}] Loaded from Database for Validation Data For Object with aceid [#{object["aceId"]}] and class #{object["class"]}" if $LOG_LEVEL > 2
            @oldkeyload=@key.keyload
            @key.keyload=@key.keyload+10
            $keyDao.setkeyload(@key)
            puts "[Key - SiriProxy] Key with id[#{@key.id}] increased it's keyload from [#{@oldkeyload}] to [#{@key.keyload}]"
            self.sessionValidationData= @key.sessionValidation
            self.validationData_avail = true
          else
            puts "[Key - SiriProxy] No keys available in database Closing connections"
            self.validationData_avail = false
            self.close_connection() #close connections
            self.other_connection.close_connection() #close other
            return false
          end
        end

      end

      #rescue SystemCallError,NoMethodError
    rescue SystemCallError
      puts "[ERROR - SiriProxy] Error opening the sessionValidationData  file. Connect an iPhone4S first or create them manually!"
    end
  end

  def plist_blob(string)
    string = [string].pack('H*')
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
      if self.name=="Guzzoni"
        puts "   @connectionfromapple=true "
        @connectionfromapple=true
      end
      ##############
      #A Device has connected!!!
      #Check for User Agent and replace correctly
    elsif line.match(/^Host:/)
      line = "Host: guzzoni.apple.com"  #Keeps Apple from instantly knowing that this is a Proxy Server.
    elsif line.match(/^User-Agent:/)
      #if its and iphone4s
      self.clientport, self.clientip = Socket.unpack_sockaddr_in(get_peername)
      if line.match(/iPhone4,1;/)
        puts "[RollEyes - Siri*-*Proxy]"
        puts "[Info - SiriProxy] iPhone 4S connected from IP #{self.clientip}"
        puts "[RollEyes - Siri*-*Proxy]"
        if line.match(/5.0/)
          self.iOS = 5
        elsif line.match(/5.1/)
          self.iOS = 5.1
        elsif line.match(/6.0/)
          self.iOS = 6
        end
        self.is_4S = true
        self.is_iPad3 = false
        @devicetype="iPhone 4S"
      elsif line.match(/iPad3,1;/) and $APP_CONFIG.try_iPad3==true
        puts "[RollEyes - Siri*-*Proxy]"
        puts "[Info - SiriProxy] iPad 3 Wi-Fi only connected from IP #{self.clientip}"
        puts "[RollEyes - Siri*-*Proxy]"
        if line.match(/5.0/)
          self.iOS = 5
        elsif line.match(/5.1/)
          self.iOS = 5.1
        elsif line.match(/6.0/)
          self.iOS = 6
        end
        self.is_4S = false
        self.is_iPad3 = true
        @devicetype="iPad 3 Wi-Fi only"
      elsif line.match(/iPad3,2;/) and $APP_CONFIG.try_iPad3==true
        puts "[RollEyes - Siri*-*Proxy]"
        puts "[Info - SiriProxy] iPad 3 CDMA connected from IP #{self.clientip}"
        puts "[RollEyes - Siri*-*Proxy]"
        if line.match(/5.0/)
          self.iOS = 5
        elsif line.match(/5.1/)
          self.iOS = 5.1
        elsif line.match(/6.0/)
          self.iOS = 6
        end
        self.is_4S = false
        self.is_iPad3 = true
        @devicetype="iPad 3 CDMA"
      elsif line.match(/iPad3,3;/) and $APP_CONFIG.try_iPad3==true
        puts "[RollEyes - Siri*-*Proxy]"
        puts "[Info - SiriProxy] iPad 3 GSM connected from IP #{self.clientip}"
        puts "[RollEyes - Siri*-*Proxy]"
        if line.match(/5.0/)
          self.iOS = 5
        elsif line.match(/5.1/)
          self.iOS = 5.1
        elsif line.match(/6.0/)
          self.iOS = 6
        end
        self.is_4S = false
        self.is_iPad3 = true
        @devicetype="iPad 3 GSM"
      else # now seperates anything else exept 4s
        #we can close connections here .... and we can count them here
        puts "[Info - Siriproxy] Curent connections [#{$conf.active_connections}]"
        #Some code in order connections to depend on the evailable keys
        #if no keys then maximize the connections in order to prevent max connection reach and 4s not be able to connect
        #
        @max_connections=$conf.max_connections
        @keysavailable=$keyDao.list4Skeys().count

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
          self.is_iPad3 = false
          @devicetype="GSM iPhone4"
          if line.match(/5.0/)
            self.iOS = 5
          elsif line.match(/5.1/)
            self.iOS = 5.1
          elsif line.match(/6.0/)
            self.iOS = 6
          end
          puts "[Info - SiriProxy] GSM iPhone 4 connected from IP #{self.clientip}"
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPhone3,1"] = "iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s "
          puts "[Info - SiriProxy] Final Header: " + line if $LOG_LEVEL > 2
        elsif  line.match(/iPhone3,3;/)
          self.is_4S = false
          self.is_iPad3 = false
          @devicetype="CDMA iPhone4"
          if line.match(/5.0/)
            self.iOS = 5
          elsif line.match(/5.1/)
            self.iOS = 5.1
          elsif line.match(/6.0/)
            self.iOS = 6
          end
          puts "[Info - SiriProxy] CDMA iPhone 4 connected from IP #{self.clientip}"
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPhone3,3"] = "iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s "
          puts "[Info - SiriProxy] Final Header: " + line if $LOG_LEVEL > 2
        elsif line.match(/iPad2,1;/)
          self.is_4S = false
          self.is_iPad3 = false
          @devicetype="iPad2 Wifi Only"
          if line.match(/5.0/)
            self.iOS = 5
          elsif line.match(/5.1/)
            self.iOS = 5.1
          elsif line.match(/6.0/)
            self.iOS = 6
          end
          puts "[Info - SiriProxy] iPad2 Wifi Only connected from IP #{self.clientip}"
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPad/iPad2,1"] = "iPhone/iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s "
          puts "[Info - SiriProxy] Final Header: " + line if $LOG_LEVEL > 2
        elsif line.match(/iPad2,2;/)
          self.is_4S = false
          self.is_iPad3 = false
          @devicetype="iPad2 GSM"
          if line.match(/5.0/)
            self.iOS = 5
          elsif line.match(/5.1/)
            self.iOS = 5.1
          elsif line.match(/6.0/)
            self.iOS = 6
          end
          puts "[Info - SiriProxy] iPad2 GSM connected from IP #{self.clientip}"
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPad/iPad2,2"] = "iPhone/iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s "
          puts "[Info - SiriProxy] Final Header: " + line if $LOG_LEVEL > 2
        elsif line.match(/iPad2,3;/)
          self.is_4S = false
          self.is_iPad3 = false
          @devicetype="iPad2 CDMA"
          if line.match(/5.0/)
            self.iOS = 5
          elsif line.match(/5.1/)
            self.iOS = 5.1
          elsif line.match(/6.0/)
            self.iOS = 6
          end
          puts "[Info - SiriProxy] iPad2 CDMA connected from IP #{self.clientip}"
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPad/iPad2,3"] = "iPhone/iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s "
          puts "[Info - SiriProxy] Final Header: " + line if $LOG_LEVEL > 2
        elsif line.match(/iPad2,4;/)
          self.is_4S = false
          self.is_iPad3 = false
          @devicetype="iPad2 32nm Wifi Only"
          if line.match(/5.0/)
            self.iOS = 5
          elsif line.match(/5.1/)
            self.iOS = 5.1
          elsif line.match(/6.0/)
            self.iOS = 6
          end
          puts "[Info - SiriProxy] iPad2 32nm Wifi Only connected from IP #{self.clientip}"
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPad/iPad2,4"] = "iPhone/iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s "
          puts "[Info - SiriProxy] Final Header: " + line if $LOG_LEVEL > 2
        elsif line.match(/iPad1,1;/)
          self.is_4S = false
          self.is_iPad3 = false
          @devicetype="iPad 1st generation"
          if line.match(/5.0/)
            self.iOS = 5
          elsif line.match(/5.1/)
            self.iOS = 5.1
          end
          puts "[Info - SiriProxy] iPad 1st generation connected from IP #{self.clientip}"
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPad/iPad1,1"] = "iPhone/iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s "
          puts "[Info - SiriProxy] Final Header: " + line if $LOG_LEVEL > 2
        elsif line.match(/iPad3,1;/)
          self.is_4S = false
          self.is_iPad3 = false
          @devicetype="iPad 3 Wi-Fi only"
          if line.match(/5.0/)
            self.iOS = 5
          elsif line.match(/5.1/)
            self.iOS = 5.1
          elsif line.match(/6.0/)
            self.iOS = 6
          end
          puts "[Info - SiriProxy] iPad 3 Wi-Fi only connected from IP #{self.clientip}"
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPad/iPad3,1"] = "iPhone/iPhone4,1" if self.iOS < 6 #No need on iOS6
          puts "[Info - SiriProxy] Changed header to iphone4s "
          puts "[Info - SiriProxy] Final Header: " + line if $LOG_LEVEL > 2
        elsif line.match(/iPad3,2;/)
          self.is_4S = false
          self.is_iPad3 = true
          @devicetype="iPad 3 CDMA"
          if line.match(/5.0/)
            self.iOS = 5
          elsif line.match(/5.1/)
            self.iOS = 5.1
          elsif line.match(/6.0/)
            self.iOS = 6
          end
          puts "[Info - SiriProxy] iPad 3 CDMA connected from IP #{self.clientip}"
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPad/iPad3,2"] = "iPhone/iPhone4,1" if self.iOS < 6 #No need on iOS6
          puts "[Info - SiriProxy] Changed header to iphone4s "
          puts "[Info - SiriProxy] Final Header: " + line if $LOG_LEVEL > 2
        elsif line.match(/iPad3,3;/)
          self.is_4S = false
          self.is_iPad3 = true
          @devicetype="iPad 3 GSM"
          if line.match(/5.0/)
            self.iOS = 5
          elsif line.match(/5.1/)
            self.iOS = 5.1
          elsif line.match(/6.0/)
            self.iOS = 6
          end
          puts "[Info - SiriProxy] iPad 3 GSM connected from IP #{self.clientip}"
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPad/iPad3,3"] = "iPhone/iPhone4,1" if self.iOS < 6 #No need on iOS6
          puts "[Info - SiriProxy] Changed header to iphone4s "
          puts "[Info - SiriProxy] Final Header: " + line if $LOG_LEVEL > 2
        elsif line.match(/iPod4,1;/)
          self.is_4S = false
          self.is_iPad3 = false
          @devicetype="iPod touch 4th generation"
          if line.match(/5.0/)
            self.iOS = 5
          elsif line.match(/5.1/)
            self.iOS = 5.1
          elsif line.match(/6.0/)
            self.iOS = 6
          end
          puts "[Info - SiriProxy] iPod touch 4th generation connected from IP #{self.clientip}"
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line["iPod touch/iPod4,1"] = "iPhone/iPhone4,1"
          puts "[Info - SiriProxy] Changed header to iphone4s "
          puts "[Info - SiriProxy] Final Header: " + line if $LOG_LEVEL > 2
        else
          #Everithing else like android devices, computer apps etc
          #Change unknown to iPhone to make sure everything works..
          puts "[Info - SiriProxy] Unknown Device Connected from IP #{self.clientip}" if $APP_CONFIG.allow_unknown_clients==true
          puts "[Info - SiriProxy] Unknown Device Connected DOS ATTACK! #{self.clientip}" if $APP_CONFIG.allow_unknown_clients!=true
          puts "[Info - SiriProxy] Unknown Device Connected from IP #{self.clientip}"
          self.is_4S = false
          self.is_iPad3 = false
          @devicetype="Unknown Device"
          self.iOS = 5
          if $APP_CONFIG.allow_unknown_clients!=true
            self.close_connection() #close connections
            self.other_connection.close_connection() #close other
          end
          puts "[Info - SiriProxy] Original Header: " + line if $LOG_LEVEL > 2
          line = "User-Agent: Assistant(iPhone/iPhone4,1; iPhone OS/5.0.1/9A405) Ace/1.0"
          puts "[Info - SiriProxy] Changed header to iphone4s "
          puts "[Info - SiriProxy] Final Header: " + line if $LOG_LEVEL > 2
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
      puts "[Warning - SiriProxy] Currupted Data!!! Clearing buffer!"
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

    unpacked="0400000001" if !unpacked.match(/^0(.)(.{8})$/) # its the value that causes the bug! Will treat it as ping pong!!! Hope this resolves this
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
        #puts "Ping Pong #{unpacked}"
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
    plist = CFPropertyList::List.new(:data => object_data)    #here is another bug sometimes
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
      puts "[Info - Dropping Object from Apple] #{object["class"]}" if $LOG_LEVEL > 1
      pp object if $LOG_LEVEL > 3
      return nil
    end

    #this comes as an reply from spire to set access token
    if(object["class"] == "CommandIgnored")
      puts "[Info - SiriProxy] Just ignoring the Authentification Token"
      if self.other_connection.activation_token_recieved==true and self.other_connection.activation_token.aceid==object["refId"]
        puts "[Info - SiriProxy] Letting the activation command ignored pass throught"
      else
        return nil
      end
    end

    #The Key banner

    if object["class"]=="CommandFailed"
      @commandFailed=true
      puts "[Warning - SiriProxy] Command Failed refid #{object["refId"]} and Creating? #{self.other_connection.createassistant}"
    end#should join these
    if object["class"]=="CommandFailed" and self.other_connection.createassistant  and self.other_connection.key!=nil and $APP_CONFIG.enable_auto_key_ban.to_s.upcase == 'ON'
      $keyDao.key_banned(self.other_connection.key)
      puts "[Warning - SiriProxy] The key [#{self.other_connection.key.id}] Marked as Banned! Still serving with validation..."
      #Should we close connections here? I
      self.close_connection()
      self.other_connection.close_connection()
      return nil
    end


    if object["properties"] != nil

      if object["class"]=="CreateSessionInfoResponse" and object["properties"]["validityDuration"]!=nil and  self.other_connection.is_4S==true and $APP_CONFIG.regenerate_interval!=nil
        object["properties"]["validityDuration"]=$APP_CONFIG.regenerate_interval #this timer can be customized
        puts "[Exploit - SiriProxy] Command sent to iPhone4s to regenerate multiple keys every [#{$APP_CONFIG.regenerate_interval}] seconds !!!"
      end

      if object["class"]=="CreateSessionInfoResponse" and object["properties"]["validityDuration"]!=nil and  self.other_connection.is_iPad3==true and $APP_CONFIG.regenerate_interval!=nil
        object["properties"]["validityDuration"]=$APP_CONFIG.regenerate_interval #this timer can be customized
        puts "[Exploit - SiriProxy] Command sent to iPad3 to regenerate multiple keys every [#{$APP_CONFIG.regenerate_interval}] seconds !!!"
      end

      #Lets record if Apple sent the respose to create session
      if object["class"]=="GetSessionCertificateResponse"
        @GetSessionCertificateResponse=true
      end

      #Lets record how many Finish speech requests are made without the activation token and not by creating witch may not include the token if max assistants are reached
      if object["class"]=="FinishSpeech" and self.name=="iPhone" and  self.other_connection.activation_token_recieved==false and @key!=nil and self.validationData_avail==true and  @createassistant==false and @finishspeech==false

        if  $APP_CONFIG.expiration_sesitivity!=0
          if  $APP_CONFIG.expiration_sesitivity!=nil and $APP_CONFIG.expiration_sesitivity > 0

            @expiration_sesitivity=$APP_CONFIG.expiration_sesitivity
          else
            @expiration_sesitivity=5 #default value
          end
          #if the device has a wrong assistant.plist then it will make constant finish speech and will break this code.
          #lets fix that
          @finishspeech=true
          @keystats=$keystatisticsDao.get_key_stats(@key)
          if @keystats==nil
            $keystatisticsDao.insert(@key)
            @keystats=$keystatisticsDao.get_key_stats(@key)
            @keystats.total_finishspeech_requests+=1
            $keystatisticsDao.save_key_stats(@keystats)
            puts '[Info - SiriProxy] Recorded FinishSpeech'
            pp @keystats
          elsif  @keystats.total_finishspeech_requests > @expiration_sesitivity #consider a dynamic number instead of 15
            #here comes the ceck!
            if @keystats.total_tokens_recieved==0
              $keyDao.validation_expired(@key) #probalby expired
              puts '[Key - SiriProxy] Probably the validation expired! '
              sendemail()
            else
              #reset them if no anomaly detected such as  expiration
              @keystats.total_finishspeech_requests=0
              @keystats.total_tokens_recieved=0
              $keystatisticsDao.save_key_stats(@keystats)
            end
          else
            @keystats.total_finishspeech_requests+=1
            $keystatisticsDao.save_key_stats(@keystats)
            puts '[Info - SiriProxy] Recorded FinishSpeech'
          end
        end
      end



      #Lets capture the unique ids for every appleid
      if object["class"]=="SetAssistantData"  #check this against validation  for the 4s
        #this changes by language change also. Please consider re code
        pp object if $LOG_LEVEL > 2
        #work to be done here
        @client=Client.new

        if object["properties"]["meCards"]!=nil

          @mecards_count=object["properties"]["meCards"].count
          for i in (0...@mecards_count)
            if object["properties"]["meCards"][i]["properties"]!=nil
              if object["properties"]["meCards"][i]["properties"]["firstName"]!=nil
                @client.fname=object["properties"]["meCards"][i]["properties"]["firstName"]
              else
                @client.fname="NA"
              end
              if object["properties"]["meCards"][i]["properties"]["nickName"]!=nil
                @client.nickname=object["properties"]["meCards"][i]["properties"]["nickName"]
              else
                @client.nickname="NA"
              end
              if object["properties"]["meCards"][i]["properties"]["identifier"]!=nil
                @client.appleDBid=object["properties"]["meCards"][i]["properties"]["identifier"]
              else
                @client.appleDBid="NA"
              end
            end
          end
        else
          @client.fname="NA"
          @client.nickname="NA"
          @client.appleDBid="NA"
        end

        #the absources can contain more than icloud id and thus not just one row is available !!!!!
        if object["properties"]["abSources"]!=nil
          @absources_count=object["properties"]["abSources"].count #count how many sources the object may have

          for i in (0...@absources_count)

            if object["properties"]["abSources"][i]["properties"]!=nil and object["properties"]["abSources"][i]["properties"]["accountName"]!=nil and object["properties"]["abSources"][i]["properties"]["accountName"]="Card" and object["properties"]["abSources"][i]["properties"]["accountIdentifier"]!=nil

              puts object["properties"]["abSources"][i]["properties"]["accountIdentifier"] if $LOG_LEVEL > 2
              @client.appleAccountid=object["properties"]["abSources"][i]["properties"]["accountIdentifier"]
              i=@absources_count # hehe lets see if this fixes some errors
            end

          end

        end

        @client.appleAccountid="NA" if @client.appleAccountid==nil

        @client.valid="True" #needed if config in empy for the below
        @client.valid="False" if $APP_CONFIG.private_server.to_s.upcase == "ON" and self.is_4S!=true and self.is_iPad3!=true
        @client.devicetype=@devicetype
        @client.deviceOS=self.iOS
        @client.last_ip=@clientip
        #this must not be updated in onld clients from here
        #pp @client

        #Lets put some auth here Log the clients even though they may not have access
        if @createassistant==true and @client!=nil
          puts 'Debug Step one of creating assistants' if $LOG_LEVEL > 2
          puts 'Client is 'if $LOG_LEVEL > 2
            pp @client if $LOG_LEVEL > 2
            @oldclient=$clientsDao.check_duplicate(@client)

            if @oldclient==nil
              $clientsDao.insert(@client)
              puts "[Client - SiriProxy] NEW Client [#{@client.appleAccountid}] and Valid=[#{@client.valid}] added To database"
              if @client.valid!='True'
                self.close_connection()
                self.other_connection.close_connection()
                self.validationData_avail=false #extra protection on alive packets
                puts "[Authentification - Siriproxy] NEW Client [#{@client.appleAccountid}] is not Valid. Access denied!!!"
                puts "[Authentification - Siriproxy] Dropping Connection"
                return nil
              end
            else
              @oldclient.fname=@client.fname #in case they ever change this
              @oldclient.nickname=@client.nickname #in case they ever change this
              @oldclient.appleDBid=@client.appleDBid
              @oldclient.appleAccountid=@client.appleAccountid
              @oldclient.devicetype=@client.devicetype #For users with multiple devices on same Apple Account
              @oldclient.deviceOS=@client.deviceOS #For users with multiple devices on same Apple Account
              @oldclient.last_ip=@clientip
              $clientsDao.update(@oldclient)
              puts "[Client - SiriProxy] OLD Client changed settings [#{@oldclient.appleAccountid}]"
              if @oldclient.valid!='True' #make a perm ban
                self.close_connection()
                self.other_connection.close_connection()
                self.validationData_avail=false #extra protection on alive packets
                puts "[Authentification - Siriproxy] OLD Client [#{@client.appleAccountid}] is not Valid. Access denied!!!"
                puts "[Authentification - Siriproxy] Dropping Connection"
                return nil
              end
            end
          end
          #end of here


          #changing and connecting
          if  @client!=nil and @loadedassistant!=nil and @loadedassistant!="" and @createassistant==false #will not enter here if creating!
            #need to get in here if changing upon creation is needed
            puts "passed" if $LOG_LEVEL > 2
            pp object if $LOG_LEVEL > 2
            @oldclient=$clientsDao.check_duplicate(@client)
            pp @oldclient if $LOG_LEVEL > 2
            if @oldclient==nil #should never get in here exept on public
              $clientsDao.insert(@client)
              puts "[Client - SiriProxy] NEW Client changed settings [#{@client.appleAccountid}] With Assistantid [#{@loadedassistant}]"

            else
              @oldclient.fname=@client.fname #in case they ever change this
              @oldclient.nickname=@client.nickname #in case they ever change this
              @oldclient.appleDBid=@client.appleDBid
              @oldclient.appleAccountid=@client.appleAccountid
              @oldclient.devicetype=@client.devicetype #For users with multiple devices on same Apple Account
              @oldclient.deviceOS=@client.deviceOS #For users with multiple devices on same Apple Account
              @oldclient.last_ip=@clientip
              $clientsDao.update(@oldclient)
              puts "[Client - SiriProxy] OLD Client changed settings [#{@oldclient.appleAccountid}] With Assistantid [#{@loadedassistant}]"
              @client=@oldclient #hehe
            end

            @assistant=Assistant.new
            @assistant.assistantid=@loadedassistant
            @assistant.speechid=@loadedspeechid
            @assistant.client_apple_account_id=@client.appleAccountid
            if @key != nil and @key.id != nil
              @assistant.key_id=@key.id
            else
              @assistant.key_id=0
            end
            @assistant.devicetype=@devicetype
            @assistant.deviceOS=self.iOS
            @assistant.last_ip=@clientip
            if  $assistantDao.check_duplicate(@assistant) #Should never  find a duplicate i think so
              puts "[Info - SiriProxy] Duplicate Assistand ID. Assistant NOT saved"
              @assistant.last_ip=@clientip
              $assistantDao.updateassistant(@userassistant)
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
          elsif self.is_iPad3
            puts "[Info - SiriProxy] Saving iPad3 validation Data"
            checkHaveiPad3Data(object)
          else
            get_validationData(object)
            if self.validationData_avail
              puts "[Info - SiriProxy] using saved validationData"
              object["properties"]["validationData"] = plist_blob(self.sessionValidationData)
            else
              puts "[Info - SiriProxy] No Validation Data available :("
            end
          end
        end
        if object["properties"]["sessionValidationData"] !=nil #&& !object["properties"]["sessionValidationData"].empty? I was wrong both are needed
          if self.is_4S
            puts "[Info -  SiriProxy] using iPhone 4S validationData and saving it"
            checkHave4SData(object)
          elsif self.is_iPad3
            puts "[Info - SiriProxy] Using iPad3 validationData and saving it"
            checkHaveiPad3Data(object)
          else
            get_validationData(object)
            if self.validationData_avail
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
            puts "[Warning - SiriProxy] This device is not set up!"
            self.speechId_avail=false #useless
            self.assistantId_avail=false #useless
            #lets close the connection and even better maybe destroy the assisatnt

          else
            self.speechId_avail=true #useless
            self.assistantId_avail=true #useless
            if object["class"]=="LoadAssistant"
              puts "[Info - SiriProxy] Device has assistantId: #{object["properties"]["assistantId"]}"
              puts "[Info - SiriProxy] Device has speechID: #{object["properties"]["speechId"]}"
            end
            #Lets record the assistants, and verify users
            #will not get in here if the private is on and user is not valid
            #Also i know that the users are already checked but the double check comes only due to that i didnt have time to clean this up
            if  object["class"]=="AssistantCreated" and self.other_connection.client!=nil and self.other_connection.createassistant==true
              puts "[Info - SiriProxy] Creating new Assistant..."
              pp object
              if object["properties"]["activationToken"] != nil
                @usedkey = $keyDao.list_keys_for_stats()
                puts '[Info - SiriProxy] Recieved token'
                @activation_token_recieved=true
                @activation_token=ActivationToken.new
                @activation_token.aceid=object["aceId"]
                @activation_token.data=object["properties"]["activationToken"]
                pp @activation_token if $LOG_LEVEL > 2
                @keystats=$keystatisticsDao.get_key_stats(@usedkey)
                if @keystats==nil
                  $keystatisticsDao.insert(@usedkey)
                  @keystats=$keystatisticsDao.get_key_stats(@usedkey)
                end
                @keystats.total_tokens_recieved+=1
                $keystatisticsDao.save_key_stats(@keystats)
                pp @keystats
              end
              @assistant=Assistant.new
              @assistant.assistantid=object["properties"]["assistantId"]
              @assistant.speechid=object["properties"]["speechId"]
              if self.other_connection.key.id != nil
                @assistant.key_id=self.other_connection.key.id
              else
                @assistant.key_id=0
              end
              @assistant.devicetype=self.other_connection.devicetype
              @assistant.last_ip=self.other_connection.clientip
              pp self.other_connection.client if $LOG_LEVEL > 2

              if  $assistantDao.check_duplicate(@assistant) #Should never  find a duplicate i think so

                puts "[Info - SiriProxy] Duplicate Assistant ID. Assistant NOT saved"

              else

                @oldclient=$clientsDao.check_duplicate(self.other_connection.client)

                if @oldclient==nil

                  # pp self.other_connection.client
                  $clientsDao.insert(self.other_connection.client)
                  @assistant.client_apple_account_id=self.other_connection.client.appleAccountid
                  $assistantDao.createassistant(@assistant)
                  puts "[Client - SiriProxy] Created Assistant ID  #{@assistant.assistantid} using key [#{self.other_connection.key.id}]"
                  $keyDao.update_used(self.other_connection.key)
                  puts "[Client - SiriProxy] NEW Client [#{self.other_connection.client.appleAccountid}] created Assistantid [#{@assistant.assistantid}]"

                elsif @client!=nil and @client.fname!=nil
                  @oldclient.fname=@client.fname #in case they ever change this
                  @oldclient.nickname=@client.nickname #in case they ever change this
                  @oldclient.appleDBid=@client.appleDBid
                  @oldclient.appleAccountid=@client.appleAccountid
                  @oldclient.devicetype=@client.devicetype #For users with multiple devices on same Apple Account
                  @oldclient.deviceOS=@client.deviceOS #For users with multiple devices on same Apple Account
                  @oldclient.last_ip=@clientip
                  $clientsDao.update(@oldclient)
                  @assistant.client_apple_account_id=@oldclient.appleAccountid
                  $assistantDao.createassistant(@assistant)
                  puts "[Client - SiriProxy] Created Assistant ID #{@assistant.assistantid} using key [#{self.other_connection.key.id}]"
                  $keyDao.update_used(self.other_connection.key)
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
      puts "[Key -  #{self.name}] Recieved Object Using: Key id [#{@key.id}] and Instance Keyload[#{@key.keyload}]  " if @key!=nil &&self.validationData_avail!=false && $LOG_LEVEL > 1
      $keyDao.update_used(@key) if @key!=nil &&self.validationData_avail!=false
      pp object if $LOG_LEVEL > 3


      #keeping this for filters
      new_obj = received_object(object)
      #puts self.name
      if self.validationData_avail==false and self.name=='iPhone' and self.is_4S==false and self.is_iPad3==false
        puts "[Protection - Siriproxy] Dropping Object from #{self.name}] #{object["class"]} due to no Validation or Authentification available" if $LOG_LEVEL >= 1
        puts '[Protection - Siriproxy] Closing both connections...'
        self.close_connection()
        self.other_connection.close_connection()


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
