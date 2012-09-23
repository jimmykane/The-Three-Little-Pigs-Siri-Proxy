require 'cfpropertylist'

def plist_blob(string)
  string = [string].pack('H*')
  string.blob = true
  string
end
