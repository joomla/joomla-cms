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

require_once "Exceptions.php";

class SeleniumAdapter extends HttpClient
{
	public function execute()
	{
		parent::execute();
		
		$this->validateSeleniumResponseCode();
		$this->validateHttpCode ();
		
		return $this->_responseBody;
	}
	
	protected function validateHttpCode()
	{
		// Http response exceptions
		switch (intval(trim($this->_responseHeaders['http_code'])))
		{
			case 400:
				throw new SeleniumMissingCommandParametersException((string) $this->_responseHeaders['http_code'], $this->_responseHeaders['url']);
				break;
			case 405:
				throw new SeleniumInvalidCommandMethodException((string) $this->_responseHeaders['http_code'], $this->_responseHeaders['url']);
				break;
			case 500:
				if (!$this->_polling) { throw new SeleniumFailedCommandException((string) $this->_responseHeaders['http_code'], $this->_responseHeaders['url']); }
				break;
			case 501:
				throw new SeleniumUnimplementedCommandException((string) $this->_responseHeaders['http_code'], $this->_responseHeaders['url']);
				break;
			default:
				// Looks for 4xx http codes
				if (preg_match("/^4[0-9][0-9]$/", $this->_responseHeaders['http_code'])) { throw new SeleniumInvalidRequestException((string) $this->_responseHeaders['http_code'], $this->_responseHeaders['url']); }
				break;
		}
	}
	
	protected function validateSeleniumResponseCode()
	{
		// Selenium response status exceptions
		if ($this->_responseBody != null)
		{
			if (isset($this->_responseBody["value"]["localizedMessage"]))
			{
				$message = $this->_responseBody["value"]["localizedMessage"];
			}
			else
			{
				$message = "";
			}
			switch (intval($this->_responseBody["status"]))
			{
				case 7:
					if (!$this->_polling) {
						throw new SeleniumNoSuchElementException($message);
					}
					break;
				case 8:
					throw new SeleniumNoSuchFrameException($message);
					break;
				case 9:
					throw new SeleniumUnknownCommandException($message);
					break;
				case 10:
					throw new SeleniumStaleElementReferenceException($message);
					break;
				case 11:
					throw new SeleniumElementNotVisibleException($message);
					break;
				case 12:
					throw new SeleniumInvalidElementStateException($message);
					break;
				case 13:
					throw new SeleniumUnknownErrorException($message);
					break;
				case 15:
					throw new SeleniumElementIsNotSelectableException($message);
					break;
				case 17:
					throw new SeleniumJavaScriptErrorException($message);
					break;
				case 19:
					throw new SeleniumXPathLookupErrorException($message);
					break;
				case 21:
					throw new SeleniumTimeoutException($message);
					break;
				case 23:
					throw new SeleniumNoSuchWindowException($message);
					break;
				case 24:
					throw new SeleniumInvalidCookieDomainException($message);
					break;
				case 25:
					throw new SeleniumUnableToSetCookieException($message);
					break;
				case 26:
					throw new SeleniumUnexpectedAlertOpenException($message);
					break;
				case 27:
					throw new SeleniumNoAlertOpenErrorException($message);
					break;
				case 28:
					throw new SeleniumScriptTimeoutException($message);
					break;
				case 29:
					throw new SeleniumInvalidElementCoordinatesException($message);
					break;
				case 30:
					throw new SeleniumIMENotAvailableException($message);
					break;
				case 31:
					throw new SeleniumIMEEngineActivationFailedException($message);
					break;
				case 32:
					throw new SeleniumInvalidSelectorException($message);
					break;
			}
		}
	}
}