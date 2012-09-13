require 'rubygems'
require 'uuidtools'

def generate_siri_utterance(ref_id, text, speakableText=text, listenAfterSpeaking=false)
  object = SiriAddViews.new
  object.make_root(ref_id)
  object.views << SiriAssistantUtteranceView.new(text, speakableText, "Misc#ident", listenAfterSpeaking)
  return object.to_hash
end

def generate_request_completed(ref_id, callbacks=nil)
  object = SiriRequestCompleted.new()
  object.callbacks = callbacks if callbacks != nil
  object.make_root(ref_id)
  return object.to_hash
end

class SiriObject
  attr_accessor :klass, :group, :properties

  def initialize(klass, group)
    @klass = klass
    @group = group
    @properties = {}
  end

  #watch out for circular references!
  def to_hash
    hash = {
      "class" => self.klass,
      "group" => self.group,
      "properties" => {}
    }

    (hash["refId"] = ref_id) rescue nil
    (hash["aceId"] = ace_id) rescue nil

    properties.each_key { |key|
      if properties[key].class == Array
        hash["properties"][key] = []
        self.properties[key].each { |val| hash["properties"][key] << (val.to_hash rescue val) }
      else
        hash["properties"][key] = (properties[key].to_hash rescue properties[key])
      end
    }

    hash
  end

  def make_root(ref_id=nil, ace_id=nil)
    self.extend(SiriRootObject)

    self.ref_id = (ref_id || random_ref_id)
    self.ace_id = (ace_id || random_ace_id)
  end
end

def add_property_to_class(klass, prop)
  klass.send(:define_method, (prop.to_s + "=").to_sym) { |value|
    self.properties[prop.to_s] = value
  }

  klass.send(:define_method, prop.to_s.to_sym) {
    self.properties[prop.to_s]
  }
end

module SiriRootObject
  attr_accessor :ref_id, :ace_id

  def random_ref_id
    UUIDTools::UUID.random_create.to_s.upcase
  end

  def random_ace_id
    UUIDTools::UUID.random_create.to_s
  end
end

class SiriAddViews < SiriObject
  def initialize(scrollToTop=false, temporary=false, dialogPhase="Completion", views=[])
    super("AddViews", "com.apple.ace.assistant")
    self.scrollToTop = scrollToTop
    self.views = views
    self.temporary = temporary
    self.dialogPhase = dialogPhase
  end
end
add_property_to_class(SiriAddViews, :scrollToTop)
add_property_to_class(SiriAddViews, :views)
add_property_to_class(SiriAddViews, :temporary)
add_property_to_class(SiriAddViews, :dialogPhase)

#####
# VIEWS
#####

class SiriAssistantUtteranceView < SiriObject
  def initialize(text="", speakableText=text, dialogIdentifier="Misc#ident", listenAfterSpeaking=false)
    super("AssistantUtteranceView", "com.apple.ace.assistant")
    self.text = text
    self.speakableText = speakableText
    self.dialogIdentifier = dialogIdentifier
    self.listenAfterSpeaking = listenAfterSpeaking
  end
end
add_property_to_class(SiriAssistantUtteranceView, :text)
add_property_to_class(SiriAssistantUtteranceView, :speakableText)
add_property_to_class(SiriAssistantUtteranceView, :dialogIdentifier)
add_property_to_class(SiriAssistantUtteranceView, :listenAfterSpeaking)

#####
# SNIPPETS
#####

class SiriMapItemSnippet < SiriObject
  def initialize(userCurrentLocation=true, items=[])
    super("MapItemSnippet", "com.apple.ace.localsearch")
    self.userCurrentLocation = userCurrentLocation
    self.items = items
  end
end
add_property_to_class(SiriMapItemSnippet, :userCurrentLocation)
add_property_to_class(SiriMapItemSnippet, :items)

class SiriAnswerSnippet < SiriObject
  def initialize(answers=[], confirmationOptions=nil)
    super("Snippet", "com.apple.ace.answer")
    self.answers = answers

    if confirmationOptions
      # need to figure out good way to do API for this
      self.confirmationOptions = confirmationOptions
    end

  end
