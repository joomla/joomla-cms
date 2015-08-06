<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::register('FinderHelperLanguage', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/language.php');

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
		$lang = JFactory::getLanguage();

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
			return;
		}

		// Compile the options.
		$options = array();

		foreach ($rows as $row)
		{
			$key = $lang->hasKey(FinderHelperLanguage::branchPlural($row->text))
					? FinderHelperLanguage::branchPlural($row->text) : $row->text;
			$string = JText::sprintf('COM_FINDER_ITEM_X_ONLY', JText::_($key));
			$options[] = JHtml::_('select.option', $row->value, $string);
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
		$lang = JFactory::getLanguage();

		// Load the finder types.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('title AS text, id AS value')
			->from($db->quoteName('#__finder_taxonomy'))
			->where($db->quoteName('parent_id') . ' = 1')
			->order('ordering, title ASC');
		$db->setQuery($query);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return;
		}

		// Compile the options.
		$options = array();
		$options[] = JHtml::_('select.option', '1', JText::_('COM_FINDER_MAPS_BRANCHES'));

		foreach ($rows as $row)
		{
			$key = $lang->hasKey(FinderHelperLanguage::branchPlural($row->text))
					? FinderHelperLanguage::branchPlural($row->text) : $row->text;
			$string = JText::sprintf('COM_FINDER_ITEM_X_ONLY', JText::_($key));
			$options[] = JHtml::_('select.option', $row->value, $string);
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
		$options = array();
		$options[] = JHtml::_('select.option', '1', JText::sprintf('COM_FINDER_ITEM_X_ONLY', JText::_('JPUBLISHED')));
		$options[] = JHtml::_('select.option', '0', JText::sprintf('COM_FINDER_ITEM_X_ONLY', JText::_('JUNPUBLISHED')));

		return $options;
	}
}
