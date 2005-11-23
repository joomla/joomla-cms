url<?php

/**
* @version $Id$
* @package Joomla 
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Browser class, provides capability information about the current web client.
 * 
 * Browser identification is performed by examining the HTTP_USER_AGENT 
 * environment variable provided by the web server.
 * 
 * This module has many influences from the lib/Browser.php code in
 * version 3 of Horde.
 *
 * @author Johan Janssens <johan@joomla.be>
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */
 
jimport('joomla.classes.object');
 
class JBrowser extends JObject
{
	/**      
	 * Full user agent string.      
	 * 
	 * @var string      
	 */     
	var $_agent = '';
	 
	/**      
	 * Lower-case user agent string.      
	 * 
	 * @var string      
	 */     
	var $_lowerAgent = '';
	
	/**      
	 * HTTP_ACCEPT string      
	 *      
	 * @var string      
	 */     
	var $_accept = '';
	
	/**      
	 * Browser information array  
	 * using PHP get_browser function
	 *      
	 * @var array    
	 */     
	var $_browser = null;
	
	/**      
	  * Known robots.      
	  * 
	  * @var array      
	  */     
	var $_robots = array(         
		/* The most common ones. */         
		'Googlebot',         
		'msnbot',         
		'Slurp',         
		'Yahoo',         
		/* The rest alphabetically. */         
		'Arachnoidea',         
		'ArchitextSpider',         
		'Ask Jeeves',         
		'B-l-i-t-z-Bot',         
		'Baiduspider',         
		'BecomeBot',         
		'cfetch',         
		'ConveraCrawler',         
		'ExtractorPro',         
		'FAST-WebCrawler',         
		'FDSE robot',         
		'fido',         
		'geckobot',         
		'Gigabot',         
		'Girafabot',         
		'grub-client',         
		'Gulliver',         
		'HTTrack',         
		'ia_archiver',         
		'InfoSeek',         
		'kinjabot',         
		'KIT-Fireball',         
		'larbin',         
		'LEIA',         
		'lmspider',         
		'Lycos_Spider',         
		'Mediapartners-Google',         
		'MuscatFerret',         
		'NaverBot',         
		'OmniExplorer_Bot',         
		'polybot',         
		'Pompos',         
		'Scooter',         
		'Teoma',         
		'TheSuBot',         
		'TurnitinBot',         
		'Ultraseek',         
		'ViolaBot',         
		'webbandit',         
		'www.almaden.ibm.com/cs/crawler',         
		'ZyBorg',     
	);
	 
	/**      
	 * Create a browser instance (Constructor).      
	 *       
	 * @param string $userAgent  The browser string to parse.     
	 * @param string $accept     The HTTP_ACCEPT settings to use.      
	 */
	function __construct($userAgent = null, $accept = null) 
	{
		$this->match($userAgent, $accept);
	}
	
	/**      
	 * Returns a reference to the global Browser object, only creating it      
	 * if it doesn't already exist.   
	 *   
	 * This method must be invoked as:
	 * 		<pre>  $browser = &JBrowser::getInstance([$userAgent[, $accept]]);</pre>      
	 *      
	 * @param string $userAgent  The browser string to parse.      
	 * @param string $accept     The HTTP_ACCEPT settings to use.     
	 * @return JBrowser  The Browser object.      
	 */
	function &getInstance($userAgent = null, $accept = null) 
	{
		static $instances; 
		        
		if (!isset($instances)) {             
			$instances = array();         
		}         
		
		$signature = serialize(array($userAgent, $accept));         
		
		if (empty($instances[$signature])) {             
			$instances[$signature] = new JBrowser($userAgent, $accept);         
		}         
		
		return $instances[$signature];
	}
	
