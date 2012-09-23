<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.6
 */
abstract class JHtmlModules
{
	/**
	 * @param	int $clientId	The client id
	 * @param	string $state 	The state of the template
	 */
	static public function templates($clientId = 0, $state = '')
	{
		$templates = ModulesHelper::getTemplates($clientId, $state);
		foreach ($templates as $template) {
			$options[]	= JHtml::_('select.option', $template->element, $template->name);
		}
		return $options;
	}
	/**
	 */
	static public function types()
	{
		$options = array();
		$options[] = JHtml::_('select.option', 'user', 'COM_MODULES_OPTION_POSITION_USER_DEFINED');
		$options[] = JHtml::_('select.option', 'template', 'COM_MODULES_OPTION_POSITION_TEMPLATE_DEFINED');
		return $options;
	}

	/**
	 */
	static public function templateStates()
	{
		$options = array();
		$options[] = JHtml::_('select.option', '1', 'JENABLED');
		$options[] = JHtml::_('select.option', '0', 'JDISABLED');
		return $options;
	}

	/**
	 * Returns a published state on a grid
	 *
	 * @param   integer       $value			The state value.
	 * @param   integer       $i				The row index
	 * @param   boolean       $enabled			An optional setting for access control on the action.
	 * @param   string        $checkbox			An optional prefix for checkboxes.
	 *
	 * @return  string        The Html code
	 *
	 * @see JHtmlJGrid::state
	 *
	 * @since   1.7.1
	 */
	public static function state($value, $i, $enabled = true, $checkbox = 'cb')
	{
		$states	= array(
			1	=> array(
				'unpublish',
				'COM_MODULES_EXTENSION_PUBLISHED_ENABLED',
				'COM_MODULES_HTML_UNPUBLISH_ENABLED',
				'COM_MODULES_EXTENSION_PUBLISHED_ENABLED',
				true,
				'publish',
				'publish'
			),
			0	=> array(
				'publish',
				'COM_MODULES_EXTENSION_UNPUBLISHED_ENABLED',
				'COM_MODULES_HTML_PUBLISH_ENABLED',
				'COM_MODULES_EXTENSION_UNPUBLISHED_ENABLED',
				true,
				'unpublish',
				'unpublish'
			),
			-1	=> array(
				'unpublish',
				'COM_MODULES_EXTENSION_PUBLISHED_DISABLED',
				'COM_MODULES_HTML_UNPUBLISH_DISABLED',
				'COM_MODULES_EXTENSION_PUBLISHED_DISABLED',
				true,
				'warning',
				'warning'
			),
			-2	=> array(
				'publish',
				'COM_MODULES_EXTENSION_UNPUBLISHED_DISABLED',
				'COM_MODULES_HTML_PUBLISH_DISABLED',
				'COM_MODULES_EXTENSION_UNPUBLISHED_DISABLED',
				true,
				'unpublish',
				'unpublish'
			),
		);

		return JHtml::_('jgrid.state', $states, $value, $i, 'modules.', $enabled, true, $checkbox);
	}

	/**
	 * Display a batch widget for the module position selector.
	 *
	 * @param   integer  $clientId  The client ID
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since   2.5
	 */
	public static function positions($clientId)
	{
		// Create the copy/move options.
		$options = array(
			JHtml::_('select.option', 'c', JText::_('JLIB_HTML_BATCH_COPY')),
			JHtml::_('select.option', 'm', JText::_('JLIB_HTML_BATCH_MOVE'))
		);

		// Create the batch selector to change select the category by which to move or copy.
		$lines = array(
			'<label id="batch-choose-action-lbl" for="batch-choose-action">',
			JText::_('COM_MODULES_BATCH_POSITION_LABEL'),
			'</label>',
			'<div id="batch-choose-action" class="control-group">',
			'<select name="batch[position_id]" class="inputbox" id="batch-position-id">',
			'<option value="">' . JText::_('JSELECT') . '</option>',
			'<option value="nochange">' . JText::_('COM_MODULES_BATCH_POSITION_NOCHANGE') . '</option>',
			'<option value="noposition">' . JText::_('COM_MODULES_BATCH_POSITION_NOPOSITION') . '</option>',
			JHtml::_('select.options',	self::positionList($clientId)),
			'</select>',
			'</div>', '<div id="batch-move-copy" class="control-group radio">',
			JHtml::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'),
			'</div>'
		);

		return implode("\n", $lines);
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
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('DISTINCT(position) as value');
		$query->select('position as text');
		$query->from($db->quoteName('#__modules'));
		$query->where($db->quoteName('client_id') . ' = ' . (int) $clientId);
		$query->order('position');

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
		if (strlen($options[0]->text) < 1)
		{
			array_shift($options);
		}

		return $options;
	}
}
