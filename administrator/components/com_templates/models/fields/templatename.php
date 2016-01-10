<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

require_once __DIR__ . '/../../helpers/templates.php';

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
		$app = JFactory::getApplication();
		$clientId = $app->getUserStateFromRequest('com_templates.styles.filter.client_id', 'filter_client_id', null);
		$options = TemplatesHelper::getTemplateOptions($clientId);

		return array_merge(parent::getOptions(), $options);
	}
}
