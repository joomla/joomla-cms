<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('list');
JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');

/**
 * Field to load a list of all extensions that have logged actions
 *
 * @since  3.9.0
 */
class JFormFieldExtension extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	protected $type = 'extension';

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.9.0
	 */
	public function getOptions()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT ' . $db->quoteName('extension'))
			->from($db->quoteName('#__action_logs'))
			->order($db->quoteName('extension'));

		$db->setQuery($query);
		$context = $db->loadColumn();

		$options = array();

		if (count($context) > 0)
		{
			foreach ($context as $item)
			{
				$extensions[] = strtok($item, '.');
			}

			$extensions = array_unique($extensions);

			foreach ($extensions as $extension)
			{
				ActionlogsHelper::loadTranslationFiles($extension);
				$options[] = HTMLHelper::_('select.option', $extension, Text::_($extension));
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
