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

namespace SeleniumClient;

use SeleniumClient\DesiredCapabilities;
use SeleniumClient\Http\HttpClient;
use SeleniumClient\Http\HttpFactory;

require_once 'By.php';
require_once 'DesiredCapabilities.php';
require_once 'Http/HttpFactory.php';
require_once 'Http/HttpClient.php';
require_once 'TargetLocator.php';
require_once 'WebElement.php';

class WebDriver
{
	private $_hubUrl = null;
	private $_sessionId = null;
	private $_screenshotsDirectory = null;
	private $_environment = HttpFactory::PRODUCTIONMODE;
	private $_capabilities = null;
	
	/**
	 * @param DesiredCapabilities $desiredCapabilities
	 * @param String $host
	 * @param Integer $port
	 */
	public function __construct(DesiredCapabilities $desiredCapabilities = null, $host = "http://localhost", $port = 4444)
	{
		$this->_hubUrl = $host . ":" . strval($port) . "/wd/hub";
		
		if(!isset($desiredCapabilities)) { $desiredCapabilities = new DesiredCapabilities("firefox"); }
		
		$this->startSession($desiredCapabilities);
	}
	
	/**
	 * Set whether production or testing mode for library
	 * @param String $value
	 */
	public function setEnvironment($value) { $this->_environment = $value; }
	
	/**
	 * Get current Selenium environment
	 * @return String
	 */
	public function getEnvironment() {
		return $this->_environment;
	}

	/**
	 * Get current Selenium Hub url
	 * @return String
	 */
	public function getHubUrl() { return $this->_hubUrl; }
	
	/**
	 * Get assigned session id
	 * @return Integer
	 */
	public function getSessionId() { return $this->_sessionId; }
	
	/**
	 * Get default screenshots directory
	 * @return String
	 */
	public function getScreenShotsDirectory() { return $this->_screenshotsDirectory; }
	
	/**
	 * Sets default screenshots directory for files to be stored in
	 * @param String $value
	 */
	public function setScreenShotsDirectory($value) { $this->_screenshotsDirectory = $value; }
	
	/**
	 * Creates new target locator to be handled
	 * @return \SeleniumClient\TargetLocator
	 */
	public function switchTo() { return new TargetLocator($this); }

	/**
	 * Starts new Selenium session
	 * @param DesiredCapabilities $desiredCapabilities
	 * @throws \Exception
	 */
	private function startSession(DesiredCapabilities $desiredCapabilities)
	{
		if($desiredCapabilities->getBrowserName() == null || trim($desiredCapabilities->getBrowserName()) == '')
		{
			throw new \Exception("Can not start session if browser name is not specified");
		}
		
		$command = "session";
		$params = array ('desiredCapabilities' => $desiredCapabilities->getCapabilities());
		$urlHubFormatted = $this->_hubUrl . "/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->execute();
		$this->_sessionId = $results['sessionId'];
		$this->_capabilities = $this->setCapabilities();
	}
	
	/**
	 * @return Array of actual capabilities
	 */
	private function setCapabilities()
	{
		$command = "session";
		$urlHubFormatted = $this->_hubUrl . "/{$command}/$this->_sessionId";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (isset($results["value"])) { $result = $results["value"]; }
		return $result;
	}
	
	/**
	 * Gets information on current selenium sessions
	 * @return Array of current sessions in hub
	 */
	public function getCurrentSessions()
	{
		$command = "sessions";
		$urlHubFormatted = $this->_hubUrl . "/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (isset ( $results ["value"] )) { $result = $results["value"]; }
		return $result;
	}
	
	/**
	 * Gets actual capabilities
	 * @return Array of actual capabilities
	 */
	public function getCapabilities() { return $this->_capabilities; }

