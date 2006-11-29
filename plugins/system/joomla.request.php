<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
jimport('joomla.application.plugin.helper');
jimport('joomla.environment.uri');

/**
 * Joomla! Search Engine Friendly URL plugin
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla!
 * @subpackage	SEF
 */
class  JRequestJoomla extends JPlugin 
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object		$subject The object to observe
	 * @since	1.0
	 */
	function JRequestJoomla(& $subject) 
	{
		parent::__construct($subject);

		// load plugin parameters
		$this->_plugin = & JPluginHelper::getPlugin('system', 'joomla.request');
		$this->_params = new JParameter($this->_plugin->params);
	}

	/**
	* Converting the site URL to fit to the HTTP request
	*
	*/
	function onBeforeStart()
	{
		global $mainframe;

		// Initialize some variables
		$config	= & JFactory::getConfig();

		// Get config variables
		$mode    = $this->_params->get('mode', 0);
		$rewrite = $config->getValue('config.sef');

		//Only use SEF is enabled and not in the administrator
		if ($rewrite && !$mainframe->isAdmin())
		{
			// get the full request URI
			$URI = JURI::getInstance();

			// Check for normal index.php?option= ... style
			$path = $URI->toString();
			if ((strpos($path, 'index.php?') !== false) || (strpos($path, 'feed.php?') !== false)) {
				return;
			}

			// Get the base and full URLs
			$FULL = $URI->toString();
			$BASE = JURI::base();
			
			// Set document link
			$doc = & JFactory::getDocument();
			$doc->setLink($BASE);

			$url = urldecode(trim(str_replace($BASE, '', $FULL), '/'));
			$this->parseURI($url);
		}
	}
	
	function parseURI($url)
	{
		$urlArray = explode('/', $url);
		if (count($urlArray))
		{
			// Check for index.php, index2.php, etc, in no-rewrite mode
			if ((preg_match( '#index\d?\.php#', $urlArray[0])) || (strpos($urlArray[0], 'feed.php') !== false)) {
				array_shift($urlArray);
			}

			$component = isset( $urlArray[0] ) ? $urlArray[0] : '';
			if ($component == '') {
				return;
			} 
			elseif (strpos($component,'option,') === false) 
			{
				//
				// FORMAT: /component_name/.../...
				//

				// shift the option off that array stack
				array_shift($urlArray);

				// component can only have letters, numbers and underscores
				$component = preg_replace( '#\W#', '', $component );

				$path = JPATH_BASE.DS.'components'.DS.'com_'.$component.DS.$component.'.php';
				// Do a quick check to make sure component exists
				if (!file_exists($path)) {
					JError::raiseError(404, JText::_('Invalid Request'));
					exit (404);
				}
				JRequest::setVar('option', 'com_'.$component, 'get');

				// If Itemid is set -- last item in array -- pop it off and set it
				if (is_numeric($urlArray[count($urlArray)-1])) {
					JRequest::setVar('Itemid', array_pop($urlArray), 'get');
				}

				// Use the custom sef handler if it exists
				$path = JPATH_BASE.DS.'components'.DS.'com_'.$component.DS.'request.php';
				if (file_exists($path)) 
				{
					require_once $path;
					$function = $component.'ParseURL';
					$function($urlArray,$this->_params);
				} 
				else 
				{
					// No handler set, just try to parse url by , separation
					foreach ($urlArray as $value)
					{
						$temp = explode(',', $value);
						if (isset ($temp[0]) && $temp[0] != '' && isset ($temp[1]) && $temp[1] != '') {
							JRequest::setVar($temp[0], $temp[1], 'get');
						}
					}
				}
			} 
			else 
			{
				//
				// FORMAT: /key1,value1/key2,value2/.../keyN,valueN
				//
				foreach ($urlArray as $value)
				{
					$temp = explode(',', $value);
					if (isset ($temp[0]) && $temp[0] != '' && isset ($temp[1]) && $temp[1] != '') {
						JRequest::setVar($temp[0], $temp[1], 'get');
					}
				}
			}
		}
	}
}

