
#####
  # This is the connection to the iPhone
#####
class SiriProxy::Connection::Iphone < SiriProxy::Connection
  def initialize
    $conf.active_connections = EM.connection_count          
    puts "Create server for iPhone connection"
    super
    self.name = "iPhone"
  end

  def post_init
    puts "[Info - iPhone] Curent connections [#{$conf.active_connections}]"
    #Some code in order connections to depend on the evailable keys
    #if no keys then maximize the connections in order to prevent max connection reach and 4s not be able to connect
    #
    @max_connections=$conf.max_connections
    @keysavailable=$keyDao.listkeys().count
    puts @keysavailable
    @max_connections
    puts $conf.max_connections
    puts @keysavailable
    if @keysavailable==0
       @max_connections=999
    elsif @keysavailable>0
       @max_connections=$conf.max_connections * @keysavailable
    end
    puts '[Keys - iPhone] Keys [#{@keysvailable}]'
    if $conf.active_connections>=@max_connections
      puts "[Warning - iPhone] Max Connections reached! Connection dropping...."
      super
      self.close_connection
      start_tls(:verify_peer      => false)
      else        
      super
      start_tls(:cert_chain_file  => File.expand_path("~/.siriproxy/server.passless.crt"),
                :private_key_file => File.expand_path("~/.siriproxy/server.passless.key"),
                :verify_peer      => false)
    end
  end

  def ssl_handshake_completed
    super
    self.other_connection = EventMachine.connect('guzzoni.apple.com', 443, SiriProxy::Connection::Guzzoni)
    self.plugin_manager.guzzoni_conn = self.other_connection
    other_connection.other_connection = self #hehe
    other_connection.plugin_manager = plugin_manager
  end
  
  def received_object(object)
    return plugin_manager.process_filters(object, :from_iphone)

    #plugin_manager.object_from_client(object, self)
  end
end
