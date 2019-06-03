<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper;

/**
 * Package field.
 *
 * @since  __DEPLOY_VERSION__
 */
class PackageField extends ListField
{
	/**
	 * The form field type.
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
		$options = InstallerHelper::getExtensionPackages();

		return array_merge(parent::getOptions(), $options);
	}
}
