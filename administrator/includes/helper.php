<?php
/**
 * @package    Joomla.Administrator
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! Administrator Application helper class.
 * Provide many supporting API functions.
 *
 * @package     Joomla.Administrator
 * @subpackage  Application
 * @since       1.5
 */
class JAdministratorHelper
{
	/**
	 * Return the application option string [main component].
	 *
	 * @return	string		Option.
	 * @since	1.5
	 */
	public static function findOption()
	{
		$input = JFactory::getApplication()->input;
		$option = strtolower($input->get('option'));

		$user = JFactory::getUser();
		if (($user->get('guest')) || !$user->authorise('core.login.admin')) {
			$option = 'com_login';
		}

		if (empty($option)) {
			$option = 'com_cpanel';
		}

		$input->set('option', $option);
		return $option;
	}
}
