<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('groupedlist');

/**
 * Chrome Styles Form Field class for the Joomla Platform.
 *
 * @since  3.0
 */
class JFormFieldChromeStyle extends JFormFieldGroupedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	public $type = 'ChromeStyle';

	/**
	 * Method to get the list of template chrome style options
	 * grouped by template.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @since   3.0
	 */
	protected function getGroups()
	{
		$groups = array();

		// Add Module Style Field
		$tmp = '---' . JText::_('JLIB_FORM_VALUE_FROM_TEMPLATE') . '---';
		$groups[$tmp][] = JHtml::_('select.option', '0', JText::_('JLIB_FORM_VALUE_INHERITED'));

		$templateStyles = $this->getTemplateModuleStyles();

		// Create one new option object for each available style, grouped by templates
		foreach ($templateStyles as $template => $styles)
		{
			$template = ucfirst($template);
			$groups[$template] = array();

			foreach ($styles as $style)
			{
				$tmp = JHtml::_('select.option', $template . '-' . $style, $style);
				$groups[$template][] = $tmp;
			}
		}

		reset($groups);

		return $groups;
	}

	/**
	 * Method to get the templates module styles.
	 *
	 * @return  array  The array of styles, grouped by templates.
	 *
	 * @since   3.0
	 */
	protected function getTemplateModuleStyles()
	{
		$moduleStyles = array();

		$templates = array($this->getSystemTemplate());
		$templates = array_merge($templates, $this->getTemplates());

		foreach ($templates as $template)
		{
			$modulesFilePath = JPATH_SITE . '/templates/' . $template->element . '/html/modules.php';

			// Is there modules.php for that template?
			if (file_exists($modulesFilePath))
			{
				$modulesFileData = file_get_contents($modulesFilePath);

				preg_match_all('/function[\s\t]*modChrome\_([a-z0-9\-\_]*)[\s\t]*\(/i', $modulesFileData, $styles);

				if (!array_key_exists($template->element, $moduleStyles))
				{
					$moduleStyles[$template->element] = array();
				}

				$moduleStyles[$template->element] = $styles[1];
			}
		}

		return $moduleStyles;
	}

	/**
	 * Method to get the system template as an object.
	 *
	 * @return  array  The object of system template.
	 *
	 * @since   3.0
	 */
	protected function getSystemTemplate()
	{
		$template = new stdClass;
		$template->element = 'system';
		$template->name    = 'system';
		$template->enabled = 1;

		return $template;
	}

	/**
	 * Return a list of templates
	 *
	 * @return  array  List of templates
	 *
	 * @since   3.2.1
	 */
	protected function getTemplates()
	{
		$db = JFactory::getDbo();

		// Get the database object and a new query object.
		$query = $db->getQuery(true);

		// Build the query.
		$query->select('element, name, enabled')
			->from('#__extensions')
			->where('client_id = 0')
			->where('type = ' . $db->quote('template'));

		// Set the query and load the templates.
		$db->setQuery($query);

		return $db->loadObjectList('element');
	}
}
