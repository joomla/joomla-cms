<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('TemplatesHelper', JPATH_ADMINISTRATOR . '/components/com_templates/helpers/templates.php');

JFormHelper::loadFieldClass('list');

/**
 * Template Style Field class for the Joomla Framework.
 *
 * @since  3.5
 */
class JFormFieldTemplateName extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var	   string
	 * @since  3.5
	 */
	protected $type = 'TemplateName';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	public function getOptions()
	{
		// Get the client_id filter from the user state.
		$clientId = JFactory::getApplication()->getUserStateFromRequest('com_templates.styles.client_id', 'client_id', '0', 'string');

		// Get the templates for the selected client_id.
		$options = TemplatesHelper::getTemplateOptions($clientId);

		// Merge into the parent options.
		return array_merge(parent::getOptions(), $options);
	}
}