	/**
	 * Removes current session
	 */
	public function quit()
	{
		$command = "session";
		$urlHubFormatted = $this->_hubUrl . "/{$command}/$this->_sessionId";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::DELETE)->execute();
	}

	
	/**
	 * Navigates to specified url
	 * @param String $url
	 */
	public function get($url)
	{
		$command = "url";
		$params = array ('url' => $url);
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->execute();
	}
	
	/**
	 * Gets current url
	 * @return String
	 */
	public function getCurrentPageUrl()
	{
		$command = "url";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();

		$result = null;
		if (isset($results["value"]) && trim ($results["value"]) != "") { $result = $results ["value"]; }
		return $result;
	}
	
	
	
	/**
	 * Sets default time for selenium to wait for an element to be present
	 * @param Integer $miliseconds
	 */
	public function setImplicitWait($miliseconds)
	{
		$command = "implicit_wait";
		$params = array ('ms' => $miliseconds );
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/timeouts/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->execute();
	}
	
	/**
	 * Get current server's status
	 * @return Array
	 */
	public function status()
	{
		$command = "status";
		$urlHubFormatted = $this->_hubUrl . "/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (is_array($results)) { $result = $results; }
		return $result;
	}
	
	
	/**
	 * Navigate forward in history
	 */
	public function forward()
	{
		$command = "forward";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->execute();
	}
	
	
	/**
	 * Navigate back in history
	 */
	public function back()
	{
		$command = "back";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->execute();
	}
	
	
	/**
	 * Refreshes current page
	 */
	public function refresh()
	{
		$command = "refresh";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->execute();
	}
	
	/**
	 * Gets current page source
	 * @return String
	 */
	public function pageSource()
	{
		$command = "source";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (isset($results["value"]) && trim ($results["value"]) != "") { $result = $results["value"]; }
		return $result;
	}
	
	/**
	 * Gets current page title
	 * @return String
	 */
	public function title()
	{
		$command = "title";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (isset($results["value"]) && trim ($results["value"]) != "") { $result = $results["value"]; }
		return $result;
	}

	/**
	 * Takes screenshot of current screen, saves it in specified default directory or as specified in parameter
	 * @param String $overrideScreenshotsDirectory
	 * @throws \Exception
	 * @return string
	 */
	public function screenshot($overrideScreenshotsDirectory = null)
	{
		$screenshotsDirectory = null;
		if (isset($overrideScreenshotsDirectory)) { $screenshotsDirectory = $overrideScreenshotsDirectory; }
		else if (isset($this->_screenshotsDirectory)) { $screenshotsDirectory = $this->_screenshotsDirectory; }
		else { throw new \Exception("Must Specify Screenshot Directory"); }
		
		$command = "screenshot";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		if (isset($results["value"]) && trim($results["value"]) != "")
		{
			if (!file_exists($screenshotsDirectory . "/" . $this->_sessionId)) { mkdir($screenshotsDirectory . "/" . $this->_sessionId, 0777, true); }
			
			$fileName = date ("YmdHmsu") . "-" . (count(glob($screenshotsDirectory . "/" . $this->_sessionId . "/*.png")) + 1) .".png";
			
			file_put_contents($screenshotsDirectory . "/" . $this->_sessionId . "/" .$fileName, base64_decode($results["value"]));
			
			return $fileName;
		}
	}
	
	/**
	 * Gets an element within current page
	 * @param By $locator
	 * @param Boolean $polling
	 * @return \SeleniumClient\WebElement
	 */
	public function findElement(By $locator, $polling = false)
	{
		$command = "element";
		$params = array ('using' => $locator->getStrategy(), 'value' => $locator->getSelectorValue());
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->setPolling($polling)->execute();
		
		$result = null;
		if (isset($results["value"]["ELEMENT"]) && trim ($results["value"]["ELEMENT"]) != "") { $result = new WebElement($this, $results["value"]["ELEMENT"]); }
		return $result;
	}
	
	/**
	 * Gets elements within current page
	 * @param By $locator
	 * @param unknown_type $polling
	 * @return Array \SeleniumClient\WebElement
	 */
	public function findElements(By $locator, $polling = false)
	{
		$command = "elements";
		$params = array('using' => $locator->getStrategy(), 'value' => $locator->getSelectorValue());
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->setPolling($polling)->execute();
		
		$result = null;
		if (isset($results["value"]) && is_array($results["value"]))
		{
			$webElements = array();
			
			foreach($results ["value"] as $element) { $webElements[] = new WebElement($this, $element["ELEMENT"]); }

			$result = $webElements;
		}
		return $result;
	}
	
	/**
	 * Gets element that is currenly focused
	 * @return \SeleniumClient\WebElement
	 */
	public function getActiveElement()
	{
		$command = "active";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->execute();
		
		$result = null;
		if (isset($results["value"]["ELEMENT"]) && trim ($results["value"]["ELEMENT"]) != "") { $result = new WebElement($this, $results["value"]["ELEMENT"]); }
		return $result;
	}

	#region Waiting Related
	
	/**
	 * Stops the process until an element is found
	 * @param By $locator
	 * @param Integer $timeOutSeconds
	 * @return \SeleniumClient\WebElement
	 */
	public function waitForElementUntilIsPresent(By $locator, $timeOutSeconds = 5)
	{
		//We have to validate that timeOutSeconds is int, we have to add a new exception into the selenium exceptions and not use the exceptions that are outsite of the library
		//if ( !is_int($timeOutSeconds) ) { throw new Not_Int_Exception("wait_for_element_until_is_present", "time_out_seconds"); }
	
		$wait = new WebDriverWait($timeOutSeconds);

		$dynamicElement = $wait->until($this, "findElement", array($locator, TRUE));

		return $dynamicElement;
	}

	/**
	 * Stops the process until an element is not found
	 * @param By $locator
	 * @param Integer $timeOutSeconds
	 * @return boolean true when element is gone, false if element is still there
	 */
	public function waitForElementUntilIsNotPresent(By $locator, $timeOutSeconds = 5)
	{
		for ($second = 0; ; $second++)
		{
			if ($second >= $timeOutSeconds) return false;
			$result = ($this->findElement($locator, true) === null);
			if ($result)
			{
				return true;
			}
			sleep(1);
		}
	}

	#endregion

	#region WebElement Related
	/**
	 * Send text to element
	 * @param Integer $elementId
	 * @param String $text
	 */
	public function webElementSendKeys($elementId, $text)
	{
		$command = "value";
		$params = array('value' => $this->getCharArray($text));
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$elementId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->execute();
	}
	
	/**
	 * Returns array of chars from String
	 * @param String $text
	 * @return array
	 */
	private function getCharArray($text)
	{
		$encoding = \mb_detect_encoding($text);
		$len = \mb_strlen($text, $encoding);
		$ret = array();
		while($len) {
			$ret[] = \mb_substr($text, 0, 1, $encoding);
			$text = \mb_substr($text, 1, $len, $encoding);
			$len = \mb_strlen($text, $encoding);
		}
		return $ret;
	}
	
	/**
	 * Gets element's visible text
	 * @param Integer $elementId
	 * @return String
	 */
	public function webElementGetText($elementId)
	{
		$command = "text";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$elementId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (isset($results["value"])) { $result = $results ["value"]; }
		return $result;
	}
	
	/**
	 * Gets element's tag name
	 * @param Integer $elementId
	 * @return String
	 */
	public function webElementGetTagName($elementId)
	{
		$command = "name";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$elementId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (isset($results["value"]) && trim($results["value"]) != "")  { $result = trim($results ["value"]); }
		return $result;
	}

	/**
	 * Gets element's specified attribute
	 * @param Integer $elementId
	 * @param String $attributeName
	 * @return String
	 */
	public function webElementGetAttribute($elementId, $attributeName)
	{
		$command = "attribute";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$elementId}/{$command}/{$attributeName}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (isset($results["value"]) && trim($results["value"]) != "") { $result = trim($results["value"]); }
		return $result;
	}
	
	/**
	 * Gets whether an element is selected
	 * @param Integer $elementId
	 * @return boolean
	 */
	public function webElementIsSelected($elementId)
	{
		$command = "selected";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$elementId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = false;
		if(trim($results ["value"]) == "1") { $result = true; }
		return $result;
	}
	
	
	/**
	 * Gets whether an element is currently displayed
	 * @param Integer $elementId
	 * @return boolean
	 */
	public function webElementIsDisplayed($elementId)
	{
		$command = "displayed";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$elementId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = false;
		if(trim($results ["value"]) == "1") { $result = true; }
		return $result;
	}
	
	/**
	 * Gets whether an element is currently enabled
	 * @param Integer $elementId
	 * @return boolean
	 */
	public function webElementIsEnabled($elementId)
	{
		$command = "enabled";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$elementId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = false;
		if(trim($results ["value"]) == "1") { $result = true; }
		return $result;
	}
	
	
	/**
	 * Clear element's value
	 * @param Integer $elementId
	 */
	public function webElementClear($elementId)
	{
		$command = "clear";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$elementId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->execute();
	}
	
	
	/**
	 * Clicks on an element
	 * @param Integer $elementId
	 */
	public function webElementClick($elementId)
	{
		$command = "click";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$elementId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->execute();
	}
	
	/**
	 * Execute form submit from element
	 * @param Integer $elementId
	 */
	public function webElementSubmit($elementId)
	{
		$command = "submit";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$elementId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->execute();
	}
	
	/**
	 * Gets element's description
	 * @param Integer $elementId
	 * @return Array
	 */
	public function webElementDescribe($elementId)
	{
		$command = "element";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}/{$elementId}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (isset($results["value"]) && is_array($results["value"])) { $result = $results ["value"]; }
		return $result;
	}
	
	/**
	 * Find an element within another element
	 * @param Integer $elementId
	 * @param By $locator
	 * @param Boolean $polling
	 * @return \SeleniumClient\WebElement
	 */
	public function webElementFindElement($elementId, By $locator, $polling = false)
	{
		$command = "element";
		$params = array('using' => $locator->getStrategy(), 'value' => $locator->getSelectorValue());
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$elementId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->setPolling($polling)->execute();

		$result = null;
		if (isset($results["value"]["ELEMENT"]) && trim($results["value"]["ELEMENT"]) != "") { $result = new WebElement($this, $results["value"]["ELEMENT"]); }
		return $result;
	}
	
	/**
	 * Find elements within another element
	 * @param Integer $elementId
	 * @param By $locator
	 * @param Boolean $polling
	 * @return \SeleniumClient\WebElement 
	 */
	public function webElementFindElements($elementId, By $locator, $polling = false)
	{
		$command = "elements";
		$params = array ('using' => $locator->getStrategy (), 'value' => $locator->getSelectorValue());
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$elementId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->setPolling($polling)->execute();
		
		$result = null;
		if (isset($results["value"]) && is_array($results["value"]))
		{
			$webElements = array();
			
			foreach($results ["value"] as $element) { $webElements[] = new WebElement($this, $element["ELEMENT"]); }
			
			$result = $webElements;
		}
		return $result;
	}
	
	/**
	 * Gets element's coordinates
	 * @param Integer $elementId
	 * @return Array
	 */
	public function webElementGetCoordinates($elementId)
	{
		$command = "location";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$elementId}/{$command}";
	
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
	
		$result = null;
		if (isset($results["value"]) && is_array($results["value"])) { $result = $results ["value"]; }
		return $result;
	}
	
	/**
	 * Gets element's coordinates after scrolling
	 * @param Integer $elementId
	 * @return Array
	 */
	public function webElementGetLocationOnScreenOnceScrolledIntoView($elementId)
	{
		$command = "location_in_view";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/element/{$elementId}/{$command}";
	
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
	
		$result = null;
		if (isset($results["value"]) && is_array($results["value"])) {
			$result = $results ["value"];
		}
		return $result;
	}
	
	#endregion

	#region Javascript Related
	/**
	 * Set's Async Script timeout
	 * @param Integer $miliseconds
	 */
	public function setAsyncScriptTimeout($miliseconds)
	{
		$command = "async_script";
		$params = array('ms' => $miliseconds);
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/timeouts/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->execute();
	}
	
	/**
	 * Executes javascript on page
	 * @param String $script
	 * @param Boolean $async
	 * @param Array $args
	 * @throws \Exception
	 * @return String
	 */
	private function executeScriptInternal($script, $async, $args)
	{
		if (!isset($this->_capabilities['javascriptEnabled']) || trim($this->_capabilities['javascriptEnabled']) != "1" ) { throw new \Exception("You must be using an underlying instance of WebDriver that supports executing javascript"); }
		
		$command = "execute";
		if($async === true) { $command = "execute_async"; }

		if($args == null) { $args = array(); }
		
		$params = array ('script' => $script, 'args' => $args);
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->execute();

		$result = null;
		if (isset($results ["value"])) { $result = $results ["value"]; }
		return $result;
	}

	/**
	 * Executes javascript on page
	 * @param String $script
	 * @param Array $args
	 * @return String
	 */
	public function executeScript($script, $args = null) { return $this->executeScriptInternal($script, false , $args); }
	
	/**
	 * Execute async javascript on page
	 * @param String $script
	 * @param Array $args
	 * @return String
	 */
	public function executeAsyncScript($script, $args = null) { return $this->executeScriptInternal($script, true , $args); }
	#endregion

	#region Windows and Iframes Related
	/**
	 * Focus on specified frame
	 * @param String $frameId
	 */
	public function getFrame($frameId)
	{
		//frameId can be string, int or array

		$command = "frame";
		$params = array ('id' => $frameId);
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->execute();
	}
	
	/**
	 * Changes focus to specified window
	 * @param String $name
	 */
	public function getWindow($name)
	{
		$command = "window";
		$params = array ('name' => $name); //name parameter could be window name or window handle
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->execute();
	}
	
	/**
	 * Closes current window
	 */
	public function closeCurrentWindow()
	{
		$command = "window";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::DELETE)->execute();
	}
	
	/**
	 * Gets current window's identifier
	 * @return String
	 */
	public function getCurrentWindowHandle()
	{
		$command = "window_handle";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (isset($results["value"])) { $result = $results ["value"]; }
		return $result;
	}
	
	/**
	 * Gets a list of available windows in current session
	 * @return Array
	 */
	public function getCurrentWindowHandles()
	{
		$command = "window_handles";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (isset($results["value"]) && is_array($results["value"])) { $result = $results ["value"]; }
		return $result;
	}
	
	/**
	 * Sets current window size
	 * @param Integer $width
	 * @param Integer $height
	 */
	public function setCurrentWindowSize($width, $height)
	{
		$windowHandle = $this->getCurrentWindowHandle();
		$this->setWindowSize($windowHandle, $width, $height);
	}
	
	
	/**
	 * Sets specified window's size
	 * @param String $windowHandle
	 * @param Integer $width
	 * @param Integer $height
	 */
	public function setWindowSize($windowHandle, $width, $height)
	{
		$command = "size";
		$params = array ('width' => $width, 'height' => $height);
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/window/{$windowHandle}/{$command}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->execute();
	}
	
	/**
	 * Gets current window's size
	 * @return Array
	 */
	public function getCurrentWindowSize()
	{
		$windowHandle = $this->getCurrentWindowHandle();
		return $this->getWindowSize($windowHandle);
	}
	
	/**
	 * Gets specified window's size
	 * @param String $windowHandle
	 * @return Array
	 */
	public function getWindowSize($windowHandle)
	{
		$command = "size";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/window/{$windowHandle}/{$command}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (isset($results["value"]) && is_array($results["value"])) { $result = $results ["value"]; }
		return $result;
	}
	
	/**
	 * Sets current window's position
	 * @param Integer $x
	 * @param Integer $y
	 */
	public function setCurrentWindowPosition($x, $y)
	{
		$windowHandle = $this->getCurrentWindowHandle();
		$this->setWindowPosition($windowHandle,$x, $y);
	}
	
	/**
	 * Sets specified window's position
	 * @param String $windowHandle
	 * @param Integer $x
	 * @param Integer $y
	 */
	public function setWindowPosition($windowHandle, $x, $y)
	{
		$command = "position";
		$params = array ('x' => $x, 'y' => $y);
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/window/{$windowHandle}/{$command}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->execute();
	}
	
	/**
	 * Gets current window's position
	 * @return Array
	 */
	public function getCurrentWindowPosition()
	{
		$windowHandle = $this->getCurrentWindowHandle();
		return $this->getWindowPosition($windowHandle);
	}
	
	/**
	 * Gets specified window's position
	 * @param String $windowHandle
	 * @return Array
	 */
	public function getWindowPosition($windowHandle)
	{
		$command = "position";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/window/{$windowHandle}/{$command}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (isset($results["value"]) && is_array($results["value"])) { $result = $results ["value"]; }
		return $result;
	}
	#endregion

	#region Cookies Related
	/**
	 * Sets cookie
	 * @param String $name
	 * @param String $value
	 * @param String $path
	 * @param String $domain
	 * @param Boolean $secure
	 * @param Integer $expiry
	 */
	public function setCookie($name, $value, $path = null, $domain = null, $secure = null, $expiry = null)
	{
		$cookie = new Cookie($name, $value, $path, $domain, $secure, $expiry);
		$command = "cookie";
		$params = array ('cookie' => $cookie->getArray());
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->execute();
	}
	
	/**
	 * Gets current cookies
	 * @return Array
	 */
	public function getCurrentCookies()
	{
		$command = "cookie";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (isset($results["value"]) && is_array($results["value"])) { $result = $results ["value"]; }
		return $result;
	}
	
	
	/**
	 * Remove cookies
	 * @param String $cookieName
	 */
	public function clearCookie($cookieName)
	{
		$command = "cookie";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}/{$cookieName}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::DELETE)->execute();
	}
	
	/**
	 * Removes all current cookies
	 */
	public function clearCurrentCookies()
	{
		$command = "cookie";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";

		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::DELETE)->execute();
	}
	#endregion

	#region Alert Related
	// Dismisses the alert.
	/**
	 * Sends false to current alert
	 */
	public function dismissAlert()
	{
		$command = "dismiss_alert";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->execute();
	}

	// Accepts the alert.
	/**
	 * Sends true to current alert
	 */
	public function acceptAlert()
	{
		$command = "accept_alert";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->execute();
	}

	// Gets the text of the alert.
	/**
	 * Gets current alert's text
	 * @return String
	 */
	public function getAlertText()
	{
		$command = "alert_text";
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$results = $httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::GET)->execute();
		
		$result = null;
		if (isset($results["value"])) { $result = $results["value"]; }
		return $result;
	}

	// Sends keys to the alert.
	
	/**
	 * Sends text to alert input
	 * @param String $value
	 */
	public function setAlertValue($value)
	{
		// validate that value is string

		$command = "alert_text";
		$params = array ('text' => $value);
		$urlHubFormatted = $this->_hubUrl . "/session/{$this->_sessionId}/{$command}";
		
		$httpClient = HttpFactory::getClient($this->_environment);
		$httpClient->setUrl($urlHubFormatted)->setHttpMethod(HttpClient::POST)->setJsonParams($params)->execute();
	}
	#endregion
}
