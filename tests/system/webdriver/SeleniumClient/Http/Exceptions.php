<?php
// Copyright 2012-present Nearsoft, Inc

// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at

// http://www.apache.org/licenses/LICENSE-2.0

// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

namespace SeleniumClient\Http;

// HTTP Code Exceptions
class SeleniumInvalidRequestException extends \Exception {
	public function __construct($httpResponseCode, $url) {
		parent::__construct ( "HTTP response code {$httpResponseCode}.Invalid request. Url {$url}. Could be unknown command or variable resource not found" );
	}
}
class SeleniumUnimplementedCommandException extends \Exception {
	public function __construct($httpResponseCode, $url) {
		parent::__construct ( "HTTP response code {$httpResponseCode}. Unimplemented Commands. Url {$url}." );
	}
}
class SeleniumInvalidCommandMethodException extends \Exception {
	public function __construct($httpResponseCode, $url) {
		parent::__construct ( "HTTP response code {$httpResponseCode}. Invalid command method. Url {$url}. If a request path maps to a valid resource, but that resource does not respond to the request method, the server should respond with a 405 Method Not Allowed." );
	}
}
class SeleniumMissingCommandParametersException extends \Exception {
	public function __construct($httpResponseCode, $url) {
		parent::__construct ( "HTTP response code {$httpResponseCode}. Missing JSON parameters. Url {$url}." );
	}
}
class SeleniumFailedCommandException extends \Exception {
	public function __construct($httpResponseCode, $url) {
		parent::__construct ( "HTTP response code {$httpResponseCode}. Command failed. Url {$url}." );
	}
}

// Selenium Response Status Exceptions
class SeleniumNoSuchElementException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " An element could not be located on the page using the given search parameters. "  . $message);
	}
}
class SeleniumNoSuchFrameException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " A request to switch to a frame could not be satisfied because the frame could not be found. "  . $message);
	}
}
class SeleniumUnknownCommandException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " The requested resource could not be found, or a request was received using an HTTP method that is not supported by the mapped resource. "  . $message);
	}
}
class SeleniumStaleElementReferenceException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " An element command failed because the referenced element is no longer attached to the DOM. "  . $message);
	}
}
class SeleniumElementNotVisibleException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " An element command could not be completed because the element is not visible on the page. "  . $message);
	}
}
class SeleniumInvalidElementStateException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " An element command could not be completed because the element is in an invalid state (e.g. attempting to click a disabled element). "  . $message);
	}
}
class SeleniumUnknownErrorException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " An unknown server-side error occurred while processing the command. "  . $message);
	}
}
class SeleniumElementIsNotSelectableException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " An attempt was made to select an element that cannot be selected. "  . $message);
	}
}
class SeleniumJavaScriptErrorException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " An error occurred while executing user supplied JavaScript. "  . $message);
	}
}
class SeleniumXPathLookupErrorException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " An error occurred while searching for an element by XPath. "  . $message);
	}
}
class SeleniumTimeoutException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " An operation did not complete before its timeout expired. "  . $message);
	}
}
class SeleniumNoSuchWindowException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " A request to switch to a different window could not be satisfied because the window could not be found. "  . $message);
	}
}
class SeleniumInvalidCookieDomainException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " An illegal attempt was made to set a cookie under a different domain than the current page. "  . $message);
	}
}
class SeleniumUnableToSetCookieException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " A request to set a cookie's value could not be satisfied. "  . $message);
	}
}
class SeleniumUnexpectedAlertOpenException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " A modal dialog was open, blocking this operation. "  . $message);
	}
}
class SeleniumNoAlertOpenErrorException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " An attempt was made to operate on a modal dialog when one was not open. "  . $message);
	}
}
class SeleniumScriptTimeoutException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " A script did not complete before its timeout expired. "  . $message);
	}
}
class SeleniumInvalidElementCoordinatesException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " The coordinates provided to an interactions operation are invalid. "  . $message);
	}
}
class SeleniumIMENotAvailableException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " IME was not available. "  . $message);
	}
}
class SeleniumIMEEngineActivationFailedException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " An IME engine could not be started. "  . $message);
	}
}

class SeleniumInvalidSelectorException extends \Exception {
	public function __construct($message = "") {
		parent::__construct ( " Argument was an invalid selector (e.g. XPath/CSS). "  . $message);
	}
}
