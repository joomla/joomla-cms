<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Utilities\ArrayHelper;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  3.7.0
 */
class JFormFieldComponents extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   3.7.0
	 */
	protected $type = 'Components';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array  An array of JHtml options.
	 *
	 * @since   11.4
	 */
	protected function getOptions()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('name AS text, element AS value')
			->from('#__extensions')
			->where('enabled >= 1')
			->where('type =' . $db->quote('component'));

		$items = $db->setQuery($query)->loadObjectList();

		if ($items)
		{
			$lang = JFactory::getLanguage();

			foreach ($items as &$item)
			{
				// Load language
				$extension = $item->value;

				$lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true)
					|| $lang->load("$extension.sys", JPATH_ADMINISTRATOR . '/components/' . $extension, null, false, true);

				// Translate component name
				$item->text = JText::_($item->text);
			}

			// Sort by component name
			$items = ArrayHelper::sortObjects($items, 'text', 1, true, true);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $items);

		return $options;
	}
}
