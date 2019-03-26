<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  System.userlogs
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('checkboxes');
JLoader::register('UserlogsHelper', JPATH_ADMINISTRATOR . '/components/com_userlogs/helpers/userlogs.php');

/**
 * Field to load a list of all users that have logged actions
 *
 * @since __DEPLOY_VERSION__
 */
class JFormFieldLogType extends JFormFieldCheckboxes
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'LogType';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOptions()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
				->select('a.extension')
				->from($db->quoteName('#__user_logs_extensions', 'a'));

		$db->setQuery($query);

		$extensions = $db->loadObjectList();

		$options  = array();
		$defaults = array();

		foreach ($extensions as $extension)
		{
			$tmp = array(
				'checked' => true,
			);

			$defaults[] = $extension;

			$option = JHtml::_('select.option', $extension->extension, UserlogsHelper::translateExtensionName($extension->extension));
			$options[] = (object) array_merge($tmp, (array) $option);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
