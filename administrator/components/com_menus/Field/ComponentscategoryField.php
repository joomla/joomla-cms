<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\Utilities\ArrayHelper;

FormHelper::loadFieldClass('list');

/**
 * Components Category field.
 *
 * @since  1.6
 */
class ComponentsCategoryField extends \JFormFieldList
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
			->select('DISTINCT a.name AS text, a.element AS value')
			->from('#__extensions as a')
			->where('a.enabled >= 1')
			->where('a.type =' . $db->quote('component'))
			->join('INNER', '#__categories as b ON a.element=b.extension');

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
				$item->text = \JText::_($item->text);
			}

			// Sort by component name
			$items = ArrayHelper::sortObjects($items, 'text', 1, true, true);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $items);

		return $options;
	}
}
