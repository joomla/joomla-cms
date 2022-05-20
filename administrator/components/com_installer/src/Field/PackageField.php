<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper;

/**
 * Package field.
 *
 * Selects the extension ID of an extension of the "package" type.
 *
 * @since __DEPLOY_VERSION__
 */
class PackageField extends ListField
{
	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$options = InstallerHelper::getPackageOptions();

		return array_merge($options, parent::getOptions());
	}

}
