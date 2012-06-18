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

  def post_init   #removed code from here to allow a 4s to connect!
    super
    start_tls(:cert_chain_file  => File.expand_path("~/.siriproxy/server.passless.crt"),
              :private_key_file => File.expand_path("~/.siriproxy/server.passless.key"),
              :verify_peer      => false)
  end

  def ssl_handshake_completed
    super
    begin
      self.host = 'guzzoni.apple.com'
      self.other_connection = EventMachine.connect('guzzoni.apple.com', 443, SiriProxy::Connection::Guzzoni)
      self.plugin_manager.apple_conn = self.other_connection
      other_connection.other_connection = self #hehe
      other_connection.plugin_manager = plugin_manager
    rescue
      puts "[Warning - Siriproxy] Could not connect to Guzzoni!!! "
      puts "[Warning - Siriproxy] Attempting connection to Kryten instead..."
      self.host = 'kryten.apple.com'
      self.other_connection = EventMachine.connect('kryten.apple.com', 443, SiriProxy::Connection::Kryten)
      self.plugin_manager.apple_conn = self.other_connection
      other_connection.other_connection = self #hehe
      other_connection.plugin_manager = plugin_manager
      #self.close_connection
    end
  end

  def received_object(object)
    return plugin_manager.process_filters(object, :from_iphone)
    #plugin_manager.object_from_client(object, self)
  end
end
