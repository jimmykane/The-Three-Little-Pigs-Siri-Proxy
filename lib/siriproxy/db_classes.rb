# -*- encoding : utf-8 -*-
require 'singleton'
require 'siriproxy/db_connection'
class ConfigProxy
  include Singleton 
	attr_accessor :id, :max_threads,:max_connections,:active_connections,:max_keyload,:keyload_dropdown,:keyload_dropdown_interval
  def id=(value)  # The setter method for @id
		@id =  value
	end

	def max_threads=(value)  
		@max_threads =  value
	end

	def max_connections=(value)  
		@max_connections =  value
	end

	def active_connections=(value)  
		@active_connections =  value
	end
  
  def max_keyload=(value)  
		@max_keyload =  value
	end
  
  def keyload_dropdown=(value)  
		@keyload_dropdown =  value
	end
  
  def keyload_dropdown_interval=(value)  
		@keyload_dropdown_interval =  value
	end
  
end

class ConfigDao
	include Singleton
	
	def initialize()		
	end
	def connect_to_db(my)
		@my = my
	end
  def getsettings
    sql = "SELECT * FROM `config` "
    st = @my.prepare(sql)
    st.execute()
    result = fetchResults(st)
		st.close    
    return result[0]
  end
	def update(dto)
		sql = "UPDATE `config` SET max_threads = ?,max_connections= ? ,active_connections=?, max_keyload=?,keyload_dropdown=?,keyload_dropdown_interval=? WHERE id=1"
		st = @my.prepare(sql)
		st.execute(dto.max_threads,dto.max_connections,dto.active_connections,dto.max_keyload,dto.keyload_dropdown,dto.keyload_dropdown_interval)
    
		st.close
   
	end
  def fetchResults(stmt)
		rows = []
		while row = stmt.fetch do
      dto = ConfigProxy.instance
      dto.id = row[0]
			dto.max_threads= row[1]
			dto.max_connections=row[2]
			dto.active_connections=row[3]		
      dto.max_keyload=row[4]	
      dto.keyload_dropdown=row[5]	
      dto.keyload_dropdown_interval=row[6]	
			rows << dto
		end
		return rows
	end

end



class Key  

  attr_accessor :id, :assistantid,:speechid,:speechid,:expired,:sessionValidation,:keyload,:date_added,:availablekeys
	
	def id=(value)  # The setter method for @id
		@id =  value
	end

	def assistantid=(value)  # The setter method for @assistantid
		@assistantid =  value
	end

	def speechid=(value)  # The setter method for @speechid
		@speechid =  value
	end

	def sessionValidation=(value)  # The setter method for @sessionValidation
		@sessionValidation =  value
	end

	def expired=(value)  # The setter method for @expired
		@expired =  value
	end

	def keyload=(value)  # The setter method for @load
		@keyload =  value
	end

	def date_added=(value)  # The setter method for @date_added
		@date_added =  value
	end
  def availablekeys=(value)  # The setter method for @date_added
		@availablekeys =  value
	end
end



class Key4S < Key
  include Singleton 

end

class PublicKey < Key
  include Singleton 

end

