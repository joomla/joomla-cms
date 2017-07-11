<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Plugins\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\Component\Plugins\Administrator\Helper\PluginsHelper;

\JLoader::register('PluginsHelper', JPATH_ADMINISTRATOR . '/components/com_plugins/helpers/plugins.php');

FormHelper::loadFieldClass('list');

/**
 * Plugin Type field.
 *
 * @since  3.5
 */
class PluginTypeField extends \JFormFieldList
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