end
add_property_to_class(SiriAnswerSnippet, :answers)
add_property_to_class(SiriAnswerSnippet, :confirmationOptions)

class SiriPersonSnippet < SiriObject
  def initialize(persons=[])
    super("PersonSnippet", "com.apple.ace.contact")
    self.persons = persons
  end
end
add_property_to_class(SiriPersonSnippet, :persons)

class SiriForecastSnippet < SiriObject
  def initialize(aceWeathers=[])
    super("ForecastSnippet", "com.apple.ace.weather")
    self.aceWeathers = aceWeathers
  end
end
add_property_to_class(SiriForecastSnippet, :aceWeathers)

#####
# COMMANDS
#####

class SiriSendCommands < SiriObject
  def initialize(commands=[])
    super("SendCommands", "com.apple.ace.system")
    self.commands=commands
  end
end
add_property_to_class(SiriSendCommands, :commands)

    class SiriConfirmSnippetCommand < SiriObject
      def initialize(request_id = "")
        super("ConfirmSnippet", "com.apple.ace.assistant")
        self.request_id = request_id
      end
    end
    add_property_to_class(SiriConfirmSnippetCommand, :request_id)

    class SiriCancelSnippetCommand < SiriObject
      def initialize(request_id = "")
        super("CancelSnippet", "com.apple.ace.assistant")
        self.request_id = request_id
      end
    end
    add_property_to_class(SiriCancelSnippetCommand, :request_id)

    class SiriSnippetOpenedCommand < SiriObject
      def initialize(request_id = "", object=SOME_KIND_OF_ITEM)
        super("SnippetOpened", "com.apple.ace.assistant")
        self.request_id = request_id
        self.object = object
      end
    end
    add_property_to_class(SiriSnippetOpenedCommand, :request_id)
    add_property_to_class(SiriSnippetOpenedCommand, :object)

    class SiriSnippetAttributeOpenedCommand < SiriObject
      def initialize(request_id = "", attributeValue="", attributeName="")
        super("SnippetAttributeOpened", "com.apple.ace.assistant")
        self.request_id = request_id
        self.attributeValue = attributeValue
        self.attributeName = attributeName
      end
    end
    add_property_to_class(SiriSnippetAttributeOpenedCommand, :request_id)
    add_property_to_class(SiriSnippetAttributeOpenedCommand, :attributeValue)
    add_property_to_class(SiriSnippetAttributeOpenedCommand, :attributeName)
    
#####
# OBJECTS
#####

class SiriButton < SiriObject
  def initialize(text="Button Text", commands=[])
    super("Button", "com.apple.ace.assistant")
    self.text = text
    self.commands = commands
  end
end
add_property_to_class(SiriButton, :text)
add_property_to_class(SiriButton, :commands)

class SiriBusinessReview < SiriObject
  def initialize()
    super("Review", "com.apple.ace.localsearch")
  end
end

class SiriBusinessPhoneNumber < SiriObject
  def initialize(value="+14088993800", type="PRIMARY")
    super("PhoneNumber", "com.apple.ace.localsearch")
    self.value = value
    self.type = type
  end
end
add_property_to_class(SiriBusinessPhoneNumber, :value)
add_property_to_class(SiriBusinessPhoneNumber, :type)

class SiriBusinessRating < SiriObject
  def initialize(value=100.0, count=0)
    super("Rating", "com.apple.ace.localsearch")
    self.value = value
    self.count = count
  end
end
add_property_to_class(SiriBusinessRating, :value)
add_property_to_class(SiriBusinessRating, :count)

class SiriConfirmationOptions < SiriObject
  def initialize(submitCommands=[], cancelCommands=[], denyCommands=[], confirmCommands=[], denyText="Cancel", cancelLabel="Cancel", submitLabel="Send", confirmText="Send", cancelTrigger="Deny")
    super("ConfirmationOptions", "com.apple.ace.assistant")

    self.submitCommands = submitCommands
    self.cancelCommands = cancelCommands
    self.denyCommands = denyCommands
    self.confirmCommands = confirmCommands

    self.denyText = denyText
    self.cancelLabel = cancelLabel
    self.submitLabel = submitLabel
    self.confirmText = confirmText
    self.cancelTrigger = cancelTrigger
  end
