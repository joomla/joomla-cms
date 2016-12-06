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
			->select(array($db->qn('element', 'value'), $db->qn('name', 'text'), $db->qn('folder')))
			->from($db->qn('#__extensions'))
			->where($db->qn('enabled'))
			->where($db->qn('type') . ' = ' . $db->q('plugin'))
			->order(array($db->qn('folder'), $db->qn('ordering'), $db->qn('name')));

		if (!empty($folder))
		{
			$query->where($db->qn('folder') . ' = ' . $db->q($folder));
		}

		$options = $db->setQuery($query)->loadObjectList();

		$lang = JFactory::getLanguage();

		foreach ($options as &$item)
		{
			$source = JPATH_PLUGINS . '/' . $item->folder . '/' . $item->value;
			$extension = 'plg_' . $item->folder . '_' . $item->value;

			$what = $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, null, false, false)
				|| $lang->load($extension . '.sys', $source, null, false, false)
				|| $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
				|| $lang->load($extension . '.sys', $source, $lang->getDefault(), false, false);

			$item->text = JText::_($item->text);

			if (empty($folder))
			{
				$item->text .= ' (' . $item->folder . ')';
			}
		}

		return $options;
	}
}
