<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class JFormFieldMenuType extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'MenuType';

	/**
	 * A reverse lookup of the base link URL to Title
	 *
	 * @var	array
	 */
	protected $_rlu = array();

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		// Initialize variables.
		$html = array();
		$types = $this->_getTypeList();

		$size	= ($v = $this->_element->attributes('size')) ? ' size="'.$v.'"' : '';
		$class	= ($v = $this->_element->attributes('class')) ? 'class="'.$v.'"' : 'class="text_area"';

		switch ($this->value)
		{
			case 'url':
				$value = JText::_('Menus_Type_External_URL');
				break;

			case 'alias':
				$value = JText::_('Menus_Type_Alias');
				break;

			case 'separator':
				$value = JText::_('Menus_Type_Separator');
				break;

			default:
				$link	= $this->_form->getValue('link');
				// Clean the link back to the option, view and layout
				$value	= JText::_(JArrayHelper::getValue($this->_rlu, MenusHelper::getLinkKey($link)));
				break;
		}
		// Load the javascript and css
		JHtml::_('behavior.framework');
		JHtml::script('modal.js');
		JHtml::stylesheet('modal.css');

		// Attach modal behavior to document
		$document = JFactory::getDocument();
		$document->addScriptDeclaration("
		window.addEvent('domready', function() {
			var div = new Element('div').setStyle('display', 'none').injectBefore(document.id('menu-types'));
			document.id('menu-types').injectInside(div);
			SqueezeBox.initialize();
			SqueezeBox.assign($$('input.modal'), {
				parse: 'rel'
			});
		});");

		$html[] = '<input type="text" readonly="readonly" disabled="disabled" value="'.$value.'"'.$size.$class.'>';
		$html[] = '<input type="button" class="modal" value="'.JText::_('Menus_Change_Linktype').'" rel="{handler:\'clone\', target:\'menu-types\'}">';
		$html[] = '<input type="hidden" name="'.$this->inputName.'" value="'.htmlspecialchars($this->value).'">';

		$html[] = '<div id="menu-types">';
		$html[] = $types;
		$html[] = '</div>';

		return implode("\n", $html);
	}

	protected function _getTypeList()
	{
		// Initialize variables.
		$html = array();
		$types = $this->_getTypeOptions();

		$html[] = '<dl class="menu_types">';



		foreach ($types as $name => $list)
		{
		$html[] = '	<dt>'.$name.'</dt>';
		$html[] = '	<dd>';
		$html[] = '		<ul>';
			foreach ($list as $item)
			{
		$html[] = '			<li>';
		$html[] = '				<a class="choose_type" href="index.php?option=com_menus&amp;task=item.setType&amp;type='.base64_encode(json_encode(array('title'=>$item->title, 'request'=>$item->request))).'" title="'.JText::_($item->description).'">'.JText::_($item->title).'</a>';
		$html[] = '			</li>';
			}
		$html[] = '		</ul>';
		$html[] = '	</dd>';
		}

		$html[] = '	<dt>'.JText::_('Menus_Type_System').'</dt>';
		$html[] = '	<dd>';
		$html[] = '		'.JText::_('Menus_Type_System_Desc');
		$html[] = '		<ul>';
		$html[] = '			<li>';
		$html[] = '				<a class="choose_type" href="index.php?option=com_menus&amp;task=item.setType&amp;type='.base64_encode(json_encode(array('title'=>'url'))).'" title="'.JText::_('Menus_Type_External_URL_Desc').'">'.JText::_('Menus_Type_External_URL').'</a>';
		$html[] = '			</li>';
		$html[] = '			<li>';
		$html[] = '				<a class="choose_type" href="index.php?option=com_menus&amp;task=item.setType&amp;type='.base64_encode(json_encode(array('title'=>'alias'))).'" title="'.JText::_('Menus_Type_Alias_Desc').'">'.JText::_('Menus_Type_Alias').'</a>';
		$html[] = '			</li>';
		$html[] = '			<li>';
		$html[] = '				<a class="choose_type" href="index.php?option=com_menus&amp;task=item.setType&amp;type='.base64_encode(json_encode(array('title'=>'separator'))).'" title="'.JText::_('Menus_Type_Separator_Desc').'">'.JText::_('Menus_Type_Separator').'</a>';
		$html[] = '			</li>';
		$html[] = '		</ul>';
		$html[] = '	</dd>';
		$html[] = '</dl>';
		
		return implode("\n", $html);
	}		
	/**
	 * Method to get the available menu item type options.
	 *
	 * @return	array	Array of groups with menu item types.
	 * @since	1.6
	 */
	protected function _getTypeOptions()
	{
		jimport('joomla.filesystem.file');

		// Initialize variables.
		$lang = &JFactory::getLanguage();
		$list = array();

		// Get the list of components.
		$db = & JFactory::getDBO();
		$db->setQuery(
			'SELECT `name`, `option`' .
			' FROM `#__components`' .
			' WHERE `link` <> ""' .
			' AND `parent` = 0' .
			' ORDER BY `name`'
		);
		$components = $db->loadObjectList();

		foreach ($components as $component)
		{
			if ($options = $this->_getTypeOptionsByComponent($component->option))
			{
				$list[$component->name] = $options;

				// Create the reverse lookup for link-to-name.
				foreach ($options as $option)
				{
					if (isset($option->request))
					{
						$this->_rlu[MenusHelper::getLinkKey($option->request)] = $option->get('title');

						if (isset($option->request['option'])) {
							$lang->load($option->request['option'].'.menu');
						}
					}
				}
			}
		}
		return $list;
	}

	protected function _getTypeOptionsByComponent($component)
	{
		// Initialize variables.
		$options = array();

		$mainXML = JPATH_SITE.'/components/'.$component.'/metadata.xml';
		if (is_file($mainXML)) {
			$options = $this->_getTypeOptionsFromXML($mainXML, $component);
		}

		if (empty($options)) {
			$options = $this->_getTypeOptionsFromMVC($component);
		}

		return $options;
	}

	protected function _getTypeOptionsFromXML($file, $component)
	{
		// Initialize variables.
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
			$o->title		= $menu['name'];
			$o->description	= $menu['msg'];
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
				if ($child->getName() == 'option')
				{
					// Create the menu option for the component.
					$o = new JObject;
					$o->title		= $child['name'];
					$o->description	= $child['msg'];
					$o->request		= array('option' => $component, (string) $optionsNode['var'] => (string) $child['value']);

					$options[] = $o;
				}
				elseif ($child->getName() == 'default')
				{
					// Create the menu option for the component.
					$o = new JObject;
					$o->title		= $child['name'];
					$o->description	= $child['msg'];
					$o->request		= array('option' => $component);

					$options[] = $o;
				}
			}
		}

		return $options;
	}

	protected function _getTypeOptionsFromMVC($component)
	{
		// Initialize variables.
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
			if (strpos($view, '_') !== 0)
			{
				// Determine if a metadata file exists for the view.
				$file = $path.'/'.$view.'/metadata.xml';
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
							if (!empty($menu['hidden']) && $menu['hidden'] == 'true') {
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
											$o = new JObject;
											$o->title		= $child['name'];
											$o->description	= $child['msg'];
											$o->request		= array('option' => $component, 'view' => $view, (string) $optionsNode['var'] => (string) $child['value']);

											$options[] = $o;
										}
										elseif ($child->getName() == 'default')
										{
											// Create the menu option for the component.
											$o = new JObject;
											$o->title		= $child['name'];
											$o->description	= $child['msg'];
											$o->request		= array('option' => $component, 'view' => $view);

											$options[] = $o;
										}
									}
								}
							}
							else {
								$options = array_merge($options, (array) $this->_getTypeOptionsFromLayouts($component, $view));
							}
						}
						unset($xml);
					}

				} else {
					$options = array_merge($options, (array) $this->_getTypeOptionsFromLayouts($component, $view));
				}
			}
		}

		return $options;
	}

	protected function _getTypeOptionsFromLayouts($component, $view)
	{
		// Initialize variables.
		$options = array();

		$layouts = array();
		$folders = JFolder::folders(JPATH_SITE.DS.'templates','',false,true);
		foreach($folders as $folder)
		{
			if (JFolder::exists($folder.DS.'html'.DS.$component.DS.$view)) {
				$layouts = array_merge($layouts, JFolder::files($folder.DS.'html'.DS.$component.DS.$view, '.xml$', false, true));
			}
		}

		// Get the layouts from the view folder.
		$path = JPATH_SITE.'/components/'.$component.'/views/'.$view.'/tmpl';
		if (JFolder::exists($path)) {
			$layouts = array_merge($layouts, JFolder::files($path, '.xml$', false, true));
		}
		else {
			return $options;
		}

		// Process the found layouts.
		foreach ($layouts as $layout)
		{
			// Ignore private layouts.
			if (strpos(JFile::getName($layout), '_') === false)
			{
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
					$o->request['layout'] = $layout;
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