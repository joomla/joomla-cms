<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

// Needed for front end view
/**
 * Base Display Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  controller
 * @since       3.2
*/
class ConfigControllerApplicationDisplay extends ConfigControllerDisplay
{
	/*
	 * Prefix for the view and model classes
	*
	* @var  string
	*/
	public $prefix = 'Config';

	/**
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		return parent::execute();
	}
}