end
add_property_to_class(SiriConfirmationOptions, :submitCommands)
add_property_to_class(SiriConfirmationOptions, :cancelCommands)
add_property_to_class(SiriConfirmationOptions, :denyCommands)
add_property_to_class(SiriConfirmationOptions, :confirmCommands)
add_property_to_class(SiriConfirmationOptions, :denyText)
add_property_to_class(SiriConfirmationOptions, :cancelLabel)
add_property_to_class(SiriConfirmationOptions, :submitLabel)
add_property_to_class(SiriConfirmationOptions, :confirmText)
add_property_to_class(SiriConfirmationOptions, :cancelTrigger)

class SiriLocation < SiriObject
  def initialize(label="Apple", street="1 Infinite Loop", city="Cupertino", stateCode="CA", countryCode="US", postalCode="95014", latitude=37.3317031860352, longitude=-122.030089795589)
    super("Location", "com.apple.ace.system")
    self.label = label
    self.street = street
    self.city = city
    self.stateCode = stateCode
    self.countryCode = countryCode
    self.postalCode = postalCode
    self.latitude = latitude
    self.longitude = longitude
  end
end
add_property_to_class(SiriLocation, :label)
add_property_to_class(SiriLocation, :street)
add_property_to_class(SiriLocation, :city)
add_property_to_class(SiriLocation, :stateCode)
add_property_to_class(SiriLocation, :countryCode)
add_property_to_class(SiriLocation, :postalCode)
add_property_to_class(SiriLocation, :latitude)
add_property_to_class(SiriLocation, :longitude)

class SiriContact < SiriObject
  def initialize(lastName="Jobs", lastNamePhonetic="", firstName="Steve", firstNamePhonetic="", middleName="", nickName="", suffix="", prefix="", fullName="Steve Jobs", relatedNames=[], addresses=[], emails=[], identifier="", birthday=1955-02-24, phones=[], company="Apple Inc.", me=false)
    super("Location", "com.apple.ace.system")
    self.lastName = lastName
    self.lastNamePhonetic = lastNamePhonetic
    self.firstName = firstName
    self.firstNamePhonetic = firstNamePhonetic
    self.middleName = middleName
    self.nickName = nickName
    self.suffix = suffix
    self.prefix = prefix
    self.fullName = fullName
    self.relatedNames = relatedNames
    self.addresses = addresses
    self.emails = emails
    self.identifier = identifier
    self.birthday = birthday
    self.phones = phones
    self.company = company
    self.me = me
  end
end
add_property_to_class(SiriContact, :lastName)
add_property_to_class(SiriContact, :lastNamePhonetic)
add_property_to_class(SiriContact, :firstName)
add_property_to_class(SiriContact, :firstNamePhonetic)
add_property_to_class(SiriContact, :middleName)
add_property_to_class(SiriContact, :nickName)
add_property_to_class(SiriContact, :suffix)
add_property_to_class(SiriContact, :prefix)
add_property_to_class(SiriContact, :fullName)
add_property_to_class(SiriContact, :relatedNames)
add_property_to_class(SiriContact, :addresses)
add_property_to_class(SiriContact, :emails)
add_property_to_class(SiriContact, :identifier)
add_property_to_class(SiriContact, :birthday)
add_property_to_class(SiriContact, :phones)
add_property_to_class(SiriContact, :company)
add_property_to_class(SiriContact, :me)

class SiriContactRelatedNames < SiriObject
  def initialize(label="", name="")
    super("RelatedName", "com.apple.ace.system")
    self.label = label
    self.name = name
  end
end
add_property_to_class(SiriContactRelatedNames, :label)
add_property_to_class(SiriContactRelatedNames, :name)

