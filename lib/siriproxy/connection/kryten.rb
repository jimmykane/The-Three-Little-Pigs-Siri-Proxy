#####
# This is the connection to the Kryten (the Siri server backend for iOS 6)
#####
class SiriProxy::Connection::Kryten < SiriProxy::Connection
  def initialize
    super
    self.name = "Kryten"
  end
  
  def connection_completed
    super
    start_tls(:verify_peer => false)
  end
  
  def received_object(object)		
    return plugin_manager.process_filters(object, :from_apple)

    #plugin_manager.object_from_apple(object, self)
  end
	
  def block_rest_of_session 
    @block_rest_of_session = true
  end
end