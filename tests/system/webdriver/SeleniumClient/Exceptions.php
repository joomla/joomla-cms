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

class WebDriverWaitTimeoutException extends \Exception {}

class EmptyValueException extends \Exception { }

class NotStringException extends \Exception
{
	public function __construct($functionName, $paramName) { parent::__construct("function {$functionName}: {$paramName} is not String."); }
}

class NotIntException extends \Exception
{
	public function __construct($functionName, $paramName) { parent::__construct("function {$functionName}: {$paramName} is not Int."); }
}

class NotBooleanException extends \Exception
{
	public function __construct($functionName, $paramName) { parent::__construct("function {$functionName}: {$paramName} is not Boolean"); }
}

class DirectoryNotFoundException extends \Exception
{
	public function __construct($functionName, $directoryPath) { parent::__construct("function {$functionName}: \"{$directoryPath}\" Directory specified for storing screenshots does not exist"); }
}

class FileNotFoundException extends \Exception
{
	public function __construct($functionName, $filePath) { parent::__construct("function {$functionName}: \"{$filePath}\" File does not exist"); }
}