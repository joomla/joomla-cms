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

abstract class HttpFactory{
	
	const PRODUCTIONMODE = "PRODUCTION";
	const TESTINGMODE = "TESTING";	
	
	public static function getClient($environment)
	{
		switch(strtoupper($environment))
		{			
			case HttpFactory::PRODUCTIONMODE :
				require_once("SeleniumAdapter.php");
				return new SeleniumAdapter();
				break;
			case HttpFactory::TESTINGMODE:			
				require_once("../../SeleniumClientTest/HttpClientMock.php");
				return new \HttpClientMock();			
				break;
		}
	}
}