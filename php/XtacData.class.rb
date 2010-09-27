class XtacData
	attr_accessor :host, :database, :hasConnection, :connection

	def initialize(inHost, inDatabase=nil)
		@host = inhost
		@database = inDatabase
		@hasConnection = false
	end

	def hasConnection
		return @hasConnection
	end

	def translateValue(inValue)
		unless(inValue.length == 0 || inValue[-1] == 'Z' || inValue.is_a? Numeric
	end
end
