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

abstract class BrowserType
{
	/*
	Supported Browsers
	chrome|firefox|htmlunit|internet explorer|iphone
	*/

	const CHROME = "chrome";
	const FIREFOX = "firefox";
	const HTMLUNIT = "htmlunit";
	const INTERNET_EXPLORER = "internet explorer";
	const IPHONE = "iphone";
	
	public static function isValidBrowserType($browserType)
	{
		$refl = new \ReflectionClass(__CLASS__);
		
		$validBrowserType = false;
		
		foreach ($refl->getConstants() as $constantName => $constantValue)
		{
			if ($constantValue ==  $browserType) { $validBrowserType = true; }
		}
		
		return $validBrowserType;
	}
}