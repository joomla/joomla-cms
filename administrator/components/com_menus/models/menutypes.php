<?php
/**
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Menu Item Types Model for Menus.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @version		1.6
 */
class MenusModelMenutypes extends JModelLegacy
{
	/**
	 * A reverse lookup of the base link URL to Title
	 *
	 * @var	array
	 */
	protected $rlu = array();

	/**
	 * Method to get the reverse lookup of the base link URL to Title
	 *
	 * @return	array	Array of reverse lookup of the base link URL to Title
	 * @since	1.6
	 */
	public function getReverseLookup()
	{
		if (empty($this->rlu)) {
			$this->getTypeOptions();
		}
		return $this->rlu;
	}

	/**
	 * Method to get the available menu item type options.
	 *
	 * @return	array	Array of groups with menu item types.
	 * @since	1.6
	 */
	public function getTypeOptions()
	{
		jimport('joomla.filesystem.file');

		// Initialise variables.
		$lang = JFactory::getLanguage();
		$list = array();

		// Get the list of components.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('name, element AS ' . $db->qn('option'));
		$query->from('#__extensions');
		$query->where('type = ' . $db->q('component'));
		$query->where('enabled = 1');
		$query->order('name ASC');
		$db->setQuery($query);
		$components = $db->loadObjectList();

		foreach ($components as $component)
		{
			if ($options = $this->getTypeOptionsByComponent($component->option)) {
				$list[$component->name] = $options;

				// Create the reverse lookup for link-to-name.
				foreach ($options as $option)
				{
					if (isset($option->request)) {
						$this->rlu[MenusHelper::getLinkKey($option->request)] = $option->get('title');

						if (isset($option->request['option'])) {
								$lang->load($option->request['option'].'.sys', JPATH_ADMINISTRATOR, null, false, false)
							||	$lang->load($option->request['option'].'.sys', JPATH_ADMINISTRATOR.'/components/'.$option->request['option'], null, false, false)
							||	$lang->load($option->request['option'].'.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
							||	$lang->load($option->request['option'].'.sys', JPATH_ADMINISTRATOR.'/components/'.$option->request['option'], $lang->getDefault(), false, false);
						}
					}
				}
			}
		}

		return $list;
	}

	protected function getTypeOptionsByComponent($component)
	{
		// Initialise variables.
		$options = array();

		$mainXML = JPATH_SITE.'/components/'.$component.'/metadata.xml';

		if (is_file($mainXML)) {
			$options = $this->getTypeOptionsFromXML($mainXML, $component);
		}

		if (empty($options)) {
			$options = $this->getTypeOptionsFromMVC($component);
		}

		return $options;
	}

	protected function getTypeOptionsFromXML($file, $component)
	{
		// Initialise variables.
		$options = array();

		// Attempt to load the xml file.
		if (!$xml = simplexml_load_file($file)) {
			return false;
		}

		// Look for the first menu node off of the root node.
		if (!$menu = $xml->xpath('menu[1]')) {
			return false;
		}
		else {
			$menu = $menu[0];
		}

		// If we have no options to parse, just add the base component to the list of options.
		if (!empty($menu['options']) && $menu['options'] == 'none')
		{
			// Create the menu option for the component.
			$o = new JObject;
			$o->title		= (string) $menu['name'];
			$o->description	= (string) $menu['msg'];
			$o->request		= array('option' => $component);

			$options[] = $o;

			return $options;
		}

		// Look for the first options node off of the menu node.
		if (!$optionsNode = $menu->xpath('options[1]')) {
			return false;
		}
		else {
			$optionsNode = $optionsNode[0];
		}

		// Make sure the options node has children.
		if (!$children = $optionsNode->children()) {
			return false;
		}
		else {
			// Process each child as an option.
			foreach ($children as $child)
			{
				if ($child->getName() == 'option') {
					// Create the menu option for the component.
					$o = new JObject;
					$o->title		= (string) $child['name'];
					$o->description	= (string) $child['msg'];
					$o->request		= array('option' => $component, (string) $optionsNode['var'] => (string) $child['value']);

					$options[] = $o;
				}
				elseif ($child->getName() == 'default') {
					// Create the menu option for the component.
					$o = new JObject;
					$o->title		= (string) $child['name'];
					$o->description	= (string) $child['msg'];
					$o->request		= array('option' => $component);

					$options[] = $o;
				}
			}
		}

		return $options;
	}

	protected function getTypeOptionsFromMVC($component)
	{
		// Initialise variables.
		$options = array();

		// Get the views for this component.
		$path = JPATH_SITE.'/components/'.$component.'/views';

		if (JFolder::exists($path)) {
			$views = JFolder::folders($path);
		}
		else {
			return false;
		}

		foreach ($views as $view)
		{
			// Ignore private views.
			if (strpos($view, '_') !== 0) {
				// Determine if a metadata file exists for the view.
				$file = $path.'/'.$view.'/metadata.xml';

				if (is_file($file)) {
					// Attempt to load the xml file.
					if ($xml = simplexml_load_file($file)) {
						// Look for the first view node off of the root node.
						if ($menu = $xml->xpath('view[1]')) {
							$menu = $menu[0];

							// If the view is hidden from the menu, discard it and move on to the next view.
							if (!empty($menu['hidden']) && $menu['hidden'] == 'true') {
								unset($xml);
								continue;
							}

							// Do we have an options node or should we process layouts?
							// Look for the first options node off of the menu node.
							if ($optionsNode = $menu->xpath('options[1]')) {
								$optionsNode = $optionsNode[0];

								// Make sure the options node has children.
								if ($children = $optionsNode->children()) {
									// Process each child as an option.
									foreach ($children as $child)
									{
										if ($child->getName() == 'option') {
											// Create the menu option for the component.
											$o = new JObject;
											$o->title		= (string) $child['name'];
											$o->description	= (string) $child['msg'];
											$o->request		= array('option' => $component, 'view' => $view, (string) $optionsNode['var'] => (string) $child['value']);

											$options[] = $o;
										}
										elseif ($child->getName() == 'default') {
											// Create the menu option for the component.
											$o = new JObject;
											$o->title		= (string) $child['name'];
											$o->description	= (string) $child['msg'];
											$o->request		= array('option' => $component, 'view' => $view);

											$options[] = $o;
										}
									}
								}
							}
							else {
								$options = array_merge($options, (array) $this->getTypeOptionsFromLayouts($component, $view));
							}
						}
						unset($xml);
					}

				}
				else {
					$options = array_merge($options, (array) $this->getTypeOptionsFromLayouts($component, $view));
				}
			}
		}

		return $options;
	}

	protected function getTypeOptionsFromLayouts($component, $view)
	{
		// Initialise variables.
		$options = array();
		$layouts = array();
		$layoutNames = array();
		$templateLayouts = array();
		$lang = JFactory::getLanguage();

		// Get the layouts from the view folder.
		$path = JPATH_SITE.'/components/'.$component.'/views/'.$view.'/tmpl';
		if (JFolder::exists($path)) {
			$layouts = array_merge($layouts, JFolder::files($path, '.xml$', false, true));
		}
		else {
			return $options;
		}

		// build list of standard layout names
		foreach ($layouts as $layout)
		{
			// Ignore private layouts.
			if (strpos(JFile::getName($layout), '_') === false) {
				$file = $layout;
				// Get the layout name.
				$layoutNames[] = JFile::stripext(JFile::getName($layout));
			}
		}

		// get the template layouts
		// TODO: This should only search one template -- the current template for this item (default of specified)
		$folders = JFolder::folders(JPATH_SITE . '/templates', '', false, true);
		// Array to hold association between template file names and templates
		$templateName = array();
		foreach($folders as $folder)
		{
			if (JFolder::exists($folder . '/html/' . $component . '/' . $view)) {
				$template = JFile::getName($folder);
					$lang->load('tpl_'.$template.'.sys', JPATH_SITE, null, false, false)
				||	$lang->load('tpl_'.$template.'.sys', JPATH_SITE.'/templates/'.$template, null, false, false)
				||	$lang->load('tpl_'.$template.'.sys', JPATH_SITE, $lang->getDefault(), false, false)
				||	$lang->load('tpl_'.$template.'.sys', JPATH_SITE.'/templates/'.$template, $lang->getDefault(), false, false);

				$templateLayouts = JFolder::files($folder . '/html/' . $component . '/' . $view, '.xml$', false, true);

				foreach ($templateLayouts as $layout)
				{
					$file = $layout;
					// Get the layout name.
					$templateLayoutName = JFile::stripext(JFile::getName($layout));

					// add to the list only if it is not a standard layout
					if (array_search($templateLayoutName, $layoutNames) === false) {
						$layouts[] = $layout;
						// Set template name array so we can get the right template for the layout
						$templateName[$layout] = JFile::getName($folder);
					}
				}
			}
		}

		// Process the found layouts.
		foreach ($layouts as $layout)
		{
			// Ignore private layouts.
			if (strpos(JFile::getName($layout), '_') === false) {
				$file = $layout;
				// Get the layout name.
				$layout = JFile::stripext(JFile::getName($layout));

				// Create the menu option for the layout.
				$o = new JObject;
				$o->title		= ucfirst($layout);
				$o->description	= '';
				$o->request		= array('option' => $component, 'view' => $view);

				// Only add the layout request argument if not the default layout.
				if ($layout != 'default') {
					// If the template is set, add in format template:layout so we save the template name
					$o->request['layout'] = (isset($templateName[$file])) ? $templateName[$file] . ':' . $layout : $layout;
				}

				// Load layout metadata if it exists.
				if (is_file($file)) {
					// Attempt to load the xml file.
					if ($xml = simplexml_load_file($file)) {
						// Look for the first view node off of the root node.
						if ($menu = $xml->xpath('layout[1]')) {
							$menu = $menu[0];

							// If the view is hidden from the menu, discard it and move on to the next view.
							if (!empty($menu['hidden']) && $menu['hidden'] == 'true') {
								unset($xml);
								unset($o);
								continue;
							}

							// Populate the title and description if they exist.
							if (!empty($menu['title'])) {
								$o->title = trim((string) $menu['title']);
							}

							if (!empty($menu->message[0])) {
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
}
