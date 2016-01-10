<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

require_once __DIR__ . '/../../helpers/installer.php';

/**
 * Type Field class for the Joomla Framework.
 *
 * @since  3.5
 */
class JFormFieldType extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var	   string
	 * @since  3.5
	 */
	protected $type = 'Type';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.5
	 */
	public function getOptions()
	{
		$options = InstallerHelper::getExtensionTypes();

		return array_merge(parent::getOptions(), $options);
	}
}
