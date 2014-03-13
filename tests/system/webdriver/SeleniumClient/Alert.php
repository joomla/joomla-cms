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

class Alert
{

	private $_driver;

	/**
	 * @param WebDriver $driver
	 */
	public function __construct(WebDriver $driver) { $this->_driver = $driver; }

	#region IAlert Members
	/**
	 * Gets the text of the alert.
	 * @return String
	 */
	public function getText() { return $this->_driver->getAlertText(); }

	/**
	 * Dismisses the alert.
	 */
	public function dismiss() { $this->_driver->dismissAlert(); }

	/**
	 * Accepts the alert.
	 */
	public function accept() { $this->_driver->acceptAlert(); }

	/**
	 * Sends keys to the alert.
	 * @param String $keysToSend
	 */
	public function sendKeys($keysToSend)
	{
		$this->_driver->setAlertValue($keysToSend);
	}
	#endregion
}