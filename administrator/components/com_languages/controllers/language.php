<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Languages list actions controller.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @version		1.6
 */
class LanguagesControllerLanguage extends JControllerForm
{
	// Define protected variables and custom methods if necessary.
	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param	int		$recordId	The primary key id for the item.
	 * @param	string	$key		The name of the primary key variable.
	 *
	 * @return	string	The arguments to append to the redirect URL.
	 * @since	1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $key = 'lang_id')
	{
		return parent::getRedirectToItemAppend($recordId, $key);
	}
}
