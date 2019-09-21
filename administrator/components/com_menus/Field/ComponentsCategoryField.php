<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * Components Category field.
 *
 * @since  1.6
 */
class ComponentsCategoryField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   3.7.0
	 */
	protected $type = 'ComponentsCategory';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array  An array of JHtml options.
	 *
	 * @since   3.7.0
	 */
	protected function getOptions()
	{
		// Initialise variable.
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				[
					'DISTINCT ' . $db->quoteName('a.name', 'text'),
					$db->quoteName('a.element', 'value'),
				]
			)
			->from($db->quoteName('#__extensions', 'a'))
			->join('INNER', $db->quoteName('#__categories', 'b'), $db->quoteName('a.element') . ' = ' . $db->quoteName('b.extension'))
			->where(
				[
					$db->quoteName('a.enabled') . ' >= 1',
					$db->quoteName('a.type') . ' = ' . $db->quote('component'),
				]
			);

		$items = $db->setQuery($query)->loadObjectList();

		if (count($items))
		{
			$lang = Factory::getLanguage();

			foreach ($items as &$item)
			{
				// Load language
				$extension = $item->value;
				$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
				$lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true)
					|| $lang->load("$extension.sys", $source, null, false, true);

				// Translate component name
				$item->text = Text::_($item->text);
			}

			// Sort by component name
			$items = ArrayHelper::sortObjects($items, 'text', 1, true, true);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $items);

		return $options;
	}
}
