<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Templates\Administrator\Helper\TemplatesHelper;

/**
 * Methods supporting a list of template style records.
 *
 * @since  1.6
 */
class StylesModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 * @since   3.2
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'template', 'a.template',
				'home', 'a.home',
				'menuitem',
			);
		}

		parent::__construct($config, $factory);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = 'a.template', $direction = 'asc')
	{
		$app = Factory::getApplication();

		if (!$app->isClient('api'))
		{
			// Load the filter state.
			$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
			$this->setState('filter.template', $this->getUserStateFromRequest($this->context . '.filter.template', 'filter_template', '', 'string'));
			$this->setState('filter.menuitem', $this->getUserStateFromRequest($this->context . '.filter.menuitem', 'filter_menuitem', '', 'cmd'));

			// Special case for the client id.
			$clientId = (int) $this->getUserStateFromRequest($this->context . '.client_id', 'client_id', 0, 'int');
			$clientId = !in_array($clientId, [0, 1]) ? 0 : $clientId;
			$this->setState('client_id', $clientId);
		}

		// Load the parameters.
		$params = ComponentHelper::getParams('com_templates');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('client_id');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.template');
		$id .= ':' . $this->getState('filter.menuitem');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  \JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$clientId = (int) $this->getState('client_id');

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.template, a.title, a.home, a.client_id, l.title AS language_title, l.image as image, l.sef AS language_sef'
			)
		);
		$query->from($db->quoteName('#__template_styles', 'a'))
			->where($db->quoteName('a.client_id') . ' = :clientid')
			->bind(':clientid', $clientId, ParameterType::INTEGER);

		// Join on menus.
		$query->select('COUNT(' . $db->quoteName('m.template_style_id') . ') AS assigned')
			->join('LEFT', $db->quoteName('#__menu', 'm') . ' ON ' . $db->quoteName('m.template_style_id') . ' = ' . $db->quoteName('a.id'))
			->group($db->quoteName(['a.id', 'a.template', 'a.title', 'a.home', 'a.client_id', 'l.title', 'l.image', 'l.sef', 'e.extension_id']));

		// Join over the language.
		$query->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.home'));

		// Filter by extension enabled.
		$query->select($db->quoteName('extension_id', 'e_id'))
			->join('LEFT', $db->quoteName('#__extensions', 'e') . ' ON e.element = a.template AND e.client_id = a.client_id')
			->where($db->quoteName('e.enabled') . ' = 1')
			->where($db->quoteName('e.type') . ' = ' . $db->quote('template'));

		// Filter by template.
		if ($template = $this->getState('filter.template'))
		{
			$query->where($db->quoteName('a.template') . ' = :template')
				->bind(':template', $template);
		}

		// Filter by menuitem.
		$menuItemId = $this->getState('filter.menuitem');

		if ($clientId === 0 && is_numeric($menuItemId))
		{
			// If user selected the templates styles that are not assigned to any page.
			if ((int) $menuItemId === -1)
			{
				// Only custom template styles overrides not assigned to any menu item.
				$query->where($db->quoteName('a.home') . ' = ' . $db->quote(0))
					->where($db->quoteName('m.id') . ' IS NULL');
			}
			// If user selected the templates styles assigned to particular pages.
			else
			{
				// Subquery to get the language of the selected menu item.
				$menuItemId = (int) $menuItemId;
				$menuItemLanguageSubQuery = $db->getQuery(true);
				$menuItemLanguageSubQuery->select($db->quoteName('language'))
					->from($db->quoteName('#__menu'))
					->where($db->quoteName('id') . ' = :menuitemid')
					->bind(':menuiteid', $menuItemId, ParameterType::INTEGER);

				// Subquery to get the language of the selected menu item.
				$templateStylesMenuItemsSubQuery = $db->getQuery(true);
				$templateStylesMenuItemsSubQuery->select($db->quoteName('id'))
					->from($db->quoteName('#__menu'))
					->where($db->quoteName('template_style_id') . ' = ' . $db->quoteName('a.id'));

				// Main query where clause.
				$query->where('(' .
					// Default template style (fallback template style to all menu items).
					$db->quoteName('a.home') . ' = ' . $db->quote(1) . ' OR ' .
					// Default template style for specific language (fallback template style to the selected menu item language).
					$db->quoteName('a.home') . ' IN (' . $menuItemLanguageSubQuery . ') OR ' .
					// Custom template styles override (only if assigned to the selected menu item).
					'(' . $db->quoteName('a.home') . ' = ' . $db->quote(0) . ' AND ' . $menuItemId . ' IN (' . $templateStylesMenuItemsSubQuery . '))' .
					')'
				);
			}
		}

		// Filter by search in title.
		if ($search = $this->getState('filter.search'))
		{
			if (stripos($search, 'id:') === 0)
			{
				$ids = (int) substr($search, 3);
				$query->where($db->quoteName('a.id') . ' = :id');
				$query->bind(':id', $ids, ParameterType::INTEGER);
			}
			else
			{
				$search = '%' . strtolower($search) . '%';
				$query->where('LOWER(' . $db->quoteName('a.template') . ') LIKE :template')
					->orWhere('LOWER(' . $db->quoteName('a.title') . ') LIKE :title')
					->bind(':template', $search)
					->bind(':title', $search);
			}
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.template')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Override parent getItems to add extra XML metadata.
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as &$item)
		{

			// Style Title
			$item->title = trim(str_ireplace($item->template . ' - ', '', $item->title));

			// Thumbnail & Preview
			$template = $item->template;
			$client = ApplicationHelper::getClientInfo($item->client_id);
			$basePath = $client->path . '/templates/' . $template;
			$baseUrl = ($item->client_id == 0) ? Uri::root(true) : Uri::root(true) . '/administrator';
			$thumb = $basePath . '/template_thumbnail.png';
			$preview = $basePath . '/template_preview.png';

			if (file_exists($thumb) || file_exists($preview))
			{

				if (file_exists($thumb))
				{
					$item->thumbnail = $baseUrl . '/templates/' . $template . '/template_thumbnail.png';
				}

				if (file_exists($preview))
				{
					$item->preview = $item->thumbnail = $baseUrl . '/templates/' . $template . '/template_thumbnail.png';
				}
			}

			// xml data
			$item->xmldata = TemplatesHelper::parseXMLTemplateFile($client->path, $template);
			$num = $this->updated($item->e_id);

			if ($num)
			{
				$item->updated = $num;
			}
		}

		return $items;
	}

	/**
	 * Check if template extension have any updated override.
	 *
	 * @param   integer  $exid  Extension id of template.
	 *
	 * @return   boolean  False if records not found/else integer.
	 *
	 * @since   4.0.0
	 */
	public function updated($exid)
	{
		$db = Factory::getDbo();

		// Select the required fields from the table
		$query = $db->getQuery(true)
			->select('a.template')
			->from($db->quoteName('#__template_overrides', 'a'))
			->where('extension_id = ' . $db->quote($exid))
			->where('state = 0');

		// Reset the query.
		$db->setQuery($query);

		// Load the results as a list of stdClass objects.
		$num = count($db->loadObjectList());

		if ($num > 0)
		{
			return $num;
		}

		return false;
	}
}
