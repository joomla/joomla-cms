<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('PluginsHelper', JPATH_ADMINISTRATOR . '/components/com_plugins/helpers/plugins.php');

/**
 * Plugin Type Field class for the Joomla Framework.
 *
 * @since  3.5
 */
class JFormFieldPluginType extends JFormAbstractlist
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $type = 'PluginType';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.5
	 */
	public function getOptions()
	{
		$options = PluginsHelper::folderOptions();

		return array_merge(parent::getOptions(), $options);
	}
}
