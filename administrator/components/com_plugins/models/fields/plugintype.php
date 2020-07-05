<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('PluginsHelper', JPATH_ADMINISTRATOR . '/components/com_plugins/helpers/plugins.php');

JFormHelper::loadFieldClass('list');

/**
 * Plugin Type field.
 *
 * @since  3.5
 */
class JFormFieldPluginType extends JFormFieldList
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