class SiriContactAddresses < SiriObject
  def initialize(label="", street="", city="", stateCode="", postalCode="")
    super("Location", "com.apple.ace.system")
    self.label = label
    self.street = street
    self.city = city
    self.stateCode = stateCode
    self.postalCode = postalCode
  end
end
add_property_to_class(SiriContactAddresses, :label)
add_property_to_class(SiriContactAddresses, :street)
add_property_to_class(SiriContactAddresses, :city)
add_property_to_class(SiriContactAddresses, :stateCode)
add_property_to_class(SiriContactAddresses, :postalCode)

class SiriContactEmails < SiriObject
  def initialize(label="", emailAddress="")
    super("Email", "com.apple.ace.system")
    self.label = label
    self.emailAddress = emailAddress
  end
end
add_property_to_class(SiriContactEmails, :label)
add_property_to_class(SiriContactEmails, :emailAddress)

class SiriContactPhones < SiriObject
  def initialize(label="", number="")
    super("Phone", "com.apple.ace.system")
    self.label = label
    self.number = number
  end
end
add_property_to_class(SiriContactPhones, :label)
add_property_to_class(SiriContactPhones, :number)

class SiriDisambiguationList < SiriObject
  def initialize(items=[], speakableSelectionResponse="OK\u2026", listenAfterSpeaking=true, speakableText="", speakableFinalDelimiter=", or,", speakableDelimiter=", ", selectionResponse="OK\u2026")
    super("DisambiguationList", "com.apple.ace.assistant")
    self.items = items
    self.speakableSelectionResponse = speakableSelectionResponse
    self.listenAfterSpeaking = listenAfterSpeaking
    self.speakableText = speakableText
    self.speakableFinalDelimiter = speakableFinalDelimiter
    self.speakableDelimiter = speakableDelimiter
    self.selectionResponse = selectionResponse
  end
end
add_property_to_class(SiriDisambiguationList, :items)
add_property_to_class(SiriDisambiguationList, :speakableSelectionResponse)
add_property_to_class(SiriDisambiguationList, :listenAfterSpeaking)
add_property_to_class(SiriDisambiguationList, :speakableText)
add_property_to_class(SiriDisambiguationList, :speakableFinalDelimiter)
add_property_to_class(SiriDisambiguationList, :speakableDelimiter)
add_property_to_class(SiriDisambiguationList, :selectionResponse)

class SiriAnswer < SiriObject
  def initialize(title="", lines=[])
    super("Object", "com.apple.ace.answer")
    self.title = title
    self.lines = lines
  end
end
add_property_to_class(SiriAnswer, :title)
add_property_to_class(SiriAnswer, :lines)

class SiriAnswerLine < SiriObject
  def initialize(text="", image="")
    super("ObjectLine", "com.apple.ace.answer")
    self.text = text
    self.image = image
  end
end
add_property_to_class(SiriAnswerLine, :text)
add_property_to_class(SiriAnswerLine, :image)

class SiriWeatherObject < SiriObject
  def initialize(currentConditions=SiriCurrentWeatherConditions.new, view="HOURLY", hourlyForecasts=[], weatherLocation=SiriWeatherLocation.new, extendedForecastUrl="http://m.yahoo.com/search?p=Cupertino,+CA&.tsrc=appleww", dailyForecasts=[], units=SiriWeatherUnits.new)
    super("Object", "com.apple.ace.weather")
    self.currentConditions = currentConditions
    self.view = view
    self.hourlyForecasts = hourlyForecasts
    self.weatherLocation = weatherLocation
    self.extendedForecastUrl = extendedForecastUrl
    self.dailyForecasts = dailyForecasts
    self.units = units
  end
end
add_property_to_class(SiriWeatherObject, :currentConditions)
add_property_to_class(SiriWeatherObject, :view)
add_property_to_class(SiriWeatherObject, :hourlyForecasts)
add_property_to_class(SiriWeatherObject, :weatherLocation)
add_property_to_class(SiriWeatherObject, :extendedForecastUrl)
add_property_to_class(SiriWeatherObject, :dailyForecasts)
add_property_to_class(SiriWeatherObject, :units)

