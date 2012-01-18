require 'eventmachine'
require 'zlib'
require 'pp'


class String
  def to_hex(seperator=" ")
    bytes.to_a.map{|i| i.to_s(16).rjust(2, '0')}.join(seperator)
  end
end

class SiriProxy
  
  def initialize()
    # @todo shouldnt need this, make centralize logging instead
    $LOG_LEVEL = $APP_CONFIG.log_level.to_i
    #
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
    @@key=Key.new
    @@key.keyload=0
		$keyDao=KeyDao.instance#instansize Dao object controller
		$keyDao.connect_to_db($my_db)		
    
    if ($keyDao.listkeys().count)>0      
      @@key.availablekeys=$keyDao.listkeys().count      
      puts "[Keys - SiriProxy] Available Keys in Database: [#{@@key.availablekeys}]"
    else
      puts "[Keys - SiriProxy] Initialized Please connect a 4S. No keys available"
      @@key.availablekeys=0
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
        EventMachine::PeriodicTimer.new(10){
          $conf.active_connections = EM.connection_count          
          $confDao.update($conf)
          puts "[Info - SiriProxy] Active connections [#{$conf.active_connections}] Max connections [#{$conf.max_connections}]"
          if $conf.active_connections>=$conf.max_connections 
            EventMachine.stop
            puts "[Warning - Exit - SiriProxy] Max Connections reached! Sever exiting...."
            exit (1)#Fix for issue-bug https://github.com/jimmykane/The-Three-Little-Pigs-Siri-Proxy/issues/14
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
