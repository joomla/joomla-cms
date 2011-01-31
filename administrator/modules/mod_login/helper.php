<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	mod_login
 * @since		1.6
 */
abstract class modLoginHelper
{
	/**
	 * Get an HTML select list of the available languages.
	 *
	 * @return	string
	 */
	public static function getLanguageList()
	{
		jimport('joomla.language.helper');
		$languages = array();
		$languages = JLanguageHelper::createLanguageList(null, JPATH_BASE, false, true);
		array_unshift($languages, JHtml::_('select.option', '', JText::_('JDEFAULT')));
		return JHtml::_('select.genericlist', $languages, 'lang', ' class="inputbox"', 'value', 'text', null);
	}

	/**
	 * Get the redirect URI after login.
	 *
	 * @return	string
	 */
	public static function getReturnURI()
	{
		$uri = JFactory::getURI();
		$return = 'index.php'.$uri->toString(array('query'));
		if($return != 'index.php?option=com_login'){
			return base64_encode($return);
		} else {
			return base64_encode('index.php');
		}
	}
}