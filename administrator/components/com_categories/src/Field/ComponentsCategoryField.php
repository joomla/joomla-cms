<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\Field;

\defined('_JEXEC') or die;

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
		$db      = Factory::getDbo();
		$options = array();

		$query = $db->getQuery(true);
		$query->select('DISTINCT ' . $db->quoteName('extension'))
			->from($db->quoteName('#__categories'))
			->where($db->quoteName('extension') . ' != ' . $db->quote('system'));

		$db->setQuery($query);
		$categoryTypes = $db->loadColumn();

		foreach ($categoryTypes as $categoryType)
		{
			$option        = new \stdClass;
			$option->value = $categoryType;

			// Extract the component name and optional section name
			$parts     = explode('.', $categoryType);
			$component = $parts[0];
			$section   = (\count($parts) > 1) ? $parts[1] : null;

			// Load component language files
			$lang = Factory::getLanguage();
			$lang->load($component, JPATH_BASE)
			|| $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component);

			// If the component section string exists, let's use it
			if ($lang->hasKey($component_section_key = strtoupper($component . ($section ? "_$section" : ''))))
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