class SiriCurrentWeatherConditions < SiriObject
  def initialize(heatIndex="38", dayOfWeek=7, timeOfObservation="13:53", barometricPressure=SiriBarometricPressure.new, visibility="16.09", percentOfMoonFaceVisible=92.3, temperature="34", sunrise="06:42", sunset="20:10", moonPhase="WANING_GIBBOUS", percentHumidity="32", timeZone="Central Standard Time", dewPoint="21", condition=SiriWeatherCondition.new, windChill="34")
    super("CurrentConditions", "com.apple.ace.weather")
    self.heatIndex = heatIndex
    self.dayOfWeek = dayOfWeek
    self.timeOfObservation = timeOfObservation
    self.barometricPressure = barometricPressure
    self.visibility = visibility
    self.percentOfMoonFaceVisible = percentOfMoonFaceVisible
    self.temperature = temperature
    self.sunrise = sunrise
    self.sunset = sunset
    self.moonPhase = moonPhase
    self.percentHumidity = percentHumidity
    self.timeZone = timeZone
    self.dewPoint = dewPoint
    self.condition = condition
    self.windChill = windChill
  end
end
add_property_to_class(SiriCurrentWeatherConditions, :heatIndex)
add_property_to_class(SiriCurrentWeatherConditions, :dayOfWeek)
add_property_to_class(SiriCurrentWeatherConditions, :timeOfObservation)
add_property_to_class(SiriCurrentWeatherConditions, :barometricPressure)
add_property_to_class(SiriCurrentWeatherConditions, :visibility)
add_property_to_class(SiriCurrentWeatherConditions, :percentOfMoonFaceVisible)
add_property_to_class(SiriCurrentWeatherConditions, :temperature)
add_property_to_class(SiriCurrentWeatherConditions, :sunrise)
add_property_to_class(SiriCurrentWeatherConditions, :sunset)
add_property_to_class(SiriCurrentWeatherConditions, :moonPhase)
add_property_to_class(SiriCurrentWeatherConditions, :percentHumidity)
add_property_to_class(SiriCurrentWeatherConditions, :timeZone)
add_property_to_class(SiriCurrentWeatherConditions, :dewPoint)
add_property_to_class(SiriCurrentWeatherConditions, :condition)
add_property_to_class(SiriCurrentWeatherConditions, :windChill)

class SiriBarometricPressure < SiriObject
  def initialize(value="1014.8", trend="Falling")
    super("BarometricPressure", "com.apple.ace.weather")
    self.value = value
    self.trend = trend
  end
end
add_property_to_class(SiriBarometricPressure, :value)
add_property_to_class(SiriBarometricPressure, :trend)

class SiriWeatherCondition < SiriObject
  def initialize(conditionCode="PartlyCloudyDay", conditionCodeIndex=30)
    super("Condition", "com.apple.ace.weather")
    self.conditionCode = conditionCode
    self.conditionCodeIndex = conditionCodeIndex
  end
end
add_property_to_class(SiriWeatherCondition, :conditionCode)
add_property_to_class(SiriWeatherCondition, :conditionCodeIndex)

class SiriHourlyForecast < SiriObject
  def initialize(chanceOfPrecipitation=10, isUserRequested=true, condition=SiriWeatherCondition.new, temperature=33.0, timeIndex=15)
    super("HourlyForecast", "com.apple.ace.weather")
    self.chanceOfPrecipitation = chanceOfPrecipitation
    self.isUserRequested = isUserRequested
    self.condition = condition
    self.temperature = temperature
    self.timeIndex = timeIndex
  end
end
add_property_to_class(SiriHourlyForecast, :chanceOfPrecipitation)
add_property_to_class(SiriHourlyForecast, :isUserRequested)
add_property_to_class(SiriHourlyForecast, :condition)
add_property_to_class(SiriHourlyForecast, :temperature)
add_property_to_class(SiriHourlyForecast, :timeIndex)

