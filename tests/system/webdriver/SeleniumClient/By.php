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

class By
{
	private $_strategy = "";
	public function getStrategy() { return $this->_strategy; }
	
	private $_selectorValue = "";
	public function getSelectorValue() { return $this->_selectorValue; }
	
	/**
	 * Locate by element's css class name
	 * @param String $selectorValue
	 * @return \SeleniumClient\By
	 */
	public static function className($selectorValue) { return new By("class name", $selectorValue); }
	
	/**
	 * Locate by element's css selector path
	 * @param String $selectorValue
	 * @return \SeleniumClient\By
	 */
	public static function cssSelector($selectorValue) { return new By("css selector", $selectorValue); }
	
	/**
	 * Locate by element's id
	 * @param String $selectorValue
	 * @return \SeleniumClient\By
	 */
	public static function id($selectorValue) { return new By("id", $selectorValue); }
	
	/**
	 * Locate by element's name
	 * @param unknown_type $selectorValue
	 * @return \SeleniumClient\By
	 */
	public static function name($selectorValue) { return new By("name", $selectorValue); }
	
	/**
	 * Locate by element's link text
	 * @param String $selectorValue
	 * @return \SeleniumClient\By
	 */
	public static function linkText($selectorValue) { return new By("link text", $selectorValue); }
	
	/**
	 * Locate by part of element's link text
	 * @param String $selectorValue
	 * @return \SeleniumClient\By
	 */
	public static function partialLinkText($selectorValue) { return new By("partial link text", $selectorValue); }
	
	/**
	 * Locate by element's tag name
	 * @param String $selectorValue
	 * @return \SeleniumClient\By
	 */
	public static function tagName($selectorValue) { return new By("tag name", $selectorValue); }
	
	/**
	 * Locate by element's xPath
	 * @param String $selectorValue
	 * @return \SeleniumClient\By
	 */
	public static function xPath($selectorValue) { return new By("xpath", $selectorValue); }

	public function __toString() { return "By strategy: '" . $this->_strategy . "', strategy factor '" .  $this->_selectorValue."'"; }

	function __construct ($strategy, $selectorValue)
	{
		$this->_strategy = $strategy;
		$this->_selectorValue = $selectorValue;
	}
}