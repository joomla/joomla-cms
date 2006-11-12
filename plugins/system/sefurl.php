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
jimport('joomla.environment.request');
jimport('joomla.environment.uri');

/**
 * Joomla! Search Engine Friendly URL plugin
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla!
 * @subpackage	SEF
 */
class sefURL extends JPlugin {
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
	function sefURL(& $subject) {
		parent::__construct($subject);

		// load plugin parameters
		$this->_plugin = & JPluginHelper::getPlugin('system', 'sefurl');
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
		$rewrite	= $this->_params->get('mode', 0);
		$SEF	 	= $config->getValue('config.sef');

		//Only use SEF is enabled and not in the administrator
		if ($SEF && !$mainframe->isAdmin())
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

			$urlArray = explode('/', urldecode(trim(str_replace($BASE, '', $FULL), '/')));
			if (count($urlArray))
			{
				// Check for index.php, index2.php, etc, in no-rewrite mode
				if ((preg_match( '#index\d?\.php#', $urlArray[0])) || (strpos($urlArray[0], 'feed.php') !== false)) {
					array_shift($urlArray);
				}

				$component = isset( $urlArray[0] ) ? $urlArray[0] : '';
				if ($component == '') {
					return;
				} elseif (strpos($component,'option,') === false) {
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
					$path = JPATH_BASE.DS.'components'.DS.'com_'.$component.DS.'sef.php';
					if (file_exists($path)) {
						require_once $path;
						$function = $component.'_parseURL';
						$function($urlArray,$this->_params);
					} else {
						// No handler set, just try to parse url by , separation
						foreach ($urlArray as $value)
						{
							$temp = explode(',', $value);
							if (isset ($temp[0]) && $temp[0] != '' && isset ($temp[1]) && $temp[1] != '') {
								JRequest::setVar($temp[0], $temp[1], 'get');
							}
						}
					}
				} else {
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
}

// Attach sef handler to event dispatcher
$dispatcher = & JEventDispatcher::getInstance();
$dispatcher->attach(new sefURL($dispatcher));

/**
 * Function to convert an internal Joomla URL to an absolute Search Engine
 * Friendly URL.
 *
 * @param	string	$string	The internal URL
 * @return	string	The absolute search engine friendly URL
 * @since	1.0
 */
function sefRelToAbs($string)
{
	static $strings;
	global $mainframe;

	if (!$strings) {
		$strings = array();
	}

	if (!isset( $strings[$string] ))
	{
		// Initialize some variables
		$config	= & JFactory::getConfig();

		// Get the plugin parameters if not set
		static $params;
		if (!isset($params)) {
			// load plugin parameters
			$plugin = & JPluginHelper::getPlugin('system', 'sefurl');
			$params = new JParameter($plugin->params);
		}

		// Get the base request URL if not set
		$LiveSite =  JURI::base();

		// Get config variables
		$rewrite  = $params->get('mode', 0);
		$SEF	  = $config->getValue('config.sef');

		// SEF URL Handling
		if ($SEF && !eregi("^(([^:/?#]+):)", $string) && !strcasecmp(substr($string, 0, 9), 'index.php')) {
			// Replace all &amp; with &
			$string = str_replace('&amp;', '&', $string);

			// Home index.php
			if ($string == 'index.php') {
				$string = '';
			}

			// break link into url component parts
			$url = parse_url($string);

			// check if link contained a query component
			if (isset ($url['query'])) {
				// special handling for javascript
				$url['query'] = stripslashes(str_replace('+', '%2b', $url['query']));
				// clean possible xss attacks
				$url['query'] = preg_replace("'%3Cscript[^%3E]*%3E.*?%3C/script%3E'si", '', $url['query']);

				// break query into component parts
				$parts = null;
				parse_str($url['query'], $parts);

				$sefstring = '';

				if (isset($parts['option'])) {
					// Build component name and sef handler path
					$component = str_replace('com_', '', $parts['option']);
					$path = JPATH_BASE.DS.'components'.DS.$parts['option'].DS.'sef.php';

					// Use the custom sef handler if it exists
					if (file_exists($path)) {
						require_once $path;
						$function	= $component.'_buildURL';
						$sefstring	.= $component.'/';
						unset($parts['option']);
						$sefstring	.= $function($parts,$params);
						$string		= $sefstring.intval( @$parts['Itemid'] ) .'/';
					} else {
						// Components with no custom handler
						foreach ($parts as $key => $value)
						{
							// remove slashes automatically added by parse_str
							$sefstring .= $key.','.stripslashes($value).'/';
						}
						$string = str_replace('=', ',', $sefstring);
					}
				}
			}

			// check if link contained fragment identifiers (ex. #foo)
			$fragment = null;
			if (isset ($url['fragment'])) {
				// ensure fragment identifiers are compatible with HTML4
				if (preg_match('@^[A-Za-z][A-Za-z0-9:_.-]*$@', $url['fragment'])) {
					$fragment = '#'.$url['fragment'];
				}
			}

			// Prepend the base URI
			if ($rewrite) {
				return $LiveSite.'index.php/'.$string;
			} else {
				return $LiveSite.$string;
			}
		} else {
			// Handling for when SEF is not activated
			// Relative link handling
			if (!(strpos($string, $LiveSite) === 0)) {
				// if URI starts with a "/", means URL is at the root of the host...
				if (strncmp($string, '/', 1) == 0) {
					// splits http(s)://xx.xx/yy/zz..." into [1]="http(s)://xx.xx" and [2]="/yy/zz...":
					$live_site_parts = array ();
					eregi("^(https?:[\/]+[^\/]+)(.*$)", $LiveSite, $live_site_parts);

					$string = $live_site_parts[1] . $string;
					/*
					// check that url does not contain `http`, `https`, `ftp`, `mailto` or `javascript` at start of string
					} else if ( ( strpos( $string, 'http' ) !== 0 ) && ( strpos( $string, 'https' ) !== 0 ) && ( strpos( $string, 'ftp' ) !== 0 ) && ( strpos( $string, 'file' ) !== 0 ) && ( strpos( $string, 'mailto' ) !== 0 ) && ( strpos( $string, 'javascript' ) !== 0 ) ) {
						// URI doesn't start with a "/" so relative to the page (live-site):
						$string = $LiveSite .'/'. $string;
					}
					*/
				} else {
					$check = 1;

					// array list of URL schemes
					$url_schemes = array ('data:','file:','ftp:','gopher:','imap:','ldap:','mailto:','news:','nntp:','telnet:','javascript:','irc:','http:','https:');

					foreach ($url_schemes as $url)
					{
						if (strpos($string, $url) === 0) {
							$check = 0;
						}
					}

					if ($check) {
						$string = $LiveSite . '/' . $string;
					}
				}
			}
			$strings[$string] = $string;
		}
	}
	return $strings[$string];
}
?>