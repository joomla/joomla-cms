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

use SeleniumClient\Http\SeleniumStaleElementReferenceException;

class WebElement
{
	private $_driver = null;
	private $_elementId = null;
	
	function __construct(WebDriver $driver, $elementId)
	{
		$this->_driver = $driver;
		$this->_elementId = $elementId;
	}
	
	/**
	 * Gets element's id
	 * @return Integer
	 */
	public function getElementId() { return $this->_elementId; }
	
	/**
	 * Send text to element
	 * @param String $text
	 */
	public function sendKeys($text) { $this->_driver->webElementSendKeys($this->_elementId,$text); }
	
	/**
	 * Gets element's visible text
	 * @return String
	 */
	public function getText() { return $this->_driver->webElementGetText($this->_elementId); }
	
	/**
	 * Gets element's tag name
	 * @return String
	 */
	public function getTagName() { return $this->_driver->webElementGetTagName($this->_elementId); }
	
	/**
	 * Gets element's specified attribute's value
	 * @param String $attributeName
	 * @return String
	 */
	public function getAttribute($attributeName)
	{
		//attributeName should be string
		return $this->_driver->webElementGetAttribute($this->_elementId, $attributeName);
	}

	/**
	 * Gets whether element is selected
	 * @return Boolean
	 */
	public function isSelected() { return $this->_driver->webElementIsSelected($this->_elementId); }
	
	/**
	 * Gets whether element is displayed
	 * @return Boolean
	 */
	public function isDisplayed() { return $this->_driver->webElementIsDisplayed($this->_elementId); }
	
	/**
	 * Gets whether element is enabled
	 * @return Boolean
	 */
	public function isEnabled() { return $this->_driver->webElementIsEnabled($this->_elementId); }
	
	
	/**
	 * Clear current element's text
	 */
	public function clear() { return $this->_driver->webElementClear($this->_elementId); }
	
	/**
	 * Click on element
	 */
	public function click() { $this->_driver->webElementClick($this->_elementId); }
	
	/**
	 * Submit form from element
	 */
	public function submit() { $this->_driver->webElementSubmit($this->_elementId); }
	
	/**
	 * Gets element's description
	 * @return Array
	 */
	public function describe() { return $this->_driver->webElementDescribe($this->_elementId); }
	
	/**
	 * Get element's coordinates
	 * @return Array
	 */
	public function getCoordinates() {return $this->_driver->webElementGetCoordinates($this->_elementId);}
	
	/**
	 * Get element's coordinates after scrolling
	 * @return Array
	 */
	public function getLocationOnScreenOnceScrolledIntoView() {return $this->_driver->webElementGetLocationOnScreenOnceScrolledIntoView($this->_elementId);}
	
	/**
	 * Find element within current element
	 * @param By $locator
	 * @param Boolean $polling
	 * @return SeleniumClient\WebElement
	 */
	public function findElement(By $locator, $polling = false) { return $this->_driver->webElementFindElement($this->_elementId, $locator, $polling); }
	
	/**
	 * Find elements within current element
	 * @param By $locator
	 * @param Bolean $polling
	 * @return Array SeleniumClient\WebElement
	 */
	public function findElements(By $locator, $polling = false) { return $this->_driver->webElementFindElements($this->_elementId, $locator, $polling); }
	
	/**
	 * Wait for expected element to be present within current element
	 * @param By $locator
	 * @param Integer $timeOutSeconds
	 * @return Ambiguous
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
	 * Wait for current element to be displayed
	 * @param Integer $timeOutSeconds
	 * @return \SeleniumClient\WebElement
	 */
	public function waitForElementUntilIsDisplayed($timeOutSeconds = 5)
	{
		//We have to validate that timeOutSeconds is int, we have to add a new exception into the selenium exceptions and not use the exceptions that are outsite of the library
		//if ( !is_int($timeOutSeconds) ) { throw new Not_Int_Exception("wait_for_element_until_is_present", "time_out_seconds"); }
		
		$wait = new WebDriverWait($timeOutSeconds);
		$element = $wait->until($this, "isDisplayed", array());
		
		return $this;
	}
	
	/**
	 * Wait for current element to be enabled
	 * @param Integer $timeOutSeconds
	 * @return \SeleniumClient\WebElement
	 */
	public function waitForElementUntilIsEnabled($timeOutSeconds = 5)
	{
		//We have to validate that timeOutSeconds is int, we have to add a new exception into the selenium exceptions and not use the exceptions that are outsite of the library
		//if ( !is_int($timeOutSeconds) ) { throw new Not_Int_Exception("wait_for_element_until_is_present", "time_out_seconds"); }
		
		$wait = new WebDriverWait($timeOutSeconds);
		$element = $wait->until($this, "isEnabled", array());
		
		return $this;
	}
	
	/**
	 * Wait until current element's text has changed
	 * @param String $targetText
	 * @param Integer $timeOutSeconds
	 * @throws WebDriverWaitTimeoutException
	 * @return \SeleniumClient\WebElement
	 */
	public function waitForElementUntilTextIsChanged($targetText, $timeOutSeconds = 5)
	{
		//We have to validate that timeOutSeconds is int, we have to add a new exception into the selenium exceptions and not use the exceptions that are outsite of the library
		//if ( !is_int($timeOutSeconds) ) { throw new Not_Int_Exception("wait_for_element_until_is_present", "time_out_seconds"); }
		
		$wait = true;
		
		while ($wait)
		{
			$currentText = $this->getText();

			if ($currentText == $targetText) { $wait = false; }
			else if ($timeOutSeconds <= 0) { throw new WebDriverWaitTimeoutException ("Timeout for waitForElementUntilTextIsChange." ); }
			
			sleep(1);
			
			$timeOutSeconds = $timeOutSeconds - 1;
		}
		
		return $this;
	}

	/**
	 * Wait until current element's text equals specified
	 * @param By $locator
	 * @param String $targetText
	 * @param Integer $timeOutSeconds
	 * @throws WebDriverWaitTimeoutException
	 * @return \SeleniumClient\WebElement
	 */
	public function waitForElementUntilIsPresentWithSpecificText(By $locator, $targetText, $timeOutSeconds = 5)
	{
		//We have to validate that timeOutSeconds is int, we have to add a new exception into the selenium exceptions and not use the exceptions that are outsite of the library
		//if ( !is_int($timeOutSeconds) ) { throw new Not_Int_Exception("wait_for_element_until_is_present", "time_out_seconds"); }

		$dynamicElement = null;
		$wait = true;
		$attempts = $timeOutSeconds;
		
		while ($wait)
		{
			$currentText = null;

			$webDriverWait = new WebDriverWait($timeOutSeconds);
			$dynamicElement = $webDriverWait->until($this, "findElement", array($locator, TRUE));

			try
			{
				$currentText = $dynamicElement->getText();
			}
			catch(SeleniumStaleElementReferenceException $ex)
			{
				//echo "\nError The Objet Disappear, Wait For Element Until Is Present With Specific Text\n";
			}

			if ($currentText == $targetText) { $wait = false; }
			else if ($attempts <= 0) { throw new WebDriverWaitTimeoutException ("Timeout for waitForElementUntilIsPresentAndTextIsChange." ); }
			
			sleep(1);
			
			$attempts = $attempts - 1;
		}
		return $dynamicElement;
	}
}