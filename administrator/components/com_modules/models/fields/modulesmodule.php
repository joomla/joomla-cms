<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ModulesHelper', JPATH_ADMINISTRATOR . '/components/com_modules/helpers/modules.php');

JFormHelper::loadFieldClass('list');

/**
 * Modules Module field.
 *
 * @since  3.4.2
 */
class JFormFieldModulesModule extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.4.2
	 */
	protected $type = 'ModulesModule';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.4.2
	 */
	public function getOptions()
	{
		$options = ModulesHelper::getModules(JFactory::getApplication()->getUserState('com_modules.modules.client_id', 0));

		return array_merge(parent::getOptions(), $options);
	}
}
