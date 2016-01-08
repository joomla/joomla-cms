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

class Cookie {
	
	private $_name = "";
	private $_value = "";
	private $_path = "";
	private $_domain = "";
	private $_secure = false;
	private $_expiry = 0;
	
	public function __construct($name, $value, $path = null, $domain = null, $secure = null, $expiry = null) {
		
		if (isset ( $secure ) && ! is_bool ( $secure )) {
			throw new \Exception ( "'Secure' property must be boolean" );
		}
		
		if (isset ( $expiry ) && ! is_numeric ( $expiry )) {
			throw new \Exception ( "'Expiry' property must be numeric" );
		}
		
		if($name != null)
		$this->_name = $name;
		
		if($value != null)
		$this->_value = $value;
		
		if($path != null)
		$this->_path = $path;
		
		if($domain != null)
		$this->_domain = $domain;
		
		if($secure != null)
		$this->_secure = $secure;
		
		if($expiry != null)
		$this->_expiry = $expiry;
	}
	
	public function getArray() {
		
		$array = array ();
		
		$array ["name"] = $this->_name;		
		$array ["value"] = $this->_value;			
		$array ["path"] = $this->_path;		
		$array ["domain"] = $this->_domain;		
		$array ["secure"] = $this->_secure;		
		$array ["expiry"] = $this->_expiry;
		
		return $array;
	}
}