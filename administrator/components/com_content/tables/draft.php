<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Share Table class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentTableDraft extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__content_draft', 'id', $db);
	}

	/**
	 * Perform some sanity checks.
	 *
	 * @return  boolean  Always returns true.
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function loadToken($articleId)
	{
		// Check if we have an existing token
		$query = $this->getDbo()->getQuery(true)
			->select($this->getDbo()->quoteName('sharetoken'))
			->from($this->getDbo()->quoteName('#__content_draft'))
			->where($this->getDbo()->quoteName('articleId') . '=' . (int) $articleId);
		$this->_db->setQuery($query)->execute();

		return $this->getDbo()->loadResult();
	}

	/**
	 * Load a draft ID.
	 *
	 * @param   string  $token      The token to load the ID for.
	 * @param   int     $articleId  The ID of the article to check.
	 *
	 * @return  mixed  Draft ID if found, otherwise null.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function loadDraftId($token, $articleId)
	{
		$tokenQuery = $this->getDbo()->getQuery(true)
			->select($this->getDbo()->quoteName(array('id', 'sharetoken')))
			->from($this->getDbo()->quoteName('#__content_draft'))
			->where($this->getDbo()->quoteName('sharetoken') . ' = ' . $this->getDbo()->quote($token))
			->where($this->getDbo()->quoteName('articleId') . ' = ' . (int) $articleId);
		$this->getDbo()->setQuery($tokenQuery);

		$data = $this->getDbo()->loadObject();
		$id   = false;

		// Make sure the token is an exact match
		if (is_object($data) && $token === $data->sharetoken)
		{
			$id = $data->id;
		}

		return $id;
	}
}
