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

abstract class CapabilityType {	
	
	const browserName = "browserName";
	const version = "version";
	const platform = "platform";	
	const javascriptEnabled = "javascriptEnabled";
	const takesScreenshot = "takesScreenshot";
	const handlesAlerts = "handlesAlerts";
	const databaseEnabled = "databaseEnabled";
	const locationContextEnabled = "locationContextEnabled";
	const applicationCacheEnabled = "applicationCacheEnabled";
	const browserConnectionEnabled = "browserConnectionEnabled";
	const cssSelectorsEnabled = "cssSelectorsEnabled";
	const webStorageEnabled = "webStorageEnabled";
	const rotatable = "rotatable";
	const acceptSslCerts = "acceptSslCerts";
	const nativeEvents = "nativeEvents";
	const proxy = "proxy";	
	
	public static function isValidCapabilityType($capabilityType)
	{
		$refl = new \ReflectionClass(__CLASS__);
		
		$validCapabilityType = false;
		
		foreach ($refl->getConstants() as $constantName => $constantValue)
		{
			if($constantValue ==  $capabilityType )
			{
				$validCapabilityType = true;
			}
		}
		
		return $validCapabilityType;
	}
		
}