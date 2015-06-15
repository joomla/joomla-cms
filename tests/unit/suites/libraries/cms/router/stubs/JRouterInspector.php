<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for JRouter
 *
 * @package     Joomla.UnitTest
 * @subpackage  Router
 * @since       3.4
 */
class JRouterInspector extends JRouter
{
	/**
	 * Runs the protected createURI() method
	 * 
	 * @param   array   $url  valid inputs to the createURI() method
	 *
	 * @return  object  JURI object from the given parameters
	 *
	 * @since   3.4
	 */
	public function runCreateURI($url)
	{
		return $this->createURI($url);
	}

	/**
	 * Runs the protected encodeSegments() method
	 * 
	 * @param   array   $segments  array of URL segments
	 *
	 * @return  mixed  Array of encoded segments
	 *
	 * @since   3.4
	 */
	public function runEncodeSegments($segments)
	{
		return $this->encodeSegments($segments);
	}

	/**
	 * Runs the protected decodeSegments() method
	 *
	 * @param   array   $segments  array of URL segments
 	 *
	 * @return  mixed  Array of decoded segments
	 *
	 * @since   3.4
	 */
	public function runDecodeSegments($segments)
	{
		return $this->decodeSegments($segments);
	}

	/**
	 * Returns the rules-array
	 * 
	 * @return array  Array of rules
	 * 
	 * @since  3.4
	 */
	public function getRules()
	{
		return $this->_rules;
	}

	/**
	 * Clear instance of JRouter
	 * 
	 * @return void
	 * 
	 * @since  3.4
	 */
	public static function clearInstanceCache()
	{
		foreach (self::$instances as $key => $value)
		{
			unset(self::$instances[$key]);
		}
	}

	/**
	 * Runs the protected processParseRules() method
	 *
	 * @param   JUri    &$uri   The URI to parse
	 * @param   string  $stage  The stage that should be processed.
 	 *
	 * @return  mixed  Array of decoded segments
	 *
	 * @since   3.4
	 */
	public function runProcessParseRules(&$uri, $stage = self::PROCESS_DURING)
	{
		return $this->processParseRules($uri, $stage);
	}

	/**
	 * Runs the protected processBuildRules() method
	 *
	 * @param   JUri    &$uri   The URI to parse
	 * @param   string  $stage  The stage that should be processed.
 	 *
	 * @return  mixed  Array of decoded segments
	 *
	 * @since   3.4
	 */
	public function runProcessBuildRules(&$uri, $stage = self::PROCESS_DURING)
	{
		return $this->processBuildRules($uri, $stage);
	}
}