class SiriDailyForecast < SiriObject
  def initialize(lowTemperature=24.0, highTemperature=34.0, timeIndex=7, chanceOfPrecipitation=10, isUserRequested=true, condition=SiriWeatherCondition.new)
    super("DailyForecast", "com.apple.ace.weather")
    self.lowTemperature = lowTemperature
    self.highTemperature = highTemperature
    self.timeIndex = timeIndex
    self.chanceOfPrecipitation = chanceOfPrecipitation
    self.isUserRequested = isUserRequested
    self.condition = condition
  end
end
add_property_to_class(SiriDailyForecast, :lowTemperature)
add_property_to_class(SiriDailyForecast, :highTemperature)
add_property_to_class(SiriDailyForecast, :timeIndex)
add_property_to_class(SiriDailyForecast, :chanceOfPrecipitation)
add_property_to_class(SiriDailyForecast, :isUserRequested)
add_property_to_class(SiriDailyForecast, :condition)

class SiriWeatherUnits < SiriObject
  def initialize(distanceUnits="Kilometers", temperatureUnits="Celsius", pressureUnits="MB")
    super("Units", "com.apple.ace.weather")
    self.distanceUnits = distanceUnits
    self.temperatureUnits = temperatureUnits
    self.pressureUnits = pressureUnits
  end
end
add_property_to_class(SiriWeatherUnits, :distanceUnits)
add_property_to_class(SiriWeatherUnits, :temperatureUnits)
add_property_to_class(SiriWeatherUnits, :pressureUnits)

#####
# ITEMS
#####

class SiriMapItem < SiriObject
  def initialize(label="Apple Headquarters", location=SiriLocation.new, detailType="BUSINESS_ITEM")
    super("MapItem", "com.apple.ace.localsearch")
    self.label = label
    self.detailType = detailType
    self.location = location
  end
end
add_property_to_class(SiriMapItem, :label)
add_property_to_class(SiriMapItem, :detailType)
add_property_to_class(SiriMapItem, :location)

class SiriActionableMapItem < SiriObject
  def initialize(detail=SiriBusinessItem.new, label="Apple, Inc.", location=SiriLocation.new, commands=[], identifier="", detailType="BUSINESS_ITEM", providerCommand=[])
    super("ActionableMapItem", "com.apple.ace.localsearch")
    self.detail = detail
    self.label = label
    self.location = location
    self.commands = commands
    self.identifier = identifier
    self.detailType = detailType
    self.providerCommand = providerCommand
  end
end
add_property_to_class(SiriActionableMapItem, :detail)
add_property_to_class(SiriActionableMapItem, :label)
add_property_to_class(SiriActionableMapItem, :location)
add_property_to_class(SiriActionableMapItem, :commands)
add_property_to_class(SiriActionableMapItem, :identifier)
add_property_to_class(SiriActionableMapItem, :detailType)
add_property_to_class(SiriActionableMapItem, :providerCommand)

class SiriBusinessItem < SiriObject
  def initialize(name="Apple, Inc.", totalNumberOfReviews=1, businessIds={"yelp"=>"DS6ma185kMXBS8trQ0wBVg", "places"=>"-7864570592007977996", "localeze"=>"91265028"}, categories=[], reviews=[], phoneNumbers=[], rating=SiriBusinessRating.new, extSessionGuid="44eee732-1fa1-4d0b-8e5b-d055f5b0a69e,Lookup")
    super("Business", "com.apple.ace.localsearch")
    self.name = name
    self.totalNumberOfReviews = totalNumberOfReviews
    self.businessIds = businessIds
    self.categories = categories
    self.reviews = reviews
    self.phoneNumbers = phoneNumbers
    self.rating = rating
    self.extSessionGuid = extSessionGuid
  end
end
add_property_to_class(SiriBusinessItem, :name)
add_property_to_class(SiriBusinessItem, :totalNumberOfReviews)
add_property_to_class(SiriBusinessItem, :businessIds)
add_property_to_class(SiriBusinessItem, :categories)
add_property_to_class(SiriBusinessItem, :reviews)
add_property_to_class(SiriBusinessItem, :phoneNumbers)
add_property_to_class(SiriBusinessItem, :rating)
add_property_to_class(SiriBusinessItem, :extSessionGuid)

