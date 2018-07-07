<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Languages Strings JSON Controller
 *
 * @since  2.5
 */
class LanguagesControllerStrings extends JControllerAdmin
{
	/**
	 * Method for refreshing the cache in the database with the known language strings
	 *
	 * @return  void
	 *
	 * @since		2.5
	 */
	public function refresh()
	{
		echo new JResponseJson($this->getModel('strings')->refresh());
	}

	/**
	 * Method for searching language strings
	 *
	 * @return  void
	 *
	 * @since		2.5
	 */
	public function search()
	{
		echo new JResponseJson($this->getModel('strings')->search());
	}
}
