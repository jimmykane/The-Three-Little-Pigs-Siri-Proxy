require 'eventmachine'
require 'zlib'
require 'pp'
require "siriproxy/version"
require "siriproxy/functions"       

class String
  def to_hex(seperator=" ")
    bytes.to_a.map{|i| i.to_s(16).rjust(2, '0')}.join(seperator)
  end
end

class SiriProxy
  
  def initialize()
    # @todo shouldnt need this, make centralize logging instead
    $LOG_LEVEL = $APP_CONFIG.log_level.to_i
    #Version support added
    puts "Initializing TLP version [#{SiriProxy::VERSION}]"

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
    
    #initialize key controller    
		$keyDao=KeyDao.instance#instansize Dao object controller
		$keyDao.connect_to_db($my_db)       
    
    #initialize key stats controller    
    $keystatisticsDao=KeyStatisticsDao.instance
    $keystatisticsDao.connect_to_db($my_db)
    
    #Initialize The Assistant Controller
    $assistantDao=AssistantDao.instance
    $assistantDao.connect_to_db($my_db)
    
    EM.threadpool_size=$conf.max_threads    
    
    #Print email config
    if $APP_CONFIG.send_email=='ON' or $APP_CONFIG.send_email=='on'
      puts '[Info - SiriProxy] Email notifications are [ON]!'
    else
      puts '[Info - SiriProxy] Email notifications are [OFF]!'
    end    
    
    EventMachine.run do
      begin
        puts "Starting SiriProxy on port #{$APP_CONFIG.port}.."
        EventMachine::start_server('0.0.0.0', $APP_CONFIG.port, SiriProxy::Connection::Iphone) { |conn|
          $stderr.puts "start conn #{conn.inspect}"
          conn.plugin_manager = SiriProxy::PluginManager.new()
          conn.plugin_manager.iphone_conn = conn
        }
        puts "Server is Up and Running"
        @timer2=120 # The expirer
        
         #Temp fix and guard to apple not replying command failed
         EventMachine::PeriodicTimer.new(@timer2){
            puts "[Expirer - SiriProxy] Expiring past 24 hour Keys"
           @totalkeysexpired=$keyDao.expire_24h_hour_keys
           puts @totalkeysexpired
           for i in (0...@totalkeysexpired) 
               sendemail()
           end          
           $keystatisticsDao.delete_keystats
            puts "[Stats - SiriProxy] Cleaning up key statistics"
           
         }
        EventMachine::PeriodicTimer.new(10){
          $conf.active_connections = EM.connection_count          
          $confDao.update($conf)
          ### Per Key based connections
          @max_connections=$conf.max_connections
          @availablekeys=$keyDao.listkeys().count
          if @availablekeys==0
            @max_connections=999
          elsif @availablekeys>0
            @max_connections=$conf.max_connections * @availablekeys
          end
          puts "[Info - SiriProxy] Active connections [#{$conf.active_connections}] Max connections [#{@max_connections}]"
          
        }
        EventMachine::PeriodicTimer.new($conf.keyload_dropdown_interval){
          @overloaded_keys_count=$keyDao.findoverloaded().count
          if (@overloaded_keys_count>0)
            @overloaded_keys=$keyDao.findoverloaded()     
            for i in 0..(@overloaded_keys_count-1)            
              @oldkeyload=@overloaded_keys[i].keyload   
              @overloaded_keys[i].keyload=@overloaded_keys[i].keyload-$conf.keyload_dropdown
              $keyDao.setkeyload(@overloaded_keys[i])
              puts "[Keys - SiriProxy] Decreasing Keyload for Key id=[#{@overloaded_keys[i].id}] and Decreasing keyload from [#{@oldkeyload}] to [#{@overloaded_keys[i].keyload}]"
            end
          end
        }
      rescue RuntimeError => err
        if err.message == "no acceptor"
          raise "Cannot start the server on port #{$APP_CONFIG.port} - are you root, or have another process on this port already?"
        else
          raise
        end
      end
    end
  end
end
