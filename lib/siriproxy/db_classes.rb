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

    attr_accessor :id, :assistantid,:speechid,:speechid,:expired,:sessionValidation,:keyload,:date_added,:availablekeys,:banned,:iPad3

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
    def banned=(value)  # The setter method for @banned
      @banned =  value
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
    def iPad3=(value)  # The setter method for @iPad3
      @iPad3 =  value
    end
  end



  class Key4S < Key
    include Singleton

  end
  class KeyiPad3 < Key
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
      sql = "INSERT INTO `keys` (assistantid,speechid,sessionValidation,banned,expired,iPad3,date_added ) VALUES ( ? ,  ?  , ? , ? , ? , ? ,NOW())"
      st = @my.prepare(sql)
      st.execute(dto.assistantid,dto.speechid,dto.sessionValidation,dto.banned,dto.expired,dto.iPad3)
      st.close
    end

    def update(dto)
      sql = "UPDATE `keys` SET assistantid = ?,speechid= ? ,sessionValidation=?,banned=?,expired=?,keyload=?,iPad3=? WHERE id = ?"
      st = @my.prepare(sql)
      st.execute(dto.assistantid,dto.speechid,dto.sessionValidation,dto.banned,dto.expired,dto.keyload,dto.id,dto.iPad3)
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

    def validation_valid(dto)
      sql = "UPDATE `keys` SET expired='False' WHERE id = ?"
      st = @my.prepare(sql)
      st.execute(dto.id)
      st.close
    end

    def expire_hour_keys()
      sql = "UPDATE `keys` SET expired='TRUE'  WHERE date_added < NOW() -  INTERVAL #{$APP_CONFIG.hours_till_key_expires} HOUR"
      st = @my.prepare(sql)
      st.execute()
      result = st.affected_rows
      st.close
      return result

    end

    def key_banned(dto)
      sql = "UPDATE `keys` SET banned='True' WHERE id = ?"
      st = @my.prepare(sql)
      st.execute(dto.id)
      st.close
    end

    def unban_keys()
      sql = "UPDATE `keys` SET banned='False' WHERE expired='False'"
      st = @my.prepare(sql)
      st.execute()
      st.close
    end
    def ban_keys()
      sql = "UPDATE `keys` SET banned='True' WHERE expired='False'"
      st = @my.prepare(sql)
      st.execute()
      st.close
    end
    def list4Skeys()
      sql = "SELECT * FROM `keys` WHERE expired!='True' AND iPad3!='True' AND keyload < (SELECT max_keyload FROM `config` WHERE id=1) ORDER by keyload ASC"
      st = @my.prepare(sql)
      st.execute()
      result = fetchResults(st)
      st.close
      return result
    end
    def listiPad3keys()
      sql = "SELECT * FROM `keys` WHERE expired!='True' AND iPad3='True' AND keyload < (SELECT max_keyload FROM `config` WHERE id=1) ORDER by keyload ASC"
      st = @my.prepare(sql)
      st.execute()
      result = fetchResults(st)
      st.close
      return result
    end

    def list_4S_keys_for_new_assistant()
      sql = "SELECT * FROM `keys` WHERE expired!='True' AND banned!='True' AND iPad3!='True' AND keyload < (SELECT max_keyload FROM `config` WHERE id=1) ORDER by keyload ASC"
      st = @my.prepare(sql)
      st.execute()
      result = fetchResults(st)
      st.close
      return result
    end
    def list_iPad3_keys_for_new_assistant()
      sql = "SELECT * FROM `keys` WHERE expired!='True' AND banned!='True' AND iPad3='True' AND keyload < (SELECT max_keyload FROM `config` WHERE id=1) ORDER by keyload ASC"
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


    def next_available_4S()
      sql = "SELECT * FROM `keys` WHERE expired!='True' AND iPad3!='True' AND keyload<(SELECT max_keyload FROM `config` WHERE id=1) ORDER by keyload ASC LIMIT 1"
      st = @my.prepare(sql)
      st.execute()
      result = fetchResults(st)
      st.close
      return result[0]

    end

    def next_available_iPad3()
      sql = "SELECT * FROM `keys` WHERE expired!='True' AND iPad3='True' AND keyload<(SELECT max_keyload FROM `config` WHERE id=1) ORDER by keyload ASC LIMIT 1"
      st = @my.prepare(sql)
      st.execute()
      result = fetchResults(st)
      st.close
      return result[0]

    end

    def next_available_4S_for_new_assistant() #we will need the outer join here
      sql = "SELECT K.*, Count(1) FROM `keys` K
 LEFT OUTER JOIN `assistants` A ON A.key_id = K.id  WHERE K.expired='FALSE'  AND K.banned='False' AND K.iPad3='False'  AND K.keyload <(SELECT max_keyload FROM `config` WHERE id=1)
