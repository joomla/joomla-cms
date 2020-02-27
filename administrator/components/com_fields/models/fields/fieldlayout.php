<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');

/**
 * Form Field to display a list of the layouts for a field from
 * the extension or template overrides.
 *
 * @since  3.9.0
 */
class JFormFieldFieldlayout extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	protected $type = 'FieldLayout';

	/**
	 * Method to get the field input for a field layout field.
	 *
	 * @return  string   The field input.
	 *
	 * @since   3.9.0
	 */
	protected function getInput()
	{
		$extension = explode('.', $this->form->getValue('context'));
		$extension = $extension[0];

		if ($extension)
		{
			// Get the database object and a new query object.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Build the query.
			$query->select('element, name')
				->from('#__extensions')
				->where('client_id = 0')
				->where('type = ' . $db->quote('template'))
				->where('enabled = 1');

			// Set the query and load the templates.
			$db->setQuery($query);
			$templates = $db->loadObjectList('element');

			// Build the search paths for component layouts.
			$component_path = JPath::clean(JPATH_SITE . '/components/' . $extension . '/layouts/field');

			// Prepare array of component layouts
			$component_layouts = array();

			// Prepare the grouped list
			$groups = array();

			// Add "Use Default"
			$groups[]['items'][] = JHtml::_('select.option', '', JText::_('JOPTION_USE_DEFAULT'));

			// Add the layout options from the component path.
			if (is_dir($component_path) && ($component_layouts = JFolder::files($component_path, '^[^_]*\.php$', false, true)))
			{
				// Create the group for the component
				$groups['_'] = array();
				$groups['_']['id'] = $this->id . '__';
				$groups['_']['text'] = JText::sprintf('JOPTION_FROM_COMPONENT');
				$groups['_']['items'] = array();

				foreach ($component_layouts as $i => $file)
				{
					// Add an option to the component group
					$value = basename($file, '.php');
					$component_layouts[$i] = $value;

					if ($value === 'render')
					{
						continue;
					}

					$groups['_']['items'][] = JHtml::_('select.option', $value, $value);
				}
			}

			// Loop on all templates
			if ($templates)
			{
				foreach ($templates as $template)
				{
					$files = array();
					$template_paths = array(
						JPath::clean(JPATH_SITE . '/templates/' . $template->element . '/html/layouts/' . $extension . '/field'),
						JPath::clean(JPATH_SITE . '/templates/' . $template->element . '/html/layouts/com_fields/field'),
						JPath::clean(JPATH_SITE . '/templates/' . $template->element . '/html/layouts/field'),
					);

					// Add the layout options from the template paths.
					foreach ($template_paths as $template_path)
					{
						if (is_dir($template_path))
						{
							$files = array_merge($files, JFolder::files($template_path, '^[^_]*\.php$', false, true));
						}
					}

					foreach ($files as $i => $file)
					{
						$value = basename($file, '.php');

						// Remove the default "render.php" or layout files that exist in the component folder
						if ($value === 'render' || in_array($value, $component_layouts))
						{
							unset($files[$i]);
						}
					}

					if (count($files))
					{
						// Create the group for the template
						$groups[$template->name] = array();
						$groups[$template->name]['id'] = $this->id . '_' . $template->element;
						$groups[$template->name]['text'] = JText::sprintf('JOPTION_FROM_TEMPLATE', $template->name);
						$groups[$template->name]['items'] = array();

						foreach ($files as $file)
						{
							// Add an option to the template group
							$value = basename($file, '.php');
							$groups[$template->name]['items'][] = JHtml::_('select.option', $value, $value);
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

		return '';
	}
}
