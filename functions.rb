

def get_os
  os= Cfruby::OS::OSFactory.get_os()
  return os
end