	/**      
	 * Parses the user agent string and inititializes the object with     
	 * all the known features and quirks for the given browser.   
	 *    
	 * @param string $userAgent  The browser string to parse.     
	 * @param string $accept     The HTTP_ACCEPT settings to use.      
	 */
	function match($userAgent = null, $accept = null)     
	{
		  // Set our agent string.         
		  if (is_null($userAgent)) {             
			  if (isset($_SERVER['HTTP_USER_AGENT'])) {                 
				  $this->_agent = trim($_SERVER['HTTP_USER_AGENT']);             
				}         
		} else {             
			$this->_agent = $userAgent;        
		}
		$this->_lowerAgent = strtolower($this->_agent);
		
		// Set our accept string.         
		if (is_null($accept)) {             
			if (isset($_SERVER['HTTP_ACCEPT'])) {                 
				$this->_accept = strtolower(trim($_SERVER['HTTP_ACCEPT']));             
			}         
		} else {             
			$this->_accept = strtolower($accept);         
		}
		
		 // Check for UTF support.         
		 if (isset($_SERVER['HTTP_ACCEPT_CHARSET'])) {             
			 $this->set('utf', strpos(strtolower($_SERVER['HTTP_ACCEPT_CHARSET']), 'utf') !== false);         
		}
		
		if (get_cfg_var('browscap'))
			$this->_browser = get_browser($this->_agent, true); //If available, use PHP native function
		else {
			$this->_browser = $this->_get_browser($this->_agent, true, dirname(__FILE__).'/browscap.ini');
		}
	}
	
	/**      
	 * Return the currently matched platform.      
	 * 
	 * @return string  The user's platform.       
	 */     
	function getPlatform()     {         
		return $this->_browser['platform'];    
	}
	
	/**      
	 * Retrieve the current browser.      
	 * 
	 * @return string  The current browser.      
	 */     
	function getBrowser()     {         
		return $this->_browser['browser'];     
	}
	
	/**      
	 * Retrieve the current browser's major version.      
	 * 
	 * @return integer  The current browser's major version.      
	 */     
	function getMajor()     {         
		return $this->_browser['majorver'];     
	}     
	
	/**      
	 * Retrieve the current browser's minor version.     
	 * @return integer  The current browser's minor version.      
	 */     
	function getMinor()     {         
		return $this->_browser['minorver'];     
	}     
	
	/**      
	 * Retrieve the current browser's version.    
	 * @return string  The current browser's version.      
	 */     
	function getVersion()     {         
		return $this->_browser['verion'];     
	}
	
	/**      
	 * Return the full browser agent string.    
	 *  
	 * @return string  The browser agent string.      
	 */     
	function getAgentString()     {         
		return $this->_agent;     
	}
	
	/**      
	 * Returns the server protocol in use on the current server.      
	 *      
	 * @return string  The HTTP server protocol version.      
	 */     
	function getHTTPProtocol()     
	{         
		if (isset($_SERVER['SERVER_PROTOCOL'])) {             
			if (($pos = strrpos($_SERVER['SERVER_PROTOCOL'], '/'))) {                 
				return substr($_SERVER['SERVER_PROTOCOL'], $pos + 1);             
			}         
		}         
		return null;     
	}
	
	/**      
	 * Check the current browser capabilities.      
	 * @param string $feature  The capability to check.      
	 * @return boolean  Does the browser have the capability set?      
	 */     
	function hasFeature($feature)     {         
		return $this->get($feature, false);     
	}     
	
	/**      
	 * Retrieve the current browser capability.      
	 * 
	 * @param string $feature  The capability to retrieve.     
	 * @return string  The value of the requested capability.      
	 */     
	function getFeature($feature)     {         
		return $this->get($feature, null);    
	}
	
	/**      
	 * Determines if a browser can display a given MIME type.      
	 *      
	 * @param string $mimetype  The MIME type to check.      
	 * @return boolean  True if the browser can display the MIME type.      
	 */     
	function isViewable($mimetype)     
	{         
		$mimetype = strtolower($mimetype);         
		list($type, $subtype) = explode('/', $mimetype);         
		
		if (!empty($this->_accept)) {             
			$wildcard_match = false;             
			
			if (strpos($this->_accept, $mimetype) !== false) {                 
				return true;            
			}             
			
			if (strpos($this->_accept, '*/*') !== false) {                 
				$wildcard_match = true;                 
				if ($type != 'image') {                     
					return true;                 
				}            
			 }             
			 
			 /* image/jpeg and image/pjpeg *appear* to be the same              
			  * entity, but Mozilla doesn't seem to want to accept the              
			  * latter.  For our purposes, we will treat them the              
			  * same. 
			  */             
			  if ($this->isBrowser('mozilla') &&                 
				($mimetype == 'image/pjpeg') &&                 
				(strpos($this->_accept, 'image/jpeg') !== false)) {                 
					return true;             
				}             
			
			if (!$wildcard_match) {                 
				return false;             
			}         
		}         
		
		if (!$this->hasFeature('images') || ($type != 'image')) {             
			return false;         
		}         
		
		return (in_array($subtype, $this->_images));     
	}
	
