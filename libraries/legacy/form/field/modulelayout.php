<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');

/**
 * Form Field to display a list of the layouts for module display from the module or template overrides.
 *
 * @since  1.6
 */
class JFormFieldModulelayout extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'ModuleLayout';

	/**
	 * Method to get the field input for module layouts.
	 *
	 * @return  string  The field input.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		// Get the client id.
		$clientId = $this->element['client_id'];

		if ($clientId === null && $this->form instanceof JForm)
		{
			$clientId = $this->form->getValue('client_id');
		}

		$clientId = (int) $clientId;

		$client = JApplicationHelper::getClientInfo($clientId);

		// Get the module.
		$module = (string) $this->element['module'];

		if (empty($module) && ($this->form instanceof JForm))
		{
			$module = $this->form->getValue('module');
		}

		$module = preg_replace('#\W#', '', $module);

		// Get the template.
		$template = (string) $this->element['template'];
		$template = preg_replace('#\W#', '', $template);

		// Get the style.
		$template_style_id = '';
		if ($this->form instanceof JForm)
		{
			$template_style_id = $this->form->getValue('template_style_id');
			$template_style_id = preg_replace('#\W#', '', $template_style_id);
		}

		// If an extension and view are present build the options.
		if ($module && $client)
		{
			// Load language file
			$lang = JFactory::getLanguage();
			$lang->load($module . '.sys', $client->path, null, false, true)
				|| $lang->load($module . '.sys', $client->path . '/modules/' . $module, null, false, true);

			// Get the database object and a new query object.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Build the query.
			$query->select('element, name')
				->from('#__extensions as e')
				->where('e.client_id = ' . (int) $clientId)
				->where('e.type = ' . $db->quote('template'))
				->where('e.enabled = 1');

			if ($template)
			{
				$query->where('e.element = ' . $db->quote($template));
			}

			if ($template_style_id)
			{
				$query->join('LEFT', '#__template_styles as s on s.template=e.element')
					->where('s.id=' . (int) $template_style_id);
			}

			// Set the query and load the templates.
			$db->setQuery($query);
			$templates = $db->loadObjectList('element');

			// Build the search paths for module layouts.
			$module_path = JPath::clean($client->path . '/modules/' . $module . '/tmpl');

			// Prepare array of component layouts
			$module_layouts = array();

			// Prepare the grouped list
			$groups = array();

			// Add the layout options from the module path.
			if (is_dir($module_path) && ($module_layouts = JFolder::files($module_path, '^[^_]*\.php$')))
			{
				// Create the group for the module
				$groups['_'] = array();
				$groups['_']['id'] = $this->id . '__';
				$groups['_']['text'] = JText::sprintf('JOPTION_FROM_MODULE');
				$groups['_']['items'] = array();

				foreach ($module_layouts as $file)
				{
					// Add an option to the module group
					$value = basename($file, '.php');
					$text = $lang->hasKey($key = strtoupper($module . '_LAYOUT_' . $value)) ? JText::_($key) : $value;
					$groups['_']['items'][] = JHtml::_('select.option', '_:' . $value, $text);
				}
			}

			// Loop on all templates
			if ($templates)
			{
				foreach ($templates as $template)
				{
					// Load language file
					$lang->load('tpl_' . $template->element . '.sys', $client->path, null, false, true)
						|| $lang->load('tpl_' . $template->element . '.sys', $client->path . '/templates/' . $template->element, null, false, true);

					$template_path = JPath::clean($client->path . '/templates/' . $template->element . '/html/' . $module);

					// Add the layout options from the template path.
					if (is_dir($template_path) && ($files = JFolder::files($template_path, '^[^_]*\.php$')))
					{
						foreach ($files as $i => $file)
						{
							// Remove layout that already exist in component ones
							if (in_array($file, $module_layouts))
							{
								unset($files[$i]);
							}
						}

						if (count($files))
						{
							// Create the group for the template
							$groups[$template->element] = array();
							$groups[$template->element]['id'] = $this->id . '_' . $template->element;
							$groups[$template->element]['text'] = JText::sprintf('JOPTION_FROM_TEMPLATE', $template->name);
							$groups[$template->element]['items'] = array();

							foreach ($files as $file)
							{
								// Add an option to the template group
								$value = basename($file, '.php');
								$text = $lang->hasKey($key = strtoupper('TPL_' . $template->element . '_' . $module . '_LAYOUT_' . $value))
									? JText::_($key) : $value;
								$groups[$template->element]['items'][] = JHtml::_('select.option', $template->element . ':' . $value, $text);
							}
						}
					}
				}
			}
			// Compute attributes for the grouped list
			$attr = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
			$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

			// Prepare HTML code
			$html = array();

			// Compute the current selected values
			$selected = array($this->value);

			// Add a grouped list
			$html[] = JHtml::_(
				'select.groupedlist', $groups, $this->name,
				array('id' => $this->id, 'group.id' => 'id', 'list.attr' => $attr, 'list.select' => $selected)
			);

			return implode($html);
		}
		else
		{
			return '';
		}
	}
}
