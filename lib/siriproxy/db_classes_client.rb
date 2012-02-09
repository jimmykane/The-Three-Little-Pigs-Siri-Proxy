# -*- encoding : utf-8 -*-
require 'singleton'
require 'siriproxy/db_connection'
class Client

  attr_accessor :id, :fname,:nickname,:appleDBid,:appleAccountid,:valid,:date_added
	
	def id=(value)  # The setter method for @id
		@id =  value
	end

	def fname=(value)  # The setter method for @fname
		@fname =  value
	end

	def nickname=(value)  # The setter method for @nickname
		@nickname =  value
	end

	def appleDBid=(value)  # The setter method for @appleDBid
		@appleDBid =  value
	end

	def appleAccountid=(value)  # The setter method for @load
		@appleAccountid =  value
	end

	def valid=(value)  # The setter method for @valid
		@valid =  value
	end
  def date_added=(value)  # The setter method for @date_added
		@date_added =  value
	end
end

class ClientsDao
	include Singleton
	
	def initialize()
		
	end

	def connect_to_db(my)
		@my = my
	end

	def insert(dto)
		sql = "INSERT INTO `clients` (fname,nickname,apple_db_id,apple_account_id,valid,date_added ) VALUES ( ?  , ? , ? , ?, ?,NOW())"
		st = @my.prepare(sql)		
		st.execute(dto.fname,dto.nickname,dto.appleDBid,dto.appleAccountid,dto.valid)
		st.close
	end

	def update(dto)
    puts "test"
    pp dto
		sql = "UPDATE `clients` SET fname= ? ,nickname=?,apple_db_id=?,apple_account_id=?,valid=? WHERE id = ?"
		st = @my.prepare(sql)
		st.execute(dto.fname,dto.nickname,dto.appleDBid,dto.appleAccountid,dto.valid,dto.id)
		st.close
	end
    
	def delete(dto)
		sql = "DELETE FROM `clients` WHERE id = ?"
		st = @my.prepare(sql)
		st.execute(dto.id)
		st.close
	end

	def find(dto)
		sql = "SELECT * FROM `clients` WHERE id=?"
		st = @my.prepare(sql)
		st.execute(dto.id)
		result = fetchResults(st)
 		st.close
    return result
	end

	

  def listclients()
		sql = "SELECT * FROM `clients` "
		st = @my.prepare(sql)
		st.execute()
		result = fetchResults(st)
 		st.close
    return result
		
	end 

	def check_duplicate(dto)
		sql = "SELECT * FROM `clients` WHERE apple_account_id=?"
		st = @my.prepare(sql)
		st.execute(dto.appleAccountid)
		result = fetchResults(st)
 		st.close
    return result[0]
		
	end


  def fetchResults(stmt)
		rows = []
		while row = stmt.fetch do
			dto = Client.new
			dto.id = row[0]	
			dto.fname=row[1]
			dto.nickname=row[2]
			dto.appleDBid=row[3]
      dto.appleAccountid=row[4]
      dto.valid=row[5]
      dto.date_added=row[6]
			rows << dto
		end

		return rows
	end
end