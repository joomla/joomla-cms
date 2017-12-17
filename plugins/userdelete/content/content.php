<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Userdelete.content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Multilanguage;

/**
 * Joomla! User delete content Plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgUserdeleteContent extends JPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Check existence of content items for the user name
	 *
	 * Method is called before user data is deleted from the database
	 *
	 * @param   array   $user  Holds the user data
	 * @param   string  $msg   Message
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onSystemUserBeforeDelete($user)
	{
		$this->loadLanguage();

		$response            = array();
		$response['success'] = true;
		$response['message'] = JText::_('PLG_USERDELETE_CONTENT_MESSAGE');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__content'))
			->where($this->db->quoteName('created_by') . ' = ' . (int) $user['id'])
			->orWhere($this->db->quoteName('modified_by') . ' = ' . (int) $user['id']);
		$this->db->setQuery($query);

		try
		{
			$items = $this->db->loadRowList();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			return $response;
		}

		if (($items !== false) && (count($items) > 0))
		{
			// The user have contents
			return $response;
		}

		$response['success'] = false;
		return $response;
	}
}
