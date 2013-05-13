<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

class FOFToolbar
{

	/** @var array Configuration parameters */
	protected $config = array();

	/** @var array Input (e.g. request) variables */
	protected $input = array();

	/** @var arary Permissions map, see the __construct method for more information */
	public $perms = array();

	/** @var array The links to be rendered in the toolbar */
	protected $linkbar = array();

	/** @var bool Should I render the submenu in the front-end? */
	protected $renderFrontendSubmenu = false;

	/** @var bool Should I render buttons in the front-end? */
	protected $renderFrontendButtons = false;

	/**
	 *
	 * @staticvar array $instances
	 * @param type $option
	 * @param type $view
	 * @param type $config
	 * @return FOFToolbar
	 */
	public static function &getAnInstance($option = null, $config = array())
	{
		static $instances = array();

		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array)$config;
		} elseif (!is_array($config))
		{
			$config = array();
		}

		$hash = $option;
		if (!array_key_exists($hash, $instances))
		{
			if (array_key_exists('input', $config))
			{
				if ($config['input'] instanceof FOFInput)
				{
					$input = $config['input'];
				}
				else
				{
					$input = new FOFInput($config['input']);
				}
			}
			else
			{
				$input = new FOFInput();
			}
			$config['option'] = !is_null($option) ? $option : $input->getCmd('option', 'com_foobar');
			$input->set('option', $config['option']);
			$config['input'] = $input;

			$className = ucfirst(str_replace('com_', '', $config['option'])) . 'Toolbar';
			if (!class_exists($className))
			{
				list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
				if ($isAdmin)
				{
					$basePath = JPATH_ADMINISTRATOR;
				}
				elseif ($isCli)
				{
					$basePath = JPATH_ROOT;
				}
				else
				{
					$basePath = JPATH_SITE;
				}

				$searchPaths = array(
					$basePath . '/components/' . $config['option'],
					$basePath . '/components/' . $config['option'] . '/toolbars',
					JPATH_ADMINISTRATOR . '/components/' . $config['option'],
					JPATH_ADMINISTRATOR . '/components/' . $config['option'] . '/toolbars'
				);
				if (array_key_exists('searchpath', $config))
				{
					array_unshift($searchPaths, $config['searchpath']);
				}

				JLoader::import('joomla.filesystem.path');
				$path = JPath::find(
						$searchPaths, 'toolbar.php'
				);

				if ($path)
				{
					require_once $path;
				}
			}

			if (!class_exists($className))
			{
				$className = 'FOFToolbar';
			}
			$instance = new $className($config);

			$instances[$hash] = $instance;
		}

		return $instances[$hash];
	}

	public function __construct($config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array)$config;
		} elseif (!is_array($config))
		{
			$config = array();
		}

		// Cache the config
		$this->config = $config;

		// Get the input for this MVC triad
		if (array_key_exists('input', $config))
		{
			$this->input = $config['input'];
		}
		else
		{
			$this->input = new FOFInput();
		}

		// Get the default values for the component and view names
		$this->component = $this->input->getCmd('option', 'com_foobar');

		// Overrides from the config
		if (array_key_exists('option', $config))
			$this->component = $config['option'];

		$this->input->set('option', $this->component);

		// Get default permissions (can be overriden by the view)
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();

		$user = JFactory::getUser();
		$perms = (object) array(
				'manage'	 => $user->authorise('core.manage', $this->input->getCmd('option', 'com_foobar')),
				'create'	 => $user->authorise('core.create', $this->input->getCmd('option', 'com_foobar')),
				'edit'		 => $user->authorise('core.edit', $this->input->getCmd('option', 'com_foobar')),
				'editstate'	 => $user->authorise('core.edit.state', $this->input->getCmd('option', 'com_foobar')),
				'delete'	 => $user->authorise('core.delete', $this->input->getCmd('option', 'com_foobar')),
		);

		// Save front-end toolbar and submenu rendering flags if present in the config
		if (array_key_exists('renderFrontendButtons', $config))
			$this->renderFrontendButtons = $config['renderFrontendButtons'];
		if (array_key_exists('renderFrontendSubmenu', $config))
			$this->renderFrontendSubmenu = $config['renderFrontendSubmenu'];

		//if not in the administrative area, load the JToolbarHelper
		if (!$isAdmin)
		{
			//pretty ugly require...
			require_once(JPATH_ROOT . '/administrator/includes/toolbar.php');

			// Things to do if we have to render a front-end toolbar
			if ($this->renderFrontendButtons)
			{
				// Load back-end toolbar language files in front-end
				$jlang = JFactory::getLanguage();
				$jlang->load('', JPATH_ADMINISTRATOR, 'en-GB', true);
				$jlang->load('', JPATH_ADMINISTRATOR, null, true);

				// Load the core Javascript
				JHtml::_('behavior.framework', true);
			}
		}

		// Store permissions in the local toolbar object
		$this->perms = $perms;
	}

	public function renderToolbar($view = null, $task = null, $input = null)
	{
		if (!empty($input))
		{
			$saveInput = $this->input;
			$this->input = $input;
		}

		// If there is a render.toolbar=0 in the URL, do not render a toolbar
		if (!$this->input->getBool('render.toolbar', true))
			return;

		// Get the view and task
		if (empty($view))
			$view = $this->input->getCmd('view', 'cpanel');
		if (empty($task))
			$task = $this->input->getCmd('task', 'default');

		$this->view = $view;
		$this->task = $task;

		$view = FOFInflector::pluralize($view);

		// Check for an onViewTask method
		$methodName = 'on' . ucfirst($view) . ucfirst($task);
		if (method_exists($this, $methodName))
		{
			return $this->$methodName();
		}

		// Check for an onView method
		$methodName = 'on' . ucfirst($view);
		if (method_exists($this, $methodName))
		{
			return $this->$methodName();
		}

		// Check for an onTask method
		$methodName = 'on' . ucfirst($task);
		if (method_exists($this, $methodName))
		{
			return $this->$methodName();
		}

		if (!empty($input))
		{
			$this->input = $saveInput;
		}
	}

	/**
	 * Renders the toolbar for the component's Control Panel page
	 */
	public function onCpanelsBrowse()
	{
		//on frontend, buttons must be added specifically
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();

		if ($isAdmin || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!$isAdmin && !$this->renderFrontendButtons)
			return;

		$option = $this->input->getCmd('option', 'com_foobar');

		JToolBarHelper::title(JText::_(strtoupper($option)), str_replace('com_', '', $option));
		JToolBarHelper::preferences($option, 550, 875);
	}

	/**
	 * Renders the toolbar for the component's Browse pages (the plural views)
	 */
	public function onBrowse()
	{
		//on frontend, buttons must be added specifically
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();

		if ($isAdmin || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!$isAdmin && !$this->renderFrontendButtons)
			return;

		// Set toolbar title
		$option = $this->input->getCmd('option', 'com_foobar');
		$subtitle_key = strtoupper($option . '_TITLE_' . $this->input->getCmd('view', 'cpanel'));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', str_replace('com_', '', $option));

		// Add toolbar buttons
		if ($this->perms->create)
		{
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				JToolBarHelper::addNew();
			}
			else
			{
				JToolBarHelper::addNewX();
			}
		}
		if ($this->perms->edit)
		{
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				JToolBarHelper::editList();
			}
			else
			{
				JToolBarHelper::editListX();
			}
		}
		if ($this->perms->create || $this->perms->edit)
		{
			JToolBarHelper::divider();
		}

		if ($this->perms->editstate)
		{
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::divider();
		}
		if ($this->perms->delete)
		{
			$msg = JText::_($this->input->getCmd('option', 'com_foobar') . '_CONFIRM_DELETE');
			JToolBarHelper::deleteList($msg);
		}
	}

	/**
	 * Renders the toolbar for the component's Read pages
	 */
	public function onRead()
	{
		//on frontend, buttons must be added specifically
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();

		if ($isAdmin || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!$isAdmin && !$this->renderFrontendButtons)
			return;

		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);

		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . $this->input->getCmd('view', 'cpanel') . '_READ');
		JToolBarHelper::title(JText::_(strtoupper($option)) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', $componentName);

		// Set toolbar icons
		JToolBarHelper::back();
	}

	public function onAdd()
	{
		//on frontend, buttons must be added specifically
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		if (!$isAdmin && !$this->renderFrontendButtons)
			return;

		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);

		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . FOFInflector::pluralize($this->input->getCmd('view', 'cpanel'))) . '_EDIT';
		JToolBarHelper::title(JText::_(strtoupper($option)) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', $componentName);

		// Set toolbar icons
		JToolBarHelper::apply();
		JToolBarHelper::save();
		JToolBarHelper::custom('savenew', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		JToolBarHelper::cancel();
	}

	public function onEdit()
	{
		//on frontend, buttons must be added specifically
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		if (!$isAdmin && !$this->renderFrontendButtons)
			return;

		$this->onAdd();
	}

	/**
	 * Removes all links from the link bar
	 */
	public function clearLinks()
	{
		$this->linkbar = array();
	}

	/**
	 * Get the link bar's link definitions
	 * @return array
	 */
	public function &getLinks()
	{
		return $this->linkbar;
	}

	/**
	 * Append a link to the link bar
	 *
	 * @param string $name The text of the link
	 * @param string|null $link The link to render; set to null to render a separator
	 * @param bool $active True if it's an active link
	 * @param string|null $icon Icon class (used by some renderers, like the Bootstrap renderer)
	 * @param string|null $parent The parent element (referenced by name)) Thsi will create a dropdown list
	 */
	public function appendLink($name, $link = null, $active = false, $icon = null, $parent = '')
	{
		$linkDefinition = array(
			'name'	 => $name,
			'link'	 => $link,
			'active' => $active,
			'icon'	 => $icon
		);
		if (empty($parent))
		{
			$this->linkbar[$name] = $linkDefinition;
		}
		else
		{
			if (!array_key_exists($parent, $this->linkbar))
			{
				$parentElement = $linkDefinition;
				$parentElement['link'] = null;
				$this->linkbar[$parent] = $parentElement;
				$parentElement['items'] = array();
			}
			else
			{
				$parentElement = $this->linkbar[$parent];
				if (!array_key_exists('dropdown', $parentElement) && !empty($parentElement['link']))
				{
					$newSubElement = $parentElement;
					$parentElement['items'] = array($newSubElement);
				}
			}

			$parentElement['items'][] = $linkDefinition;
			$parentElement['dropdown'] = true;

			$this->linkbar[$parent] = $parentElement;
		}
	}

	/**
	 * Prefixes (some people erroneously call this "prepend" – there is no such word) a link to the link bar
	 *
	 * @param string $name The text of the link
	 * @param string|null $link The link to render; set to null to render a separator
	 * @param bool $active True if it's an active link
	 * @param srting|null $icon Icon class (used by some renderers, like the Bootstrap renderer)
	 */
	public function prefixLink($name, $link = null, $active = false, $icon = null)
	{
		$linkDefinition = array(
			'name'	 => $name,
			'link'	 => $link,
			'active' => $active,
			'icon'	 => $icon
		);
		array_unshift($this->linkbar, $linkDefinition);
	}

	/**
	 * Renders the submenu (toolbar links) for all detected views of this component
	 */
	protected function renderSubmenu()
	{
		$views = $this->getMyViews();
		if (empty($views))
			return;

		$activeView = $this->input->getCmd('view', 'cpanel');

		foreach ($views as $view)
		{
			// Get the view name
			$key = strtoupper($this->component) . '_TITLE_' . strtoupper($view);
			if (strtoupper(JText::_($key)) == $key)
			{
				$altview = FOFInflector::isPlural($view) ? FOFInflector::singularize($view) : FOFInflector::pluralize($view);
				$key2 = strtoupper($this->component) . '_TITLE_' . strtoupper($altview);
				if (strtoupper(JText::_($key2)) == $key2)
				{
					$name = ucfirst($view);
				}
				else
				{
					$name = JText::_($key2);
				}
			}
			else
			{
				$name = JText::_($key);
			}

			$link = 'index.php?option=' . $this->component . '&view=' . $view;

			$active = $view == $activeView;

			$this->appendLink($name, $link, $active);
		}
	}

	/**
	 * Automatically detects all views of the component
	 *
	 * @return array
	 */
	protected function getMyViews()
	{
		$views = array();
		$t_views = array();
		$using_meta = false;

		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		if ($isAdmin)
		{
			$basePath = JPATH_ADMINISTRATOR;
		}
		elseif ($isCli)
		{
			$basePath = JPATH_ROOT;
		}
		else
		{
			$basePath = JPATH_SITE;
		}
		$searchPath = $basePath . '/components/' . $this->component . '/views';

		JLoader::import('joomla.filesystem.folder');
		JLoader::import('joomla.utilities.arrayhelper');

		$allFolders = JFolder::folders($searchPath);

		if (!empty($allFolders))
			foreach ($allFolders as $folder)
			{
				$view = $folder;

				// View already added
				if (in_array(FOFInflector::pluralize($view), $t_views))
					continue;

				// Do we have a 'skip.xml' file in there?
				$files = JFolder::files($searchPath . '/' . $view, '^skip\.xml$');
				if (!empty($files))
					continue;

				//Do we have extra information about this view? (ie. ordering)
				$meta = JFolder::files($searchPath . '/' . $view, '^metadata\.xml$');

				//Not found, do we have it inside the plural one?
				if (!$meta)
				{
					$plural = FOFInflector::pluralize($view);
					if (in_array($plural, $allFolders))
					{
						$view = $plural;
						$meta = JFolder::files($searchPath . '/' . $view, '^metadata\.xml$');
					}
				}

				if (!empty($meta))
				{
					$using_meta = true;
					$xml = simplexml_load_file($searchPath . '/' . $view . '/' . $meta[0]);
					$order = (int) $xml->foflib->ordering;
				}
				else
				{
					// Next place. It's ok since the index are 0-based and count is 1-based
					if (!isset($to_order))
						$to_order = array();
					$order = count($to_order);
				}

				$view = FOFInflector::pluralize($view);

				$t_view = new stdClass();
				$t_view->ordering = $order;
				$t_view->view = $view;

				$to_order[] = $t_view;
				$t_views[] = $view;
			}

		JArrayHelper::sortObjects($to_order, 'ordering');
		$views = JArrayHelper::getColumn($to_order, 'view');

		//if not using the metadata file, let's put the cpanel view on top
		if (!$using_meta)
		{
			$cpanel = array_search('cpanels', $views);
			if ($cpanel !== false)
			{
				unset($views[$cpanel]);
				array_unshift($views, 'cpanels');
			}
		}

		return $views;
	}

	/**
	 * Return the front-end toolbar rendering flag
	 *
	 * @return bool
	 */
	public function getRenderFrontendButtons()
	{
		return $this->renderFrontendButtons;
	}

	/**
	 * Return the front-end submenu rendering flag
	 *
	 * @return bool
	 */
	public function getRenderFrontendSubmenu()
	{
		return $this->renderFrontendSubmenu;
	}

}