<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Menus\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');
/**
 * Menu Item Types Model for Menus.
 *
 * @since  1.6
 */
class MenutypesModel extends BaseDatabaseModel
{
	/**
	 * A reverse lookup of the base link URL to Title
	 *
	 * @var  array
	 */
	protected $rlu = array();

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return  void
	 *
	 * @note    Calling getState in this method will result in recursion.
	 * @since   12.2
	 */
	protected function populateState()
	{
		parent::populateState();

		$clientId = \JFactory::getApplication()->input->get('client_id', 0);

		$this->state->set('client_id', $clientId);
	}

	/**
	 * Method to get the reverse lookup of the base link URL to Title
	 *
	 * @return  array  Array of reverse lookup of the base link URL to Title
	 *
	 * @since   1.6
	 */
	public function getReverseLookup()
	{
		if (empty($this->rlu))
		{
			$this->getTypeOptions();
		}

		return $this->rlu;
	}

	/**
	 * Method to get the available menu item type options.
	 *
	 * @return  array  Array of groups with menu item types.
	 *
	 * @since   1.6
	 */
	public function getTypeOptions()
	{
		jimport('joomla.filesystem.file');

		$lang = \JFactory::getLanguage();
		$list = array();

		// Get the list of components.
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('name, element AS ' . $db->quoteName('option'))
			->from('#__extensions')
			->where('type = ' . $db->quote('component'))
			->where('enabled = 1')
			->order('name ASC');
		$db->setQuery($query);
		$components = $db->loadObjectList();

		foreach ($components as $component)
		{
			$options = $this->getTypeOptionsByComponent($component->option);

			if ($options)
			{
				$list[$component->name] = $options;

				// Create the reverse lookup for link-to-name.
				foreach ($options as $option)
				{
					if (isset($option->request))
					{
						$this->addReverseLookupUrl($option);

						if (isset($option->request['option']))
						{
							$componentLanguageFolder = JPATH_ADMINISTRATOR . '/components/' . $option->request['option'];
							$lang->load($option->request['option'] . '.sys', JPATH_ADMINISTRATOR, null, false, true)
								|| $lang->load($option->request['option'] . '.sys', $componentLanguageFolder, null, false, true);
						}
					}
				}
			}
		}

		// Allow a system plugin to insert dynamic menu types to the list shown in menus:
		\JFactory::getApplication()->triggerEvent('onAfterGetMenuTypeOptions', array(&$list, $this));

		return $list;
	}

	/**
	 * Method to create the reverse lookup for link-to-name.
	 * (can be used from onAfterGetMenuTypeOptions handlers)
	 *
	 * @param   \JObject  $option  with request array or string and title public variables
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function addReverseLookupUrl($option)
	{
		$this->rlu[MenusHelper::getLinkKey($option->request)] = $option->get('title');
	}

	/**
	 * Get menu types by component.
	 *
	 * @param   string  $component  Component URL option.
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	protected function getTypeOptionsByComponent($component)
	{
		$options = array();
		$client  = ApplicationHelper::getClientInfo($this->getState('client_id'));
		$mainXML = $client->path . '/components/' . $component . '/metadata.xml';

		if (is_file($mainXML))
		{
			$options = $this->getTypeOptionsFromXml($mainXML, $component);
		}

		if (empty($options))
		{
			$options = $this->getTypeOptionsFromMvc($component);
		}

		if ($client->id == 1 && empty($options))
		{
			$options = $this->getTypeOptionsFromManifest($component);
		}

		return $options;
	}

	/**
	 * Get the menu types from an XML file
	 *
	 * @param   string  $file       File path
	 * @param   string  $component  Component option as in URL
	 *
	 * @return  array|bool
	 *
	 * @since   1.6
	 */
	protected function getTypeOptionsFromXml($file, $component)
	{
		$options = array();

		// Attempt to load the xml file.
		if (!$xml = simplexml_load_file($file))
		{
			return false;
		}

		// Look for the first menu node off of the root node.
		if (!$menu = $xml->xpath('menu[1]'))
		{
			return false;
		}
		else
		{
			$menu = $menu[0];
		}

		// If we have no options to parse, just add the base component to the list of options.
		if (!empty($menu['options']) && $menu['options'] == 'none')
		{
			// Create the menu option for the component.
			$o = new \JObject;
			$o->title       = (string) $menu['name'];
			$o->description = (string) $menu['msg'];
			$o->request     = array('option' => $component);

			$options[] = $o;

			return $options;
		}

		// Look for the first options node off of the menu node.
		if (!$optionsNode = $menu->xpath('options[1]'))
		{
			return false;
		}
		else
		{
			$optionsNode = $optionsNode[0];
		}

		// Make sure the options node has children.
		if (!$children = $optionsNode->children())
		{
			return false;
		}

		// Process each child as an option.
		foreach ($children as $child)
		{
			if ($child->getName() == 'option')
			{
				// Create the menu option for the component.
				$o = new \JObject;
				$o->title       = (string) $child['name'];
				$o->description = (string) $child['msg'];
				$o->request     = array('option' => $component, (string) $optionsNode['var'] => (string) $child['value']);

				$options[] = $o;
			}
			elseif ($child->getName() == 'default')
			{
				// Create the menu option for the component.
				$o = new \JObject;
				$o->title       = (string) $child['name'];
				$o->description = (string) $child['msg'];
				$o->request     = array('option' => $component);

				$options[] = $o;
			}
		}

		return $options;
	}