GROUP BY K.id ORDER BY K.keyload,Count(1) ASC LIMIT 1"
      st = @my.prepare(sql)
      st.execute()
      result = fetchResults(st)
      st.close
      return result[0]
    end
    def next_available_iPad3_for_new_assistant() #we will need the outer join here
      sql = "SELECT K.*, Count(1) FROM `keys` K
 LEFT OUTER JOIN `assistants` A ON A.key_id = K.id  WHERE K.expired='FALSE'   AND K.banned='False' AND K.iPad3='True'  AND K.keyload<(SELECT max_keyload FROM `config` WHERE id=1)
GROUP BY K.id ORDER BY K.keyload,Count(1) ASC LIMIT 1"
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
          dto.banned=row[4]
          dto.expired=row[5]
          dto.keyload=row[6]
          dto.date_added=row[7]
          dto.iPad3=row[8]
          rows << dto
        end

        return rows
      end
    end

    class Assistant
      attr_accessor :id, :key_id,:client_apple_account_id,:assistantid,:speechid,:devicetype,:deviceOS,:date_created,:last_login,:last_ip
      def id=(value)  # The setter method for @id
        @id =  value
      end
      def key_id=(value)  # The setter method for @key_id
        @key_id =  value
      end
      def client_apple_account_id=(value)  # The setter method for @client_apple_account_id
        @client_apple_account_id =  value
      end
      def assistantid=(value)  # The setter method for @assistantid
        @assistantid =  value
      end
      def speechid=(value)  # The setter method for @speechid
        @speechid =  value
      end
      def devicetype=(value)  # The setter method for @devicetype
        @devicetype =  value
      end
      def deviceOS=(value)  # The setter method for @deviceOS
        @deviceOS =  value
      end
      def date_created=(value)  # The setter method for @date_created
        @date_created =  value
      end
      def last_login=(value)  # The setter method for @last_login
        @last_login =  value
      end
      def last_ip=(value)  # The setter method for @last_ip
        @last_ip =  value
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
        sql = "INSERT INTO `assistants` (key_id,client_apple_account_id,assistantid,speechid,device_type,device_OS,date_created,last_login,last_ip) VALUES ( ? ,? , ? , ? , ? , ? ,NOW(), NOW() , ? )"
        st = @my.prepare(sql)
        st.execute(dto.key_id,dto.client_apple_account_id,dto.assistantid,dto.speechid,dto.devicetype,dto.deviceOS,dto.last_ip)
        st.close
      end

      def updateassistant(dto)
        sql = "UPDATE `assistants` SET last_login=NOW(), last_ip=? WHERE id=?"
        st = @my.prepare(sql)
        st.execute(dto.last_ip,dto.id)
        st.close
      end

      def delete_expired_assistants
        sql = "DELETE FROM `assistants` WHERE date_created < NOW() -  INTERVAL 14 DAY"
        st = @my.prepare(sql)
        st.execute()
        st.close
      end

      def fetchResults(stmt)
        rows = []
        while row = stmt.fetch do
            dto = Assistant.new
            dto.id = row[0]
            dto.key_id= row[1]
            dto.client_apple_account_id=row[2]
            dto.assistantid=row[3]
            dto.speechid=row[4]
            dto.devicetype=row[5]		
            dto.deviceOS=row[6]
            dto.date_created=row[7]	   
            dto.last_login=row[8]	   
            dto.last_ip=row[9]
            rows << dto
          end
          return rows
        end

      end
      #added stats fixes crash with interval
      class Statistics
        attr_accessor :id, :elapsed,:uptime,:happy_hour_elapsed

        def id=(value)  # The setter method for @id
          @id =  value
        end

        def elapsed=(value)  # The setter method for @elapsedkeycheck
          @elapsed =  value
        end

        def happy_hour_elapsed=(value)  # The setter method for @uptime
          @happy_hour_elapsed =  value
        end

        def uptime=(value)  # The setter method for @uptime
          @uptime =  value
        end

      end

      class StatisticsDao

        include Singleton

        def initialize()

        end

        def connect_to_db(my)
          @my = my
        end

        def getstats()
          sql = "SELECT * FROM `stats` WHERE id=1"
          st = @my.prepare(sql)
          st.execute()
          result = fetchResults(st)
          st.close
          return result[0]
        end

        def savestats(dto)
          sql = "UPDATE `stats` SET elapsed_key_check_interval=?,up_time=?,happy_hour_elapsed=? WHERE id=1"
          st = @my.prepare(sql)
          st.execute(dto.elapsed,dto.uptime,dto.happy_hour_elapsed)
          st.close
        end

        def fetchResults(stmt)
          rows = []
          while row = stmt.fetch do
              dto = Statistics.new
              dto.id = row[0]
              dto.elapsed= row[1]
              dto.uptime=row[2]
              dto.happy_hour_elapsed=row[3]
              rows << dto
            end
            return rows
          end
        end


        class KeyStatistics
          attr_accessor :id, :key_id,:total_finishspeech_requests,:total_tokens_recieved

          def id=(value)  # The setter method for @id
            @id =  value
          end

          def key_id=(value)  # The setter method for @key_id
            @key_id =  value
          end

          def total_finishspeech_requests=(value)  # The setter method for @uptime
            @total_finishspeech_requests =  value
          end

          def total_tokens_recieved=(value)  # The setter method for @uptime
            @total_tokens_recieved =  value
          end

        end

        class KeyStatisticsDao

          include Singleton

          def initialize()

          end

          def connect_to_db(my)
            @my = my
          end

          def insert(dto)
            sql = "INSERT INTO `key_stats` (key_id,total_finishspeech_requests,total_tokens_recieved) VALUES (? , 0 , 0) "
            st = @my.prepare(sql)
            st.execute(dto.id)
            st.close
          end


          def get_key_stats(dto)
            sql = "SELECT * FROM `key_stats` WHERE key_id=?"
            st = @my.prepare(sql)
            st.execute(dto.id)
            result = fetchResults(st)
            st.close
            return result[0]
          end

          def save_key_stats(dto)
            sql = "UPDATE `key_stats` SET total_finishspeech_requests=?,total_tokens_recieved=?  WHERE id=?"
            st = @my.prepare(sql)
            st.execute(dto.total_finishspeech_requests,dto.total_tokens_recieved,dto.id)
            st.close
          end

          def delete_keystats()
            sql = "DELETE FROM key_stats WHERE key_id  IN (SELECT id FROM `keys` WHERE expired='True')"
            st = @my.prepare(sql)
            st.execute()
            st.close
          end

          def fetchResults(stmt)
            rows = []
            while row = stmt.fetch do
                dto = KeyStatistics.new
                dto.id = row[0]
                dto.key_id= row[1]
                dto.total_finishspeech_requests=row[2]
                dto.total_tokens_recieved=row[3]
                rows << dto
              end
              return rows
            end
          end


          class ActivationToken
            attr_accessor :id,:aceid,:data

            def id=(value)  # The setter method for @id
              @id =  value
            end

            def aceid=(value)  # The setter method for @refid
              @aceid =  value
            end

            def data=(value)  # The setter method for @data
              @data =  value
            end


          end
