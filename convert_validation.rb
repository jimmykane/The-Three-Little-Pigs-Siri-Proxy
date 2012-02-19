require 'cfpropertylist'

def plist_blob(string)
    string = [string].pack('H*')    
    #string.blob = true
    string
	end

puts plist_blob("AlJn9pGDWMOuWYujEY8QYf55k5Xem00wcujtWkB0VeU8AAAA4AMAAABJAAAAgF7wwkrkEpmQZvKwj56U66I7OdueST5ZPu+Gcf1iQYFAp9yvXMB24v8rGSR9efubGaDk4WYTMKOhVpfoJhr49z/TJiY4O/VrGyalR8Hao0o3EFiaQplRKG/MvstKszJooFX+2wmRbJD6sNdVhQOx1ebuL4NfVRJCFr4VsKMexWnvAAAAAAAAAE8BJsxxSwPkTHY7gMNlSi7P0j25WwIAAAA2CAY0Oq7hu0+b7mM18A6NAJfoTz7K2XSDROOkRF6KcPdZ8lEc0tAhU06qt3lb1CsLAmeI3YDN")
