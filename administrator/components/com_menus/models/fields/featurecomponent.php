<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Utilities\ArrayHelper;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldFeatureComponent extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   __DEPLOY_VERSION__
	 */
	protected $type = 'FeatureComponent';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array  An array of JHtml options.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$featureName = (string) $this->element['feature'];

		if (!$featureName)
		{
			return parent::getOptions();
		}

		$items = array();

		// Initialise variable.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT a.name AS text, a.element AS value')
			->from('#__extensions as a')
			->where('a.enabled >= 1')
			->where('a.type =' . $db->quote('component'));

		// B/C and fallback measure
		if ($featureName == 'com_categories2')
		{
			$conditions = 'b.extension = a.element OR b.extension LIKE ' . $query->concatenate(array('a.element', $db->q('.%', false)));
			$query->select('COUNT(b.id) AS count')
				->join('left', '#__categories as b ON ' . $conditions)
				->group('a.extension_id');
		}
		elseif ($featureName == 'com_fields')
		{
			$conditions = 'b.context = a.element OR b.context LIKE ' . $query->concatenate(array('a.element', $db->q('.%', false)));
			$query->select('COUNT(b.id) AS count')
				->join('left', '#__fields as b ON ' . $conditions)
				->group('a.extension_id');
		}

		$components = $db->setQuery($query)->loadObjectList();

		if (count($components))
		{
			$lang = JFactory::getLanguage();

			foreach ($components as $component)
			{
				// Load and check manifest
				$manifest = JPATH_ADMINISTRATOR .'/components/' . $component->value . '/' . str_replace('com_', '', $component->value) . '.xml';

				$found = false;

				if (is_file($manifest))
				{
					$xml = simplexml_load_file($manifest);

					if ($xml instanceof SimpleXMLElement)
					{
						$features = $xml->xpath('/extension/features/feature[@type="' . $featureName . '"]');

						if (count($features))
						{
							// Load language
							$extension = $component->value;
							$lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true)
								|| $lang->load("$extension.sys", JPATH_ADMINISTRATOR . '/components/' . $extension, null, false, true);

							foreach ($features as $feature)
							{
								$item = new stdClass;

								$item->value = (string) $feature['name'] ?: $component->value;
								$item->text  = (string) $feature['label'] ?: $component->text;

								// Translate component feature name
								$item->text = JText::_($item->text);

								$items[] = $item;

								$found = true;
							}
						}
					}
				}

				// B/C, If this component has an existing matching feature, add this too
				if (!$found && !empty($component->count))
				{
					// Load language
					$extension = $component->value;
					$lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true)
						|| $lang->load("$extension.sys", JPATH_ADMINISTRATOR . '/components/' . $extension, null, false, true);

					// Translate component name
					$component->text = JText::_($component->text);

					$items[] = $component;
				}
			}

			// Sort by component name
			$items = ArrayHelper::sortObjects($items, 'text', 1, true, true);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $items);

		return $options;
	}
}
