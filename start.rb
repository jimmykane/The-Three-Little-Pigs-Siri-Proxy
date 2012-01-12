# -*- encoding : utf-8 -*-
#!/usr/bin/env ruby
#require 'plugins/thermostat/siriThermostat'
#require_relative 'plugins/testproxy/testproxy'
#require 'plugins/eliza/eliza'
require_relative 'tweakSiri'
require_relative 'siriProxy'

#Also try Eliza -- though it should really not be run "before" anything else.
#PLUGINS = [TestProxy]

proxy = SiriProxy.new()

#that's it. :-)