	/**      
	 * Determine if the given browser is the same as the current.    
	 * 
	 * @param string $browser  The browser to check.      
	 * @return boolean  Is the given browser the same as the current?      
	 */     
	function isBrowser($browser)     {         
		return ($this->_browser['browser'] === $browser);     
	}
	
	/**      
	 * Determines if the browser is a robot or not.      
	 * 
	 * @return boolean  True if browser is a known robot.      
	 */     
	function isRobot()    
	{        
		 foreach ($this->_robots as $robot) {             
			 if (strpos($this->_agent, $robot) !== false) {                 
				 return true;             
			}         
		}         
		return false;     
	}
	
	/**      
	 * Determine if we are using a secure (SSL) connection.      
	 * 
	 * @return boolean  True if using SSL, false if not.      
	 */     
	function isSSLConnection()     
	{         
		return ((isset($_SERVER['HTTPS']) &&                  
			($_SERVER['HTTPS'] == 'on')) ||                 
			getenv('SSL_PROTOCOL_VERSION'));     
	}     
	
	/**      
	 * Escape characters in javascript code if the browser requires it.      
	 * %23, %26, and %2B (for IE) and %27 need to be escaped or else      
	 * jscript will interpret it as a single quote, pound sign, or      
	 * ampersand and refuse to work.      
	 *      
	 * @param string $code  The JS code to escape.      
	 * @return string  The escaped code.      
	 */     
	function escapeJSCode($code)     
	{         
		$from = $to = array();         
		
		if ($this->isBrowser('msie') ||             
			($this->isBrowser('mozilla') && ($this->getMajor() >= 5))) { 		     
				$from = array('%23', '%26', '%2B');             
				$to = array(urlencode('%23'), urlencode('%26'), urlencode('%2B'));         
		}         
		
		$from[] = '%27';         $to[] = '\urlencode%27';   
	
		return str_replace($from, $to, $code);      
	}
	
   /**
	* @param string The name of the property
	* @param mixed The value of the property to set
	*/
	function set( $property, $value=null ) {
		$this->_browser[$property] = $value;
	}

	/**
	* @param string The name of the property
	* @param mixed  The default value
	* @return mixed The value of the property
	*/
	function get($property, $default=null) {
		if(isset($this->_browser[$property])) {
			return $this->_browser[$property];
		} 
		return $default;
	}
	
	function _get_browser($userAgent, $return_array = false, $db='./browscap.ini')
	{
		$browscap = parse_ini_file($db,true);
		
		$cap = null;
		
		foreach ($browscap as $key=>$value)
		{
			if (!array_key_exists('parent',$value)) continue;
			$keyEreg='^'.strtolower(str_replace(
			array('\\',  '.',  '?','*', '^',  '$',  '[',  ']',  '|',  '(',  ')',  '+',  '{',  '}',  '%'  ),
			array('\\\\','\\.','.','.*','\\^','\\$','\\[','\\]','\\|','\\(','\\)','\\+','\\{','\\}','\\%'),
			$key)).'$';
			if (preg_match('%'.$keyEreg.'%i',$userAgent))
			{
				$cap = array('browser_name_regex'=>$keyEreg,'browser_name_pattern'=>$key)+$value;
				$maxDeep = 8;
				while (array_key_exists('parent',$value)&&(--$maxDeep>0))
				$cap += ($value=$browscap[$value['parent']]);
				break;
			}
		}
		if ($return_array)  {
			return $cap;
		}  
		
		return ((object)$cap);
	}
}

?>