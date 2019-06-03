<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005-2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;

/**
 * Package field.
 *
 * @since  __DEPLOY_VERSION__
 */
class ModulesPackageField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var	   string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'ModulesPackage';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOptions()
	{
		$options = ModulesHelper::getExtensionPackages();

		return array_merge(parent::getOptions(), $options);
	}
}
