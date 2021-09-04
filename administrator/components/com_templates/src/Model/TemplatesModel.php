<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Templates\Administrator\Helper\TemplatesHelper;
use Joomla\Database\ParameterType;
use Joomla\String\StringHelper;

/**
 * Methods supporting a list of template style records.
 *
 * @since  1.6
 */
class TemplatesModel extends ListModel
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
	 * @return  \Joomla\Database\DatabaseQuery
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
				[
					$db->quoteName('a.id'),
					$db->quoteName('a.template'),
					$db->quoteName('a.title'),
					$db->quoteName('a.home'),
					$db->quoteName('a.client_id'),
					$db->quoteName('a.inheritable'),
					$db->quoteName('a.parent'),
					$db->quoteName('l.title', 'language_title'),
					$db->quoteName('l.image'),
					$db->quoteName('l.sef', 'language_sef'),
				]
			)
		)
			->select(
				[
					'COUNT(' . $db->quoteName('m.template_style_id') . ') AS assigned',
					$db->quoteName('extension_id', 'templateId'),
				]
			)
			->from($db->quoteName('#__template_styles', 'a'))
			->where($db->quoteName('a.client_id') . ' = :clientid')
			->bind(':clientid', $clientId, ParameterType::INTEGER);

		// Join on menus.
		$query->join('LEFT', $db->quoteName('#__menu', 'm'), $db->quoteName('m.template_style_id') . ' = ' . $db->quoteName('a.id'))
			->group(
				[
					$db->quoteName('a.id'),
					$db->quoteName('a.template'),
					$db->quoteName('a.title'),
					$db->quoteName('a.home'),
					$db->quoteName('a.parent'),
					$db->quoteName('l.title'),
					$db->quoteName('l.image'),
					$db->quoteName('l.sef'),
					$db->quoteName('e.extension_id'),
				]
			);

		// Join over the language.
		$query->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.home'));

		// Filter by extension enabled.
		$query->join(
			'LEFT',
			$db->quoteName('#__extensions', 'e'),
			$db->quoteName('e.element') . ' = ' . $db->quoteName('a.template')
			. ' AND ' . $db->quoteName('e.client_id') . ' = ' . $db->quoteName('a.client_id')
		)
			->where(
				[
					$db->quoteName('e.enabled') . ' = 1',
					$db->quoteName('e.type') . ' = ' . $db->quote('template'),
				]
			);

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
				$query->where(
					[
						$db->quoteName('a.home') . ' = ' . $db->quote('0'),
						$db->quoteName('m.id') . ' IS NULL',
					]
				);
			}
			// If user selected the templates styles assigned to particular pages.
			else
			{
				// Subquery to get the language of the selected menu item.
				$menuItemId = (int) $menuItemId;
				$menuItemLanguageSubQuery = $db->getQuery(true);
				$menuItemLanguageSubQuery->select($db->quoteName('language'))
					->from($db->quoteName('#__menu'))
					->where($db->quoteName('id') . ' = :menuitemid');
				$query->bind(':menuitemid', $menuItemId, ParameterType::INTEGER);

				// Subquery to get the language of the selected menu item.
				$templateStylesMenuItemsSubQuery = $db->getQuery(true);
				$templateStylesMenuItemsSubQuery->select($db->quoteName('id'))
					->from($db->quoteName('#__menu'))
					->where($db->quoteName('template_style_id') . ' = ' . $db->quoteName('a.id'));

				// Main query where clause.
				$query->where('(' .
					// Default template style (fallback template style to all menu items).
					$db->quoteName('a.home') . ' = ' . $db->quote('1') . ' OR ' .
					// Default template style for specific language (fallback template style to the selected menu item language).
					$db->quoteName('a.home') . ' IN (' . $menuItemLanguageSubQuery . ') OR ' .
					// Custom template styles override (only if assigned to the selected menu item).
					'(' . $db->quoteName('a.home') . ' = ' . $db->quote('0') . ' AND ' . $menuItemId . ' IN (' . $templateStylesMenuItemsSubQuery . '))' .
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
				$search = '%' . StringHelper::strtolower($search) . '%';
				$query->extendWhere(
					'AND',
					[
						'LOWER(' . $db->quoteName('a.template') . ') LIKE :template',
						'LOWER(' . $db->quoteName('a.title') . ') LIKE :title',
					],
					'OR'
				)
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
		$items = [];
		$rawItems = parent::getItems();

		foreach ($rawItems as &$item)
		{
			$curTemplate = $item->template;

			// Style Title
			$item->title = trim(str_ireplace($item->template . ' - ', '', $item->title));

			// Thumbnail & Preview
			$template = $item->template;
			$client = ApplicationHelper::getClientInfo($item->client_id);

			if (!isset($items[$item->template]))
			{
				// xml data
				$xmldata = TemplatesHelper::parseXMLTemplateFile($client->path, $template);

				// Templates should have valid XML definition
				if (!$xmldata)
				{
					continue;
				}

				$isChild  = $xmldata->get('parent', '');
				$isModern = (bool) $xmldata->get('inheritable', '');

				// If the template is a child, we merge to the parent
				if ($isChild !== '')
				{
					$curTemplate = $isChild;
				}

				$items[$curTemplate] = new \stdClass;
				$items[$curTemplate]->templateName = $curTemplate;
				$items[$curTemplate]->extensionId = $item->templateId;
				$items[$curTemplate]->creationDate = $xmldata->get('creationDate', '');
				$items[$curTemplate]->author = $xmldata->get('author', '');
				$items[$curTemplate]->authorEmail = $xmldata->get('authorEmail', '');
				$items[$curTemplate]->authorUrl = $xmldata->get('authorUrl', '');
				$items[$curTemplate]->version = $xmldata->get('version', '');
				$items[$curTemplate]->description = $xmldata->get('description', '');
				$items[$curTemplate]->copyright = $xmldata->get('copyright', '');
				$items[$curTemplate]->inheritable = $xmldata->get('inheritable', false);
				$items[$curTemplate]->parent = $xmldata->get('parent', '');

				$items[$curTemplate]->styles = [];
				$items[$curTemplate]->childs = [];
			}

			if ($isModern || $isChild !== '')
			{
				$baseUrl = ($item->client_id == 0) ? Uri::root(true) . 'site' : Uri::root(true) . 'administrator';
				$basePath = JPATH_ROOT . '/media/templates/' . $baseUrl . '/' . $template . '/images';
				$baseUrl = '/media/templates/' . $baseUrl . '/' . $template . '/images';
			}
			else
			{
				$baseUrl  = ($item->client_id == 0) ? Uri::root(true) : Uri::root(true) . '/administrator';
				$basePath = $client->path . '/templates/' . $template;
				$baseUrl  = $baseUrl . '/templates/' . $template;
			}

			$thumb = '/template_thumbnail.png';
			$preview = '/template_preview.png';

			if (file_exists($basePath . $thumb) || file_exists($basePath . $preview))
			{

				if (file_exists($basePath . $thumb))
				{
					$items[$curTemplate]->thumbnail = $baseUrl . '/template_thumbnail.png';
				}

				if (file_exists($basePath . $preview))
				{
					$items[$curTemplate]->preview = $item->thumbnail = $baseUrl . '/template_preview.png';
				}
			}
			elseif ($isChild !== '')
			{
				if (file_exists(str_replace('/' . $template . '/', '/' . $isChild . '/', $basePath) . $thumb))
				{
					$items[$curTemplate]->thumbnail = str_replace('/' . $template . '/', '/' . $isChild . '/', $basePath) . $thumb;
				}

				if (file_exists(str_replace('/' . $template . '/', '/' . $isChild . '/', $basePath) . $preview))
				{
					$items[$curTemplate]->preview = str_replace('/' . $template . '/', '/' . $isChild . '/', $basePath) . $preview;
				}
			}

			$num = $this->updated($item->templateId);

			if ($num)
			{
				$item->updated = $num;
			}

			if ($item->inheritable && $item->parent !== '')
			{
				$items[$curTemplate]->childs[] = $item;
			}
			else
			{
				$items[$curTemplate]->styles[] = $item;
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

	/**
	 * Get overrides folder.
	 *
	 * @param   string  $name  The name of override.
	 * @param   string  $path  Location of override.
	 *
	 * @return  object  containing override name and path.
	 *
	 * @since   3.2
	 */
	public function getOverridesFolder($name,$path)
	{
		$folder = new \stdClass;
		$folder->name = $name;
		$folder->path = str_replace(JPATH_ROOT, '', $path . $name); //base64_encode();

		return $folder;
	}

	/**
	 * Get a list of overrides.
	 *
	 * @return  array containing overrides.
	 *
	 * @since   3.2
	 */
	public function getOverridesList($id)
	{
		if (!in_array($id, [0, 1])) {
			return [];
		}

		$client        = ApplicationHelper::getClientInfo($id);
		$componentPath = Path::clean($client->path . '/components/');
		$modulePath    = Path::clean($client->path . '/modules/');
		$pluginPath    = Path::clean(JPATH_ROOT . '/plugins/');
		$layoutPath    = Path::clean(JPATH_ROOT . '/layouts/');
		$components    = Folder::folders($componentPath);
		$lang = Factory::getLanguage();
		$base_dir = $client->path === 'site' ? JPATH_SITE : JPATH_ADMINISTRATOR;
		$language_tag = Factory::getLanguage()->getTag();
		$reload = true;

		foreach ($components as $component)
		{
			$componentObj = ComponentHelper::getComponent($component);
			$extension = Table::getInstance('extension');
			$extension->load($componentObj->id);
			$manifest = new \Joomla\Registry\Registry($extension->manifest_cache);
			$untranslatedName = @$manifest->get('name', $componentObj->name);

			if ($untranslatedName)
			{
				$lang->load($component, $base_dir, $language_tag, $reload);
				$name = Text::_(strtoupper($untranslatedName));
			}
			else
			{
				$name = $untranslatedName;
			}

			// Collect the folders with views
			$folders = Folder::folders($componentPath . '/' . $component, '^view[s]?$', false, true);
			$folders = array_merge($folders, Folder::folders($componentPath . '/' . $component, '^tmpl?$', false, true));

			if (!$folders)
			{
				continue;
			}

			foreach ($folders as $folder)
			{
				// The subfolders are views
				$views = Folder::folders($folder);

				foreach ($views as $view)
				{
					// The old scheme, if a view has a tmpl folder
					$path = $folder . '/' . $view . '/tmpl';

					// The new scheme, the views are directly in the component/tmpl folder
					if (!is_dir($path) && substr($folder, -4) == 'tmpl')
					{
						$path = $folder . '/' . $view;
					}

					// Check if the folder exists
					if (!is_dir($path))
					{
						continue;
					}

					$result['components'][$name][] = $this->getOverridesFolder($view, Path::clean($folder . '/'));
				}
			}
		}

		foreach (Folder::folders($pluginPath) as $pluginGroup)
		{
			foreach (Folder::folders($pluginPath . '/' . $pluginGroup) as $plugin)
			{
				if (file_exists($pluginPath . '/' . $pluginGroup . '/' . $plugin . '/tmpl/'))
				{
					$pluginLayoutPath = Path::clean($pluginPath . '/' . $pluginGroup . '/');
					$result['plugins'][$pluginGroup][] = $this->getOverridesFolder($plugin, $pluginLayoutPath);
				}
			}
		}

		$modules = Folder::folders($modulePath);

		foreach ($modules as $module)
		{
			$res = $this->getOverridesFolder($module, $modulePath);

			if (!isset($result['modules'][$module]))
			{
				$result['modules'][$module] = [];
			}

			$result['modules'][$module][] = $res;
		}

		$layoutFolders = Folder::folders($layoutPath);

		foreach ($layoutFolders as $layoutFolder)
		{
			$layoutFolderPath = Path::clean($layoutPath . '/' . $layoutFolder . '/');
			$layouts = Folder::folders($layoutFolderPath);

			foreach ($layouts as $layout)
			{
				$result['layouts'][$layoutFolder][] = $this->getOverridesFolder($layout, $layoutFolderPath);
			}
		}

		// Check for layouts in component folders
		foreach ($components as $component)
		{
			if (file_exists($componentPath . '/' . $component . '/layouts/'))
			{
				$componentLayoutPath = Path::clean($componentPath . '/' . $component . '/layouts/');

				if ($componentLayoutPath)
				{
					$layouts = Folder::folders($componentLayoutPath);

					foreach ($layouts as $layout)
					{
						$result['layouts'][$component][] = $this->getOverridesFolder($layout, $componentLayoutPath);
					}
				}
			}
		}

		if (!empty($result))
		{
			return $result;
		}
	}
}
