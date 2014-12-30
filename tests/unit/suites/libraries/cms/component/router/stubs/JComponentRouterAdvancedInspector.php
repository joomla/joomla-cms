<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for JComponentRouterAdvanced
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.4
 */
class JComponentRouterAdvancedInspector extends JComponentRouterAdvanced
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
}
