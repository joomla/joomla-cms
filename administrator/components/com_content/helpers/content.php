<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content component helper.
 *
 * @since  1.6
 */
class ContentHelper extends JHelperContent
{
	public static $extension = 'com_content';

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('JGLOBAL_ARTICLES'),
			'index.php?option=com_content&view=articles',
			$vName == 'articles'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_CONTENT_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_content',
			$vName == 'categories'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_CONTENT_SUBMENU_FEATURED'),
			'index.php?option=com_content&view=featured',
			$vName == 'featured'
		);

		if (JComponentHelper::isEnabled('com_fields') && JComponentHelper::getParams('com_content')->get('custom_fields_enable', '1'))
		{
			JHtmlSidebar::addEntry(
				JText::_('JGLOBAL_FIELDS'),
				'index.php?option=com_fields&context=com_content.article',
				$vName == 'fields.fields'
			);
			JHtmlSidebar::addEntry(
				JText::_('JGLOBAL_FIELD_GROUPS'),
				'index.php?option=com_fields&view=groups&context=com_content.article',
				$vName == 'fields.groups'
			);
		}
	}

	/**
	 * Applies the content tag filters to arbitrary text as per settings for current user group
	 *
	 * @param   text  $text  The string to filter
	 *
	 * @return  string  The filtered string
	 *
	 * @deprecated  4.0  Use JComponentHelper::filterText() instead.
	 */
	public static function filterText($text)
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use JComponentHelper::filterText() instead', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		return JComponentHelper::filterText($text);
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   stdClass[]  &$items  The category objects
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.5
	 */
	public static function countItems(&$items)
	{
		$config = (object) array(
			'related_tbl'   => 'content',
			'state_col'     => 'state',
			'group_col'     => 'catid',
			'relation_type' => 'category_or_group',
		);

		return parent::countRelations($items, $config);
	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   stdClass[]  &$items     The tag objects
	 * @param   string      $extension  The name of the active view.
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.6
	 */
	public static function countTagItems(&$items, $extension)
	{
		$parts   = explode('.', $extension);
		$section = count($parts) > 1 ? $parts[1] : null;

		$config = (object) array(
			'related_tbl'   => ($section === 'category' ? 'categories' : 'content'),
			'state_col'     => ($section === 'category' ? 'published' : 'state'),
			'group_col'     => 'tag_id',
			'extension'     => $extension,
			'relation_type' => 'tag_assigments',
		);

		return parent::countRelations($items, $config);
	}

	/**
	 * Returns a valid section for articles. If it is not valid then null
	 * is returned.
	 *
	 * @param   string  $section  The section to get the mapping for
	 *
	 * @return  string|null  The new section
	 *
	 * @since   3.7.0
	 */
	public static function validateSection($section)
	{
		if (JFactory::getApplication()->isClient('site'))
		{
			// On the front end we need to map some sections
			switch ($section)
			{
				// Editing an article
				case 'form':

				// Category list view
				case 'featured':
				case 'category':
					$section = 'article';
			}
		}

		if ($section != 'article')
		{
			// We don't know other sections
			return null;
		}

		return $section;
	}

	/**
	 * Returns valid contexts
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public static function getContexts()
	{
		JFactory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_content.article'    => JText::_('COM_CONTENT'),
			'com_content.categories' => JText::_('JCATEGORY')
		);

		return $contexts;
	}
}