	/**
	 * Get menu types from MVC
	 *
	 * @param   string  $component  Component option like in URLs
	 *
	 * @return  array|bool
	 *
	 * @since   1.6
	 */
	protected function getTypeOptionsFromMvc($component)
	{
		$options = array();
		$views   = array();

		foreach ($this->getFolders($component) as $path)
		{
			if (!is_dir($path))
			{
				continue;
			}

			$views = array_merge($views, \JFolder::folders($path, '.', false, true));
		}

		foreach ($views as $viewPath)
		{
			$view = basename($viewPath);

			// Ignore private views.
			if (strpos($view, '_') !== 0)
			{
				// Determine if a metadata file exists for the view.
				$file = $viewPath . '/metadata.xml';

				if (is_file($file))
				{
					// Attempt to load the xml file.
					if ($xml = simplexml_load_file($file))
					{
						// Look for the first view node off of the root node.
						if ($menu = $xml->xpath('view[1]'))
						{
							$menu = $menu[0];

							// If the view is hidden from the menu, discard it and move on to the next view.
							if (!empty($menu['hidden']) && $menu['hidden'] == 'true')
							{
								unset($xml);
								continue;
							}

							// Do we have an options node or should we process layouts?
							// Look for the first options node off of the menu node.
							if ($optionsNode = $menu->xpath('options[1]'))
							{
								$optionsNode = $optionsNode[0];

								// Make sure the options node has children.
								if ($children = $optionsNode->children())
								{
									// Process each child as an option.
									foreach ($children as $child)
									{
										if ($child->getName() == 'option')
										{
											// Create the menu option for the component.
											$o = new \JObject;
											$o->title       = (string) $child['name'];
											$o->description = (string) $child['msg'];
											$o->request     = array('option' => $component, 'view' => $view, (string) $optionsNode['var'] => (string) $child['value']);

											$options[] = $o;
										}
										elseif ($child->getName() == 'default')
										{
											// Create the menu option for the component.
											$o = new \JObject;
											$o->title       = (string) $child['name'];
											$o->description = (string) $child['msg'];
											$o->request     = array('option' => $component, 'view' => $view);

											$options[] = $o;
										}
									}
								}
							}
							else
							{
								$options = array_merge($options, (array) $this->getTypeOptionsFromLayouts($component, $view));
							}
						}

						unset($xml);
					}
				}
				else
				{
					$options = array_merge($options, (array) $this->getTypeOptionsFromLayouts($component, $view));
				}
			}
		}

		return $options;
	}

	/**
	 * Get menu types from Component manifest
	 *
	 * @param   string  $component  Component option like in URLs
	 *
	 * @return  array|bool
	 *
	 * @since   3.7.0
	 */
	protected function getTypeOptionsFromManifest($component)
	{
		// Load the component manifest
		$fileName = JPATH_ADMINISTRATOR . '/components/' . $component . '/' . str_replace('com_', '', $component) . '.xml';

		if (!is_file($fileName))
		{
			return false;
		}

		if (!($manifest = simplexml_load_file($fileName)))
		{
			return false;
		}

		// Check for a valid XML root tag.
		if ($manifest->getName() != 'extension')
		{
			return false;
		}

		$options = array();

		// Start with the component root menu.
		$rootMenu = $manifest->administration->menu;

		// If the menu item doesn't exist or is hidden do nothing.
		if (!$rootMenu || in_array((string) $rootMenu['hidden'], array('true', 'hidden')))
		{
			return $options;
		}

		// Create the root menu option.
		$ro = new \stdClass;
		$ro->title       = (string) trim($rootMenu);
		$ro->description = '';
		$ro->request     = array('option' => $component);

		// Process submenu options.
		$submenu = $manifest->administration->submenu;

		if (!$submenu)
		{
			return $options;
		}

		foreach ($submenu->menu as $child)
		{
			$attributes = $child->attributes();

			$o = new \stdClass;
			$o->title       = (string) trim($child);
			$o->description = '';

			if ((string) $attributes->link)
			{
				parse_str((string) $attributes->link, $request);
			}
			else
			{
				$request = array();

				$request['option']     = $component;
				$request['act']        = (string) $attributes->act;
				$request['task']       = (string) $attributes->task;
				$request['controller'] = (string) $attributes->controller;
				$request['view']       = (string) $attributes->view;
				$request['layout']     = (string) $attributes->layout;
				$request['sub']        = (string) $attributes->sub;
			}

			$o->request = array_filter($request, 'strlen');
			$options[]  = new \JObject($o);

			// Do not repeat the default view link (index.php?option=com_abc).
			if (count($o->request) == 1)
			{
				$ro = null;
			}
		}

		if ($ro)
		{
			$options[] = new \JObject($ro);
		}

		return $options;
	}

