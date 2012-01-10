<?php
class DefensioError                extends Exception     { public $http_status; };
class DefensioFail                 extends DefensioError { public $defensio_response; };
class DefensioUnexpectedHTTPStatus extends DefensioError { };
class DefensioInvalidKey           extends DefensioError { };
class DefensioEmptyCallbackData    extends DefensioError { };
class DefensioConnectionError      extends DefensioError { public $error_code; public $error_string; };
class DefensioConnectionTimeout   extends DefensioConnectionError{ };
?>
