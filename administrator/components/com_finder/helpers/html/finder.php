<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('FinderHelperLanguage', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/language.php');

use Joomla\Utilities\ArrayHelper;

/**
 * HTML behavior class for Finder.
 *
 * @since  2.5
 */
abstract class JHtmlFinder
{
	/**
	 * Creates a list of types to filter on.
	 *
	 * @return  array  An array containing the types that can be selected.
	 *
	 * @since   2.5
	 */
	public static function typeslist()
	{
		// Load the finder types.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT t.title AS text, t.id AS value')
			->from($db->quoteName('#__finder_types') . ' AS t')
			->join('LEFT', $db->quoteName('#__finder_links') . ' AS l ON l.type_id = t.id')
			->order('t.title ASC');
		$db->setQuery($query);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return array();
		}

		// Compile the options.
		$options = array();

		$lang = JFactory::getLanguage();

		foreach ($rows as $row)
		{
			$key       = $lang->hasKey(FinderHelperLanguage::branchPlural($row->text)) ? FinderHelperLanguage::branchPlural($row->text) : $row->text;
			$options[] = JHtml::_('select.option', $row->value, JText::sprintf('COM_FINDER_ITEM_X_ONLY', JText::_($key)));
		}

		return $options;
	}

	/**
	 * Creates a list of maps.
	 *
	 * @return  array  An array containing the maps that can be selected.
	 *
	 * @since   2.5
	 */
	public static function mapslist()
	{
		// Load the finder types.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('title', 'text'))
			->select($db->quoteName('id', 'value'))
			->from($db->quoteName('#__finder_taxonomy'))
			->where($db->quoteName('parent_id') . ' = 1');
		$db->setQuery($query);

		try
		{
			$branches = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $db->getMessage());
		}

		// Translate.
		$lang = JFactory::getLanguage();

		foreach ($branches as $branch)
		{
			$key = FinderHelperLanguage::branchPlural($branch->text);
			$branch->translatedText = $lang->hasKey($key) ? JText::_($key) : $branch->text;
		}

		// Order by title.
		$branches = ArrayHelper::sortObjects($branches, 'translatedText', 1, true, true);

		// Compile the options.
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('COM_FINDER_MAPS_SELECT_BRANCH'));

		// Convert the values to options.
		foreach ($branches as $branch)
		{
			$options[] = JHtml::_('select.option', $branch->value, $branch->translatedText);
		}

		return $options;
	}

	/**
	 * Creates a list of published states.
	 *
	 * @return  array  An array containing the states that can be selected.
	 *
	 * @since   2.5
	 */
	public static function statelist()
	{
		return array(
			JHtml::_('select.option', '1', JText::sprintf('COM_FINDER_ITEM_X_ONLY', JText::_('JPUBLISHED'))),
			JHtml::_('select.option', '0', JText::sprintf('COM_FINDER_ITEM_X_ONLY', JText::_('JUNPUBLISHED')))
		);
	}
}
