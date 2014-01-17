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

require_once 'WebDriver.php';

class TargetLocator
{
	private $_driver;
	
	public function __construct(WebDriver $driver)
	{
		$this->_driver = $driver;
	}
	
	#region TargetLocator members
	/**
	 * Move to a different frame using its index
	 * @param Integer $frameIndex
	 * @return current WebDriver
	 */
	public function getFrameByIndex($frameIndex)
	{
		
		$this->_driver->getFrame($frameIndex);

		return $this->_driver;
	}

	/**
	 * Move to different frame using its name
	 * @param String $frameName
	 * @return current WebDriver
	 */
	public function getFrameByName($frameName)
	{
		//We should validate that frameName is string
		/*
		if ($frameName == null)
		{
			throw new ArgumentNullException("frameName", "Frame name cannot be null");
		}
		*/

		$this->_driver->getFrame($frameName);

		return $this->_driver;
	}

	/**
	 * Move to a frame element.
	 * @param WebElement $frameElement
	 * @return current WebDriver
	 */
	public function getFrameByWebElement(WebElement $frameElement)
	{
		//We should validate that frameElement is string
		/*
		if (frameElement == null)
		{
			throw new ArgumentNullException("frameElement", "Frame element cannot be null");
		}

		RemoteWebElement convertedElement = frameElement as RemoteWebElement;
		if (convertedElement == null)
		{
			throw new ArgumentException("frameElement cannot be converted to RemoteWebElement", "frameElement");
		}
		*/

		$frameId = $frameElement->getElementId();
		$target = array('ELEMENT' => $frameId);
		$this->_driver->getFrame($target);

		return $this->_driver;
	}

	/**
	 * Change to the Window by passing in the name
	 * @param String $windowName
	 * @return current WebDriver
	 */
	public function getWindow($windowName)
	{
		$this->_driver->getWindow($windowName);
		
		return $this->_driver;
	}

	/**
	 * Change the active frame to the default
	 * @return current WebDriver
	 */
	public function getDefaultFrame()
	{
		$this->_driver->getFrame(null);

		return $this->_driver;
	}

	/**
	 * Finds the active element on the page and returns it
	 * @return WebElement
	 */
	public function getActiveElement()
	{
		$webElement = null;

		$webElement = $this->_driver->getActiveElement();

		return $webElement;
	}


	/**
	 *  Switches to the currently active modal dialog for this particular driver instance.
	 * @return \SeleniumClient\Alert
	 */
	public function getAlert()
	{
		// N.B. We only execute the GetAlertText command to be able to throw
		// a NoAlertPresentException if there is no alert found.
		//$this->_driver->getAlertText();
		return new Alert($this->_driver); //validate that the Alert object can be created, if not throw an exception, try to use a factory singleton o depency of injection to only use 1 instance
	}
	#endregion
}