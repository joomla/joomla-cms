<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Share Table class.
 *
 * @since  _DEPLOY_VERSION_
 */
class ContentTableDraft extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__content_draft', 'id', $db);
	}

	/**
	 * Perform some sanity checks.
	 *
	 * @return  bool  True if all is OK | False otherwise.
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function check()
	{
		if ((int) $this->get('id') === 0)
		{
			$date = new JDate;

			$this->set('created', $date->toSql());
		}

		return true;
	}

	/**
	 * Load an existing token.
	 *
	 * @param   int  $articleId  The article ID to check a token for.
	 *
	 * @return  mixed  Token if found, null otherwise.
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function loadToken($articleId)
	{
		// Check if we have an existing token
		$query = $this->_db->getQuery(true)
			->select($this->_db->quoteName('sharetoken'))
			->from($this->_db->quoteName('#__content_draft'))
			->where($this->_db->quoteName('articleId') . '=' . (int) $articleId);
		$this->_db->setQuery($query)->execute();

		return $this->_db->loadResult();
	}

	/**
	 * Load a draft ID.
	 *
	 * @param   string  $token  The token to load the ID for.
	 *
	 * @return  mixed  Draft ID if found, otherwise null.
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function loadDraftId($token)
	{
		$tokenQuery = $this->_db->getQuery(true)
			->select($this->_db->quoteName('id'))
			->from($this->_db->quoteName('#__content_draft'))
			->where($this->_db->quoteName('sharetoken') . ' = ' . $this->_db->quote($token));
		$this->_db->setQuery($tokenQuery);

		return $this->_db->loadResult();
	}
}
