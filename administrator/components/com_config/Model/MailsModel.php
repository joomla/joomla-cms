<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Methods supporting a list of mail records.
 *
 * @since  4.0.0
 */
class MailsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 * 
	 * @since   4.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'mailkey', 'a.mailkey',
				'maildesc', 'a.maildesc',
				'component', 'a.component',
				'subject', 'a.subject',
				'body', 'a.body'
			);
		}

		parent::__construct($config);
	}


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   4.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Load the parameters.
		$params = \JComponentHelper::getParams('com_adminmails');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.mailkey', 'asc');
	}

	public function getItems()
	{
		$items = parent::getItems();

		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('language')
			->from('#__mail_templates')
			->order('language ASC');

		foreach ($items as $item)
		{
			$query->clear('where')
				->where('mail_id = ' . $db->q($item->mail_id));
			$db->setQuery($query);
			$item->languages = $db->loadColumn();
		}

		return $items;
	}
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 * 
	 * @since   4.0.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= Factory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from($db->quoteName('#__mail_templates').' AS a')->group('a.mail_id');

		return $query;
	}

	public function getLanguages()
	{
		return LanguageHelper::getContentLanguages(array(0,1));
	}
}
