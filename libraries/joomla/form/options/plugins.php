<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Plugins Option class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
abstract class JFormOptionPlugins
{
	protected $type = 'Plugins';

	/**
	 * Method to get a list of options.
	 *
	 * @param   SimpleXMLElement  $option     <option/> element
	 * @param   string            $fieldname  The name of the field containing this option.
	 *
	 * @return  array  A list of objects representing HTML option elements (such as created by JHtmlSelect::option).
	 *
	 * @since   11.1
	 */
	public static function getOptions(SimpleXMLElement $option, $fieldname = '')
	{
		$folder	= (string) $option['folder'];

		// Get list of plugins
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('element AS value, name AS text, folder')
			->from('#__extensions')
			->where('enabled = 1')
			->order('folder, ordering, name');

		if (!empty($folder))
		{
			$query->where('folder = ' . $db->q($folder));
		}

		$options = $db->setQuery($query)->loadObjectList();

		$lang = JFactory::getLanguage();

		foreach ($options as $i => $item)
		{
			$source = JPATH_PLUGINS . '/' . $folder . '/' . $item->value;
			$extension = 'plg_' . $folder . '_' . $item->value;

			$lang->load($extension . '.sys', JPATH_ADMINISTRATOR, null, false, false)
				|| $lang->load($extension . '.sys', $source, null, false, false)
				|| $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
				|| $lang->load($extension . '.sys', $source, $lang->getDefault(), false, false);

			$options[$i]->text = JText::_($item->text);

			if (empty($folder))
			{
				$options[$i]->text .= ' (' . $item->folder . ')';
			}
		}

		return $options;
	}
}
