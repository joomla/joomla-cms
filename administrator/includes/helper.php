<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

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
	 * Use JApplicationHelper::getComponent() instead
	 *
	 * @access public
	 * @return string Option
	 * @since 1.5
	 * @deprecated 1.6
	 */
	function findOption()
	{
		$option = NULL;

		$user = JFactory::getUser();
		if ($user->get('guest')) {
			$option = 'com_login';
		}

		if(empty($option)) {
			$option = JApplicationHelper::getComponent('com_cpanel');
		}

		return $option;
	}
}

?>