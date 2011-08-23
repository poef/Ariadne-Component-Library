<?php
	namespace ar;
	
	class exceptions {
		const NO_PATH_INFO     = 101;
		const UNKNOWN_ERROR    = 102;
		const HEADERS_SENT     = 103;
		const ACCESS_DENIED    = 104;
		const SESSION_TIMEOUT  = 105;
		const PASSWORD_EXPIRED = 106;
		const OBJECT_NOT_FOUND = 107;
		const DATABASE_EMPTY   = 108;
		const ILLEGAL_ARGUMENT = 109;
		const CONFIGURATION_ERROR = 110;
	}

	interface exception { }
	
	class exceptionDefault extends \Exception implements exception { }
	
	class exceptionIllegalRequest extends \Exception implements exception { }
	
	class exceptionConfigError extends \Exception implements exception { }
	
	class exceptionAuthenticationError extends \Exception implements exception { }
	
?>