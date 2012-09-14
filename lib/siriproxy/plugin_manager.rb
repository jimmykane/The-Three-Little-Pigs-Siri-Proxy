require 'cora'
require 'pp'

class SiriProxy::PluginManager < Cora
  attr_accessor :plugins, :iphone_conn, :apple_conn, :user_assistant, :user_appleid, :user_fname, :user_nickname, :user_language, :user_devicetype, :user_deviceOS, :user_lastIP, :user_last_login, :person
  def initialize()
    @person = ""
    load_plugins()
  end

  def load_plugins()
    @plugins = []
    if $APP_CONFIG.plugins
      $APP_CONFIG.plugins.each do |pluginConfig|
        if pluginConfig.is_a? String
          className = pluginConfig
          requireName = "siriproxy-#{className.downcase}"
        else
          className = pluginConfig['name']
          requireName = pluginConfig['require'] || "siriproxy-#{className.downcase}"
        end
        require requireName
        plugin = SiriProxy::Plugin.const_get(className).new(pluginConfig)
        plugin.manager = self
        @plugins << plugin
      end
    end
    log "Plugins loaded: #{@plugins}"
  end

  def process_filters(object, direction)
    object_class = object.class #This way, if we change the object class we won't need to modify this code.

    if object['class'] == 'SetRequestOrigin'
      properties = object['properties']
      set_location(properties['latitude'], properties['longitude'], properties)
    end
    if object['class'] == 'PersonSearchCompleted'
      for x in (0..object["properties"]["results"].length)
        @person = object["properties"]["results"][x]["properties"]
      end
    end

    plugins.each do |plugin|
      #log "Processing filters on #{plugin} for '#{object["class"]}'"
      new_obj = plugin.process_filters(object, direction)
      object = new_obj if(new_obj == false || new_obj.class == object_class) #prevent accidental poorly formed returns
      return nil if object == false #if any filter returns "false," then the object should be dropped
    end
    #Often this indicates a bug in OUR code. So let's not send it to Apple. :-)

    if((object["class"] == "CommandIgnored")&&(direction==:from_iphone))
      puts "Maybe a Bug"
      pp object
      return nil
    end

    return object
  end

  def process(text)
    begin
      result = super(text)
      self.apple_conn.block_rest_of_session if result
      return result
    rescue Exception=>e
      respond e.to_s, spoken: "Oh no! A plugin crashed:"
      log "Plugin Crashed: #{e}"
      return true
    end
  end

  def send_request_complete_to_iphone
    log "Sending Request Completed"
    object = generate_request_completed(self.apple_conn.last_ref_id)
    self.apple_conn.inject_object_to_output_stream(object)
  end

  def respond(text, options={})
    self.apple_conn.inject_object_to_output_stream(generate_siri_utterance(self.apple_conn.last_ref_id, text, (options[:spoken] or text), options[:prompt_for_response] == true))
  end

  def no_matches
    return false
  end

  def log(text)
    puts "[Info - Plugin Manager] #{text}" if $LOG_LEVEL >= 1
  end
end
