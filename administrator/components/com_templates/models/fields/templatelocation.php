<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('TemplatesHelper', JPATH_ADMINISTRATOR . '/components/com_templates/helpers/templates.php');

JFormHelper::loadFieldClass('list');

/**
 * Template Location field.
 *
 * @since  3.5
 */
class JFormFieldTemplateLocation extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var	   string
	 * @since  3.5
	 */
	protected $type = 'TemplateLocation';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.5
	 */
	public function getOptions()
	{
		$options = TemplatesHelper::getClientOptions();

		return array_merge(parent::getOptions(), $options);
	}
}
