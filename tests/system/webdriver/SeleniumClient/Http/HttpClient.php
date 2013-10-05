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

class HttpClient
{
	const POST="POST";
	const GET="GET";
	const DELETE="DELETE";
	
	protected $_url = null;
	protected $_polling = null;
	protected $_jsonParams = null;
	protected $_httpMethod = null;
	
	protected $_trace = false;
	
	protected $_responseHeaders = null;
	protected $_responseBody = null;
	
	public function getTrace() { return $this->_trace; }
	
	public function setTrace($value)
	{
		$this->_trace = $value;	
		return $this;
	}
	
	public function setUrl($value)
	{
		$this->_url = $value;
		return $this;
	}
	
	public function setPolling($value)
	{
		$this->_polling = $value;
		return $this;
	}
	
	public function setJsonParams($value)
	{
		$this->_jsonParams = $value;
		return $this;
	}
	
	public function setHttpMethod($value)
	{
		$this->_httpMethod = $value;
		return $this;
	}
	
	public function execute()
	{
		if (empty($this->_url) || empty($this->_httpMethod)) { throw new \Exception("Must specify URL and HTTP METHOD"); }
		
		$curl = curl_init($this->_url);
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=UTF-8','Accept: application/json'));
		
		if($this->_httpMethod == HttpClient::POST)
		{
			curl_setopt($curl, CURLOPT_POST, true);
				
			if ($this->_jsonParams && is_array($this->_jsonParams)) { curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->_jsonParams)); }
		}
		else if ($this->_httpMethod == HttpClient::DELETE) { curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE'); }
		else if ($this->_httpMethod == HttpClient::GET) { /*NO ACTION NECESSARY*/ }
		
		//Curl execution here
		$rawResponse = trim(curl_exec($curl));
		
		$responseBody = json_decode($rawResponse, true);
		
		$responseHeaders=curl_getinfo($curl);
		
		if ($this->_trace)
		{
			echo "\n***********************************************************************\n";
			echo "URL: " . $this->_url . "\n";
			echo "METHOD: " . $this->_httpMethod . "\n";
			
			echo "PARAMETTERS: ";
			if (is_array($this->_jsonParams)) { echo print_r($this->_jsonParams); }
			else echo "NONE"; { echo "\n"; }
			
			echo "RESULTS:" .  print_r($responseBody);
			echo "\n";
			echo "CURL INFO: ";
			echo print_r($responseHeaders);
			
			echo "\n***********************************************************************\n";
		}
		
		curl_close($curl);
		
		$this->_responseHeaders = $responseHeaders;
		$this->_responseBody = $responseBody;
	}
}