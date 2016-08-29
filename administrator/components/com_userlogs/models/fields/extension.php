<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userlogs
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');
JLoader::register('UserlogsHelper', JPATH_COMPONENT . '/helpers/userlogs.php');

/**
 * Field to load a list of all users that have logged actions
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldExtension extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'extension';

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOptions()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
				->select('DISTINCT b.extension')
				->from($db->quoteName('#__user_logs', 'b'));

		$db->setQuery($query);
		$extensions = $db->loadObjectList();

		$options = array();

		foreach ($extensions as $extension)
		{
			$text = UserlogsHelper::translateExtensionName(strtoupper(strtok($extension->extension, '.')));
			$options[] = JHtml::_('select.option', $extension->extension, $text);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
