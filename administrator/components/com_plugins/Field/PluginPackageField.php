<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Plugins\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\Component\Plugins\Administrator\Helper\PluginsHelper;

/**
 * Location field.
 *
 * @since  __DEPLOY_VERSION__
 */
class PluginPackageField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var	   string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'PluginPackage';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOptions()
	{
		$options = PluginsHelper::getExtensionPackages();

		return array_merge(parent::getOptions(), $options);
	}
}
