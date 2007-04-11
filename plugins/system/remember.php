<?php
/**
* @version		$Id: log.php 7104 2007-04-08 16:17:14Z jinx $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Joomla! System Remember Me Plugin
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	System
 */
class  plgSystemRemember extends JPlugin
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
	function plgSystemRemember(& $subject)
	{
		parent::__construct($subject);

		// load plugin parameters
		$this->_plugin = & JPluginHelper::getPlugin('system', 'remember');
		$this->_params = new JParameter($this->_plugin->params);
	}

	function onAfterInitialise()
	{
		global $mainframe;
		$user = &JFactory::getUser();
		if (!$user->get('gid')) {
			jimport('joomla.utilities.utility');
			$hash = JUTility::getHash('JLOGIN_REMEMBER');
			if (isset($_COOKIE[$hash])) {
				jimport('joomla.utilities.simplecrypt');
				$crypt	= new JSimpleCrypt();
				$str	= $crypt->decrypt($_COOKIE[$hash]);
				if (strpos($str, ':|:')) {
					$credentials = explode(':|:', $str);
					$username = $credentials[0];
					$password = $credentials[1];
					$mainframe->login($username, $password, true);
				}
			}
		}
	}
}