class SiriPersonItem < SiriObject
  def initialize(identifier="")
    super("Person", "com.apple.ace.contact")
    self.identifier = identifier
  end
end
add_property_to_class(SiriPersonItem, :identifier)

class SiriListItem < SiriObject
  def initialize(title="", selectionText="", commands=[], speakableText="", object=SOME_KIND_OF_ITEM)
    super("ListItem", "com.apple.ace.system")
    self.title = title
    self.selectionText = selectionText
    self.commands = commands
    self.speakableText = speakableText
    self.object = object
  end
end
add_property_to_class(SiriListItem, :title)
add_property_to_class(SiriListItem, :selectionText)
add_property_to_class(SiriListItem, :commands)
add_property_to_class(SiriListItem, :speakableText)
add_property_to_class(SiriListItem, :object)



#####
# APPLE COMMANDS (commands that typically come from the server side)
#####

class SiriGetRequestOrigin < SiriObject
  def initialize(desiredAccuracy="HundredMeters", searchTimeout=8.0, maxAge=1800)
    super("GetRequestOrigin", "com.apple.ace.system")
    self.desiredAccuracy = desiredAccuracy
    self.searchTimeout = searchTimeout
    self.maxAge = maxAge
  end
end
add_property_to_class(SiriGetRequestOrigin, :desiredAccuracy)
add_property_to_class(SiriGetRequestOrigin, :searchTimeout)
add_property_to_class(SiriGetRequestOrigin, :maxAge)

class SiriRequestCompleted < SiriObject
  def initialize(callbacks=[])
    super("RequestCompleted", "com.apple.ace.system")
    self.callbacks = callbacks
  end
end
add_property_to_class(SiriRequestCompleted, :callbacks)

#####
# IPHONE RESPONSES (misc meta data back to the server)
#####

class SiriStartRequest < SiriObject
  def initialize(utterance="Testing", handsFree=false, proxyOnly=false)
    super("StartRequest", "com.apple.ace.system")
    self.utterance = utterance
    self.handsFree = handsFree
    if proxyOnly # dont send local when false since its non standard
      self.proxyOnly = proxyOnly
    end
  end
end
add_property_to_class(SiriStartRequest, :utterance)
add_property_to_class(SiriStartRequest, :handsFree)
add_property_to_class(SiriStartRequest, :proxyOnly)


class SiriSetRequestOrigin < SiriObject
  def initialize(longitude=-122.030089795589, latitude=37.3317031860352, desiredAccuracy="HundredMeters", altitude=0.0, speed=1.0, direction=1.0, age=0, horizontalAccuracy=50.0, verticalAccuracy=10.0)
    super("SetRequestOrigin", "com.apple.ace.system")
    self.horizontalAccuracy = horizontalAccuracy
    self.latitude = latitude
    self.desiredAccuracy = desiredAccuracy
    self.altitude = altitude
    self.speed = speed
    self.longitude = longitude
    self.verticalAccuracy = verticalAccuracy
    self.direction = direction
    self.age = age
  end
end
add_property_to_class(SiriSetRequestOrigin, :horizontalAccuracy)
add_property_to_class(SiriSetRequestOrigin, :latitude)
add_property_to_class(SiriSetRequestOrigin, :desiredAccuracy)
add_property_to_class(SiriSetRequestOrigin, :altitude)
add_property_to_class(SiriSetRequestOrigin, :speed)
add_property_to_class(SiriSetRequestOrigin, :longitude)
add_property_to_class(SiriSetRequestOrigin, :verticalAccuracy)
add_property_to_class(SiriSetRequestOrigin, :direction)
add_property_to_class(SiriSetRequestOrigin, :age)

class SiriPersonSearch < SiriObject
  def initialize(name="Steve Jobs", scope="Local")
    super("PersonSearch", "com.apple.ace.contact")
    self.name = name
    self.scope = scope
  end
end
add_property_to_class(SiriPersonSearch, :name)
add_property_to_class(SiriPersonSearch, :scope)