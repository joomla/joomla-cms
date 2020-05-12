<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * MenuItem by Component field.
 *
 * @since  4.0.0
 */
class MenuItemByComponentField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   4.0.0
	 */
	protected $type = 'MenuItemByComponent';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return    array  An array of JHtml options.
	 *
	 * @since   4.0.0
	 */
	protected function getOptions()
	{
		// Initialise variable.
		$db      = Factory::getDbo();
		$options = array();

		$query = $db->getQuery(true);
		$query->select('DISTINCT ' . $db->quoteName('extensions.element'))
			->from($db->quoteName('#__menu', 'menu'))
			->join(
				'LEFT', $db->quoteName('#__extensions', 'extensions'),
				$db->quoteName('extensions.extension_id') . ' = ' . $db->quoteName('menu.component_id')
			)
			->where($db->quoteName('menu.client_id') . ' = 0')
			->where($db->quoteName('menu.type') . ' = ' . $db->quote('component'));

		$db->setQuery($query);
		$components = $db->loadColumn();

		foreach ($components as $component)
		{
			$option        = new \stdClass;
			$option->value = $component;

			// Load component language files
			$lang = Factory::getLanguage();
			$lang->load($component, JPATH_BASE)
			|| $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component);

			// If the component section string exists, let's use it
			if ($lang->hasKey($component_section_key = strtoupper($component)))
			{
				$option->text = Text::_($component_section_key);
			}
			else
				// Else use the component title
			{
				$option->text = Text::_(strtoupper($component));
			}

			$options[] = $option;
		}

		// Sort by name
		$options = ArrayHelper::sortObjects($options, 'text', 1, true, true);

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