// Attach sef handler to event dispatcher
$dispatcher = & JEventDispatcher::getInstance();
$dispatcher->attach(new JRequestJoomla($dispatcher));


/**
 * Function to convert an internal Joomla URL to a humanly readible URL.
 *
 * @param	string	$string	The internal URL
 * @return	string	The absolute search engine friendly URL
 * @since	1.0
 */
function sefRelToAbs($string)
{
	global $mainframe, $Itemid, $option;
	
	static $strings;

	if (!$strings) {
		$strings = array();
	}

	if (!isset( $strings[$string] ))
	{
		// Initialize some variables
		$config	= & JFactory::getConfig();

		// Get the plugin parameters if not set
		static $params;
		if (!isset($params)) 
		{
			// load plugin parameters
			$plugin = & JPluginHelper::getPlugin('system', 'joomla.request');
			$params = new JParameter($plugin->params);
		}

		// Get config variables
		$mode    = $params->get('mode', 0);
		$rewrite = $config->getValue('config.sef');
		
		// Replace all &amp; with &
		$string = str_replace('&amp;', '&', $string);
		
		// Home index.php
		if ($string == 'index.php') {
			$string = '';
		}

		// decompose link into url component parts
		$uri = JURI::getInstance($string);
		
		// check if link contained a query component
		if ($query = $uri->getQuery()) 
		{
			// special handling for javascript
			$query = stripslashes(str_replace('+', '%2b', $query));
			
			// clean possible xss attacks
			$query = preg_replace("'%3Cscript[^%3E]*%3E.*?%3C/script%3E'si", '', $query);
			
			//set the query
			$uri->setQuery($query);

			// make sure we have a valid itemid set	
			if(!$uri->getVar('Itemid')) {
				$uri->setVar('Itemid', $Itemid);
			}
			
			// make sure we have a valid option set	
			if(!$uri->getVar('option')) {
				$uri->setVar('option', $option);
			}
		}
		
		// rewite URL
		if ($rewrite && !eregi("^(([^:/?#]+):)", $string) && !strcasecmp(substr($string, 0, 9), 'index.php')) 
		{
			//get component name
			$component = str_replace('com_', '', $uri->getVar('option'));	
			$itemid    = intval( $uri->getVar('Itemid'));
				
			$route     = ''; //the route created
				
			// Build component name and sef handler path
			$path = JPATH_BASE.DS.'components'.DS.$uri->getVar('option').DS.'request.php';
				
			$uri->delVar('option'); //don't need the option anymore
			$uri->delVar('Itemid'); //don't need the itemid anymore

			// Use the custom request handler if it exists
			if (file_exists($path)) 
			{
				require_once $path;
				$function  = $component.'BuildURL';
						
				$parts  = $function($uri->getQuery(true),$params);
								
				$route = implode('/', $parts);
				$route = ($route) ? $route.'/' : null;
						
			}	 
			else
			{
				$vars = $uri->getQuery(true);
				
				// Components with no custom handler
				foreach ($vars as $key => $value)
				{
					// remove slashes automatically added by parse_str
					$route .= $key.','.stripslashes($value).'/';
				}
				$route = str_replace('=', ',', $route);
			}
			
			// check if link contained fragment identifiers (ex. #foo)
			$fragment = null;
			if ($fragment = $uri->getFragment()) {
				// ensure fragment identifiers are compatible with HTML4
				if (preg_match('@^[A-Za-z][A-Za-z0-9:_.-]*$@', $fragment)) {
					$fragment = '#'.$fragment;
				}
			}
			
			$url = $component.'/'.$route.$itemid.$fragment;
			
			// Prepend the base URI if we are not using mod_rewrite
			if ($mode) {
				$url = 'index.php/'.$url;
			} 
						 	
			return $url;
		}
		
		$strings[$string] = $uri->toString();
	}
	
	return str_replace( '&', '&amp;', $strings[$string] );;
}
?>