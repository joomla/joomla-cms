<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  11.4
 */
class JFormFieldAliastag extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var    string
	 * @since  3.6
	 */
	protected $type = 'Aliastag';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array  An array of JHtml options.
	 *
	 * @since   3.6
	 */
	protected function getOptions()
	{
			// Get list of tag type alias
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('Distinct type_alias AS value, type_alias AS text')
				->from('#__contentitem_tag_map')
				->order('text');
			$db->setQuery($query);

			$options = $db->loadObjectList();

			$lang = JFactory::getLanguage();

			foreach ($options as $i => $item)
			{
				$parts     = explode('.', $item->value);
				$extension = $parts[0];
				$lang->load($extension . '.sys', JPATH_ADMINISTRATOR, null, false, true);
				$options[$i]->text = JText::_(strtoupper($extension) . '_TAGS_' . strtoupper($parts[1]));
			}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
