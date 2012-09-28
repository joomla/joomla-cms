<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Languages Strings JSON Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 * @since       2.5
 */
class LanguagesControllerStrings extends JControllerAdmin
{
	/**
	 * Constructor
	 *
	 * @param		array	An optional associative array of configuration settings
	 *
	 * @return	void
	 *
	 * @since		2.5
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		require_once JPATH_COMPONENT . '/helpers/jsonresponse.php';
	}

	/**
	 * Method for refreshing the cache in the database with the known language strings
	 *
	 * @return	void
	 *
	 * @since		2.5
	 */
	public function refresh()
	{
		echo new JJsonResponse($this->getModel('strings')->refresh());
	}

	/**
	 * Method for searching language strings
	 *
	 * @return	void
	 *
	 * @since		2.5
	 */
	public function search()
	{
		echo new JJsonResponse($this->getModel('strings')->search());
	}
}
