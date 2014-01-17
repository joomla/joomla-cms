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

require_once 'WebElement.php';

class SelectElement
{
	private $_element;
	
	function __construct(WebElement $element)
	{
		$this->_element = $element;
	}
	
	/**
	 * Gets related WebElement
	 * @return WebElement
	 */
	public function getElement()
	{
		return $this->_element;
	}
	
	/**
	 * Sets an option selected by its value
	 * @param String $value
	 * @throws \Exception
	 */
	public function selectByValue($value)
	{
		$options = $this->_element->findElements(By::xPath(".//option[@value = '" . $value . "']"));
		
		$matched = false;
		foreach($options as $option)
		{
			
			if(!$option->isSelected())
			{
				$option->click();
			}
			
			$matched = true;
		}
		
		if (!$matched)
		{
			throw new \Exception("Cannot locate option in select element with value: " . $value);
		}
	}

	/**
	 * Sets an option selected by a partial text match
	 * @param String $text
	 * @throws \Exception
	 */
	public function selectByPartialText($text)
	{
		$options = $this->_element->findElements(By::xPath(".//option[contains(text(), '" . $text . "')]"));

		$matched = false;
		foreach($options as $option)
		{

			if(!$option->isSelected())
			{
				$option->click();
			}

			$matched = true;
		}

		if (!$matched)
		{
			throw new \Exception("Cannot locate option in select element with text: " . $text);
		}
	}
}