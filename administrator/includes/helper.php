<?php
/**
 * @version		$Id: helper.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
* Joomla! Administrator Application helper class
*
* Provide many supporting API functions
*
 * @package		Joomla.Administrator
* @final
 */
class JAdministratorHelper
{
	/**
	 * Return the application option string [main component]
	 *
	 * @access public
	 * @return string Option
	 * @since 1.5
	 */
	function findOption()
	{
		$option = strtolower(JRequest::getCmd('option'));

		$user = &JFactory::getUser();
		if ($user->get('guest')) {
			$option = 'com_login';
		}

		if (empty($option)) {
			$option = 'com_cpanel';
		}

		JRequest::setVar('option', $option);
		return $option;
	}
}

?>