# -*- encoding: utf-8 -*-
$:.push File.expand_path("../lib", __FILE__)

Gem::Specification.new do |s|
  s.name        = "siriproxy-example"
  s.version     = "0.9.13" 
  s.authors     = ["plamoni, jimmykane, thpryrchn"]
  s.email       = [""]
  s.homepage    = ""
  s.summary     = %q{An Example Siri Proxy Plugin}
  s.description = %q{This is a "hello world" style plugin. It simply intercepts the phrase "text siri proxy" and responds with a message about the proxy being up and running. This is good base code for other plugins. }

  s.rubyforge_project = "siriproxy-example"

  s.files         = `git ls-files 2> /dev/null`.split("\n")
  s.test_files    = `git ls-files -- {test,spec,features}/* 2> /dev/null`.split("\n")
  s.executables   = `git ls-files -- bin/* 2> /dev/null`.split("\n").map{ |f| File.basename(f) }
  s.require_paths = ["lib"]

  # specify any dependencies here; for example:
  # s.add_development_dependency "rspec"
  # s.add_runtime_dependency "rest-client"
end
