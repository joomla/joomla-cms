<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mails
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mails\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;

/**
 * Methods supporting a list of mail template records.
 *
 * @since  __DEPLOY_VERSION__
 */
class TemplatesModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'template_id', 'a.template_id',
				'language', 'a.language',
				'subject', 'a.subject',
				'body', 'a.body',
				'htmlnody', 'a.htmlbody'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Load the parameters.
		$params = ComponentHelper::getParams('com_mails');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.template_id', 'asc');
	}

	/**
	 * Get a list of mail templates
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItems()
	{
		$items = parent::getItems();
		$id    = '';

		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('language'))
			->from($db->quoteName('#__mail_templates'))
			->where($db->quoteName('template_id') . ' = :id')
			->order($db->quoteName('language') . ' ASC')
			->bind(':id', $id);

		foreach ($items as $item)
		{
			$id = $item->template_id;
			$db->setQuery($query);
			$item->languages = $db->loadColumn();
		}

		return $items;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  QueryInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				$db->quoteName('a') . '.*'
			)
		);
		$query->from($db->quoteName('#__mail_templates', 'a'))
			->group(
				[
					$db->quoteName('a.template_id'),
					$db->quoteName('a.language'),
					$db->quoteName('a.subject'),
					$db->quoteName('a.body'),
					$db->quoteName('a.htmlbody'),
					$db->quoteName('a.attachments'),
					$db->quoteName('a.params'),
				]
			);

		// Filter by search in title.
		if ($search = trim($this->getState('filter.search')))
		{
			if (stripos($search, 'id:') === 0)
			{
				$search = substr($search, 3);
				$query->where($db->quoteName('a.template_id') . ' = :search')
					->bind(':search', $search);
			}
			else
			{
				$search = '%' . str_replace(' ', '%', $search) . '%';
				$query->where(
					'(' . $db->quoteName('a.template_id') . ' LIKE :search1'
					. ' OR ' . $db->quoteName('a.subject') . ' LIKE :search2'
					. ' OR ' . $db->quoteName('a.body') . ' LIKE :search3'
					. ' OR ' . $db->quoteName('a.htmlbody') . ' LIKE :search4)'
				)
				->bind([':search1', ':search2', ':search3', ':search4'], $search);
			}
		}

		// Filter on the extension.
		if ($extension = $this->getState('filter.extension'))
		{
			$extension = $extension . '.%';
			$query->where($db->quoteName('a.template_id') . ' LIKE :extension')
				->bind(':extension', $extension);
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where($db->quoteName('a.language') . ' = :language')
				->bind(':language', $language);
		}

		return $query;
	}

	/**
	 * Get a list of the current content languages
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getLanguages()
	{
		return LanguageHelper::getContentLanguages(array(0,1));
	}
}
