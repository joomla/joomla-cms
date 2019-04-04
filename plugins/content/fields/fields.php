<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Fields
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

/**
 * Plug-in to show a custom field in eg an article
 * This uses the {fields ID} syntax
 *
 * @since  3.7.0
 */
class PlgContentFields extends JPlugin
{
	/**
	 * Plugin that shows a custom field
	 *
	 * @param   string  $context  The context of the content being passed to the plugin.
	 * @param   object  &$item    The item object.  Note $article->text is also available
	 * @param   object  &$params  The article params
	 * @param   int     $page     The 'page' number
	 *
	 * @return void
	 *
	 * @since  3.7.0
	 */
	public function onContentPrepare($context, &$item, &$params, $page = 0)
	{
		// If the item has a context, overwrite the existing one
		if ($context == 'com_finder.indexer' && !empty($item->context))
		{
			$context = $item->context;
		}
		elseif ($context == 'com_finder.indexer')
		{
			// Don't run this plugin when the content is being indexed and we have no real context
			return;
		}

		// Don't run if there is no text property (in case of bad calls) or it is empty
		if (empty($item->text))
		{
			return;
		}

		// Simple performance check to determine whether bot should process further
		if (strpos($item->text, 'field') === false)
		{
			return;
		}

		// Register FieldsHelper
		JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

		// Prepare the text
		if (isset($item->text))
		{
			$item->text = $this->prepare($item->text, $context, $item);
		}

		// Prepare the intro text
		if (isset($item->introtext))
		{
			$item->introtext = $this->prepare($item->introtext, $context, $item);
		}
	}

	/**
	 * Prepares the given string by parsing {field} and {fieldgroup} groups and replacing them.
	 *
	 * @param   string  $string   The text to prepare
	 * @param   string  $context  The context of the content
	 * @param   object  $item     The item object
	 *
	 * @return string
	 *
	 * @since  3.8.1
	 */
	private function prepare($string, $context, $item)
	{
		// Search for {field ID} or {fieldgroup ID} tags and put the results into $matches.
		$regex = '/{(field|fieldgroup)\s+(.*?)}/i';
		preg_match_all($regex, $string, $matches, PREG_SET_ORDER);

		if (!$matches)
		{
			return $string;
		}

		$parts = FieldsHelper::extract($context);

		if (count($parts) < 2)
		{
			return $string;
		}

		$context    = $parts[0] . '.' . $parts[1];
		$fields     = FieldsHelper::getFields($context, $item, true);
		$fieldsById = array();
		$groups     = array();

		// Rearranging fields in arrays for easier lookup later.
		foreach ($fields as $field)
		{
			$fieldsById[$field->id]     = $field;
			$groups[$field->group_id][] = $field;
		}

		foreach ($matches as $i => $match)
		{
			// $match[0] is the full pattern match, $match[1] is the type (field or fieldgroup) and $match[2] the ID and optional the layout
			$explode = explode(',', $match[2]);
			$id      = (int) $explode[0];
			$output  = '';

			if ($match[1] == 'field' && $id)
			{
				if (isset($fieldsById[$id]))
				{
					$layout = !empty($explode[1]) ? trim($explode[1]) : $fieldsById[$id]->params->get('layout', 'render');
					$output = FieldsHelper::render(
						$context,
						'field.' . $layout,
						array(
							'item'    => $item,
							'context' => $context,
							'field'   => $fieldsById[$id]
						)
					);
				}
			}
			else
			{
				if ($match[2] === '*')
				{
					$match[0]     = str_replace('*', '\*', $match[0]);
					$renderFields = $fields;
				}
				else
				{
					$renderFields = isset($groups[$id]) ? $groups[$id] : '';
				}

				if ($renderFields)
				{
					$layout = !empty($explode[1]) ? trim($explode[1]) : 'render';
					$output = FieldsHelper::render(
						$context,
						'fields.' . $layout,
						array(
							'item'    => $item,
							'context' => $context,
							'fields'  => $renderFields
						)
					);
				}
			}

			$string = preg_replace("|$match[0]|", addcslashes($output, '\\$'), $string, 1);
		}

		return $string;
	}
}
