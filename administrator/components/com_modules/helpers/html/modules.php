<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** 
 * JHtml module helper class.
 *
 * @since  1.6
 */
abstract class JHtmlModules
{
	/**
	 * Builds an array of template options
	 *
	 * @param   integer  $clientId  The client id.
	 * @param   string   $state     The state of the template.
	 *
	 * @return  array
	 */
	public static function templates($clientId = 0, $state = '')
	{
		$options   = array();
		$templates = ModulesHelper::getTemplates($clientId, $state);

		foreach ($templates as $template)
		{
			$options[] = JHtml::_('select.option', $template->element, $template->name);
		}

		return $options;
	}

	/**
	 * Builds an array of template type options
	 *
	 * @return  array
	 */
	public static function types()
	{
		$options = array();
		$options[] = JHtml::_('select.option', 'user', 'COM_MODULES_OPTION_POSITION_USER_DEFINED');
		$options[] = JHtml::_('select.option', 'template', 'COM_MODULES_OPTION_POSITION_TEMPLATE_DEFINED');

		return $options;
	}

	/**
	 * Builds an array of template state options
	 *
	 * @return  array
	 */
	public static function templateStates()
	{
		$options = array();
		$options[] = JHtml::_('select.option', '1', 'JENABLED');
		$options[] = JHtml::_('select.option', '0', 'JDISABLED');

		return $options;
	}

	/**
	 * Returns a published state on a grid
	 *
	 * @param   integer  $value     The state value.
	 * @param   integer  $i         The row index
	 * @param   boolean  $enabled   An optional setting for access control on the action.
	 * @param   string   $checkbox  An optional prefix for checkboxes.
	 *
	 * @return  string        The Html code
	 *
	 * @see     JHtmlJGrid::state
	 * @since   1.7.1
	 */
	public static function state($value, $i, $enabled = true, $checkbox = 'cb')
	{
		$states = array(
			1 => array(
				'unpublish',
				'COM_MODULES_EXTENSION_PUBLISHED_ENABLED',
				'COM_MODULES_HTML_UNPUBLISH_ENABLED',
				'COM_MODULES_EXTENSION_PUBLISHED_ENABLED',
				true,
				'publish',
				'publish',
			),
			0 => array(
				'publish',
				'COM_MODULES_EXTENSION_UNPUBLISHED_ENABLED',
				'COM_MODULES_HTML_PUBLISH_ENABLED',
				'COM_MODULES_EXTENSION_UNPUBLISHED_ENABLED',
				true,
				'unpublish',
				'unpublish',
			),
			-1 => array(
				'unpublish',
				'COM_MODULES_EXTENSION_PUBLISHED_DISABLED',
				'COM_MODULES_HTML_UNPUBLISH_DISABLED',
				'COM_MODULES_EXTENSION_PUBLISHED_DISABLED',
				true,
				'warning',
				'warning',
			),
			-2 => array(
				'publish',
				'COM_MODULES_EXTENSION_UNPUBLISHED_DISABLED',
				'COM_MODULES_HTML_PUBLISH_DISABLED',
				'COM_MODULES_EXTENSION_UNPUBLISHED_DISABLED',
				true,
				'unpublish',
				'unpublish',
			),
		);

		return JHtml::_('jgrid.state', $states, $value, $i, 'modules.', $enabled, true, $checkbox);
	}

	/**
	 * Display a batch widget for the module position selector.
	 *
	 * @param   integer  $clientId          The client ID.
	 * @param   integer  $state             The state of the module (enabled, unenabled, trashed).
	 * @param   string   $selectedPosition  The currently selected position for the module.
	 *
	 * @return  string   The necessary positions for the widget.
	 *
	 * @since   2.5
	 */

	public static function positions($clientId, $state = 1, $selectedPosition = '')
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_templates/helpers/templates.php';
		$templates      = array_keys(ModulesHelper::getTemplates($clientId, $state));
		$templateGroups = array();

		// Add an empty value to be able to deselect a module position
		$option = ModulesHelper::createOption();
		$templateGroups[''] = ModulesHelper::createOptionGroup('', array($option));

		// Add positions from templates
		$isTemplatePosition = false;

		foreach ($templates as $template)
		{
			$options = array();

			$positions = TemplatesHelper::getPositions($clientId, $template);

			if (is_array($positions))
			{
				foreach ($positions as $position)
				{
					$text = ModulesHelper::getTranslatedModulePosition($clientId, $template, $position) . ' [' . $position . ']';
					$options[] = ModulesHelper::createOption($position, $text);

					if (!$isTemplatePosition && $selectedPosition === $position)
					{
						$isTemplatePosition = true;
					}
				}

				$options = JArrayHelper::sortObjects($options, 'text');
			}

			$templateGroups[$template] = ModulesHelper::createOptionGroup(ucfirst($template), $options);
		}

		// Add custom position to options
		$customGroupText = JText::_('COM_MODULES_CUSTOM_POSITION');

		$editPositions = true;
		$customPositions = ModulesHelper::getPositions($clientId, $editPositions);
		$templateGroups[$customGroupText] = ModulesHelper::createOptionGroup($customGroupText, $customPositions);

		return $templateGroups;
	}

	/**
	 * Get a select with the batch action options
	 *
	 * @return  void
	 */
	public static function batchOptions()
	{
		// Create the copy/move options.
		$options = array(
			JHtml::_('select.option', 'c', JText::_('JLIB_HTML_BATCH_COPY')),
			JHtml::_('select.option', 'm', JText::_('JLIB_HTML_BATCH_MOVE'))
		);

		echo JHtml::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm');
	}

	/**
	 * Method to get the field options.
	 *
	 * @param   integer  $clientId  The client ID
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   2.5
	 */
	public static function positionList($clientId = 0)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT(position) as value')
			->select('position as text')
			->from($db->quoteName('#__modules'))
			->where($db->quoteName('client_id') . ' = ' . (int) $clientId)
			->order('position');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		// Pop the first item off the array if it's blank
		if (count($options))
		{
			if (strlen($options[0]->text) < 1)
			{
				array_shift($options);
			}
		}

		return $options;
	}
}