	/**
	 * Get the menu types from component layouts
	 *
	 * @param   string  $component  Component option as in URLs
	 * @param   string  $view       Name of the view
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	protected function getTypeOptionsFromLayouts($component, $view)
	{
		$options     = array();
		$layouts     = array();
		$layoutNames = array();
		$lang        = \JFactory::getLanguage();
		$client      = ApplicationHelper::getClientInfo($this->getState('client_id'));

		// Get the views for this component.
		foreach ($this->getFolders($component) as $folder)
		{
			$path = $folder . '/' . $view . '/tmpl';

			if (!is_dir($path))
			{
				$path = $folder . '/' . $view;
			}

			if (!is_dir($path))
			{
				continue;
			}

			$layouts = array_merge($layouts, \JFolder::files($path, '.xml$', false, true));
		}

		// Build list of standard layout names
		foreach ($layouts as $layout)
		{
			// Ignore private layouts.
			if (strpos(basename($layout), '_') === false)
			{
				// Get the layout name.
				$layoutNames[] = basename($layout, '.xml');
			}
		}

		// Get the template layouts
		// TODO: This should only search one template -- the current template for this item (default of specified)
		$folders = \JFolder::folders($client->path . '/templates', '', false, true);

		// Array to hold association between template file names and templates
		$templateName = array();

		foreach ($folders as $folder)
		{
			if (is_dir($folder . '/html/' . $component . '/' . $view))
			{
				$template = basename($folder);
				$lang->load('tpl_' . $template . '.sys', $client->path, null, false, true)
				|| $lang->load('tpl_' . $template . '.sys', $client->path . '/templates/' . $template, null, false, true);

				$templateLayouts = \JFolder::files($folder . '/html/' . $component . '/' . $view, '.xml$', false, true);

				foreach ($templateLayouts as $layout)
				{
					// Get the layout name.
					$templateLayoutName = basename($layout, '.xml');

					// Add to the list only if it is not a standard layout
					if (array_search($templateLayoutName, $layoutNames) === false)
					{
						$layouts[] = $layout;

						// Set template name array so we can get the right template for the layout
						$templateName[$layout] = basename($folder);
					}
				}
			}
		}

		// Process the found layouts.
		foreach ($layouts as $layout)
		{
			// Ignore private layouts.
			if (strpos(basename($layout), '_') === false)
			{
				$file = $layout;

				// Get the layout name.
				$layout = basename($layout, '.xml');

				// Create the menu option for the layout.
				$o = new \JObject;
				$o->title       = ucfirst($layout);
				$o->description = '';
				$o->request     = array('option' => $component, 'view' => $view);

				// Only add the layout request argument if not the default layout.
				if ($layout != 'default')
				{
					// If the template is set, add in format template:layout so we save the template name
					$o->request['layout'] = isset($templateName[$file]) ? $templateName[$file] . ':' . $layout : $layout;
				}

				// Load layout metadata if it exists.
				if (is_file($file))
				{
					// Attempt to load the xml file.
					if ($xml = simplexml_load_file($file))
					{
						// Look for the first view node off of the root node.
						if ($menu = $xml->xpath('layout[1]'))
						{
							$menu = $menu[0];

							// If the view is hidden from the menu, discard it and move on to the next view.
							if (!empty($menu['hidden']) && $menu['hidden'] == 'true')
							{
								unset($xml);
								unset($o);
								continue;
							}

							// Populate the title and description if they exist.
							if (!empty($menu['title']))
							{
								$o->title = trim((string) $menu['title']);
							}

							if (!empty($menu->message[0]))
							{
								$o->description = trim((string) $menu->message[0]);
							}
						}
					}
				}

				// Add the layout to the options array.
				$options[] = $o;
			}
		}

		return $options;
	}

	/**
	 * Get the folders with template files for the given component.
	 *
	 * @param   string  $component  Component option as in URLs
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	private function getFolders($component)
	{
		$client  = ApplicationHelper::getClientInfo($this->getState('client_id'));

		if (!is_dir($client->path . '/components/' . $component))
		{
			return array();
		}

		$folders = \JFolder::folders($client->path . '/components/' . $component, '^view[s]?$', false, true);
		$folders = array_merge($folders, \JFolder::folders($client->path . '/components/' . $component, '^tmpl?$', false, true));

		if (!$folders)
		{
			return array();
		}

		return $folders;
	}
}