#Dao class for keys
class KeyDao
	include Singleton
	
	def initialize()
		
	end

	def connect_to_db(my)
		@my = my
	end

	def insert(dto)
		sql = "INSERT INTO `keys` (assistantid,speechid,sessionValidation,expired,date_added ) VALUES ( ? ,  ?  , ? , ?,NOW())"
		st = @my.prepare(sql)		
		st.execute(dto.assistantid,dto.speechid,dto.sessionValidation,dto.expired)
		st.close
	end

	def update(dto)
		sql = "UPDATE `keys` SET assistantid = ?,speechid= ? ,sessionValidation=?,expired=?,keyload=? WHERE id = ?"
		st = @my.prepare(sql)
		st.execute(dto.assistantid,dto.speechid,dto.sessionValidation,dto.expired,dto.keyload,dto.id)
		st.close
	end
  
  def setkeyload(dto)
		sql = "UPDATE `keys` SET keyload=? WHERE id = ?"
		st = @my.prepare(sql)
		st.execute(dto.keyload,dto.id)
		st.close
	end

	def delete(dto)
		sql = "DELETE FROM `keys` WHERE id = ?"
		st = @my.prepare(sql)
		st.execute(dto.id)
		st.close
	end

	def find(dto)
		sql = "SELECT * FROM `keys` WHERE id=?"
		st = @my.prepare(sql)
		st.execute(dto.id)
		result = fetchResults(st)
 		st.close
    return result
	end

	def validation_expired(dto)				
		sql = "UPDATE `keys` SET expired='True' WHERE id = ?"
		st = @my.prepare(sql)
		st.execute(dto.id)
		st.close		
	end

  def listkeys()
		sql = "SELECT * FROM `keys` WHERE expired!='True' AND keyload < (SELECT max_keyload FROM `config` WHERE id=1) ORDER by keyload ASC"
		st = @my.prepare(sql)
		st.execute()
		result = fetchResults(st)
 		st.close
    return result
		
	end
  
  def findoverloaded()    
    sql = "SELECT * FROM `keys` WHERE expired!='True' AND keyload >(SELECT keyload_dropdown FROM `config` WHERE id=1) ORDER by keyload DESC"
		st = @my.prepare(sql)
		st.execute()
		result = fetchResults(st)
 		st.close
    return result 
  end

  
	def next_available()
		sql = "SELECT * FROM `keys` WHERE expired!='True' AND keyload<(SELECT max_keyload FROM `config` WHERE id=1) ORDER by keyload ASC LIMIT 1"
		st = @my.prepare(sql)
		st.execute()
		result = fetchResults(st)    
 		st.close
    return result[0]
		
	end

	def check_duplicate(dto)
		sql = "SELECT * FROM `keys` WHERE sessionValidation=?"
		st = @my.prepare(sql)
		st.execute(dto.sessionValidation)
		result = fetchResults(st)
 		st.close
    return result[0]
		
	end


  def fetchResults(stmt)
		rows = []

		while row = stmt.fetch do
			dto = Key.new
			dto.id = row[0]
			dto.assistantid= row[1]
			dto.speechid=row[2]
			dto.sessionValidation=row[3]
			dto.expired=row[4]
      dto.keyload=row[5]
			rows << dto
		end

		return rows
	end
end

class Assistant
    attr_accessor :id, :key_id,:assistantid,:speechid,:date_created
    def id=(value)  # The setter method for @id
      @id =  value
    end
    def key_id=(value)  # The setter method for @key_id
      @key_id =  value
    end
    def assistantid=(value)  # The setter method for @assistantid
      @assistantid =  value
    end
    def speechid=(value)  # The setter method for @speechid
      @speechid =  value
    end
    def date_created=(value)  # The setter method for @date_created
      @date_created =  value
    end
  end

  class AssistantDao

    include Singleton
	
    def initialize()	
      
    end
    
    def connect_to_db(my)
      @my = my
    end
    
    def getkeyassistants(dto)
      sql = "SELECT * FROM `assistants` WHERE key_id=?"
      st = @my.prepare(sql)
      st.execute(dto.id)
      result = fetchResults(st)
      st.close    
      return result[0]
    end
    
    def check_duplicate(dto)
      sql = "SELECT * FROM `assistants` WHERE assistantid=?"
      st = @my.prepare(sql)
      st.execute(dto.assistantid)
      result = fetchResults(st)
      st.close
      return result[0]		
    end
    
    def createassistant(dto)
      sql = "INSERT INTO `assistants` (key_id,assistantid,speechid,date_created) VALUES ( ? , ? , ? , NOW())"
      st = @my.prepare(sql)
      st.execute(dto.key_id,dto.assistantid,dto.speechid)   
      st.close    
    end
    
    def fetchResults(stmt)
      rows = []
      while row = stmt.fetch do
        dto = Assistant.new
        dto.id = row[0]
        dto.key_id= row[1]
        dto.assistantid=row[2]
        dto.speechid=row[3]		
        dto.date_added=row[4]	      
        rows << dto
      end
      return rows
    end

  end