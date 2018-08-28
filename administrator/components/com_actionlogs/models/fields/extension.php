<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');
JLoader::register('ActionlogsHelper', JPATH_COMPONENT . '/helpers/actionlogs.php');

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
			->select('DISTINCT ' . $db->quoteName('extension'))
			->from($db->quoteName('#__action_logs'))
			->order($db->quoteName('extension'));

		$db->setQuery($query);
		$context = $db->loadColumn();

		$options = array();

		foreach ($context as $item)
		{
			$extensions[] = strtok($item, '.');
		}

		$extensions = array_unique($extensions);

		foreach ($extensions as $extension)
		{
			ActionlogsHelper::loadTranslationFiles($extension);
			$options[] = JHtml::_('select.option', $extension, JText::_($extension));
		}

		return array_merge(parent::getOptions(), $options);
	}
}
