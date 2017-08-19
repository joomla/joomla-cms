<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('InstallerHelper', JPATH_ADMINISTRATOR . '/components/com_installer/helpers/installer.php');

JFormHelper::loadFieldClass('list');

/**
 * Package Type field.
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldPackage extends JFormFieldList
{
	/**
	 * The form field package.
	 *
	 * @var	   string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Package';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOptions()
	{
		$options = InstallerHelper::getPackages();

		return array_merge(parent::getOptions(), $options);
	}
}
