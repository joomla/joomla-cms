<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');
jimport('joomla.application.component.helper');

/**
 * Language model for the Joomla Languages component.
 *
 * @package		Joomla.Site
 * @subpackage	com_languages
 */
class LanguagesModelLanguage extends JModel
{
	protected $_context = 'com_languages';

	/**
	 * Clicks the URL, incrementing the counter
	 */
	function select()
	{
		$user = JFactory::getUser();
		if($user->id)
		{
			$user->setParam('language',$this->getState('language.tag'));
			// Save the user to the database.
			if (!$user->save(true)) {
				return new JException(JText::sprintf('USERS_USER_SAVE_FAILED', $user->getError()), 500);
			}
		}
		else
		{
			$config =& JFactory::getConfig();
			$cookie_domain = $config->get('config.cookie_domain', '');
			$cookie_path = $config->get('config.cookie_path', '/');
			jimport('joomla.utilities.utility');
			setcookie(JUtility::getHash($this->_context.'.tag'), $this->getState('language.tag'), time() + 365 * 86400, $cookie_path, $cookie_domain);
		}
	}
}

