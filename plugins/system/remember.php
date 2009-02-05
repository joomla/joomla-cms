<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * Joomla! System Remember Me Plugin
 *
 * @package		Joomla
 * @subpackage	System
 */
class plgSystemRemember extends JPlugin
{
	function onAfterInitialise()
	{
		$appl = JFactory::getApplication();

		// No remember me for admin
		if ($appl->isAdmin()) {
			return;
		}

		$user = &JFactory::getUser();
		if (!$user->get('gid'))
		{
			jimport('joomla.utilities.utility');
			$hash = JUtility::getHash('JLOGIN_REMEMBER');

			if ($str = JRequest::getString($hash, '', 'cookie', JREQUEST_ALLOWRAW | JREQUEST_NOTRIM))
			{
				jimport('joomla.utilities.simplecrypt');

				//Create the encryption key, apply extra hardening using the user agent string
				$key = JUtility::getHash(@$_SERVER['HTTP_USER_AGENT']);

				$crypt	= new JSimpleCrypt($key);
				$str	= $crypt->decrypt($str);

				$options = array();
				$options['silent']	= true;
				$options['action']	= 'core.site.login';
				if (!$appl->login(@unserialize($str), $options)) {
					// Clear the remember me cookie
					setcookie( JUtility::getHash('JLOGIN_REMEMBER'), false, time() - 86400, '/' );
				}
			}
		}
	}
}