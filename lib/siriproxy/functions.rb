# -*- encoding : utf-8 -*-
require 'pony'  
#send email function
def sendemail()
  #Lets also send an email comming soon
  if $APP_CONFIG.send_email=='ON' or $APP_CONFIG.send_email=='on'
    begin
      Pony.mail(
        :to => $APP_CONFIG.email_to, 
        :from => $APP_CONFIG.email_from,
        :subject => $APP_CONFIG.email_subject,
        :html_body => $APP_CONFIG.email_message
      )
      puts "[Email - SiriProxy] Expired key email sent to [#{$APP_CONFIG.email_to}]"
    rescue 
      puts "[Email - SiriProxy] Warning Cannot send mail. Check your ~/.siriproxy/config.yml"            
    end
  end        
  #Done with email
end
  