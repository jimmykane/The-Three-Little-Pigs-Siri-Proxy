#####
# This is the connection to Apple
#####
class SiriProxy::Connection::Apple < SiriProxy::Connection
  def initialize
    super
    self.name = "Guzzoni"
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
