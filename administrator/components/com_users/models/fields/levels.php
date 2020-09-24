<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Access Levels field.
 *
 * @since  3.6.0
 */
class JFormFieldLevels extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   3.6.0
	 */
	protected $type = 'Levels';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects
	 *
	 * @since   3.6.0
	 */
	protected function getOptions()
	{
		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), UsersHelperDebug::getLevelsOptions());
	}
}
