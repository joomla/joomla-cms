<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContentHelper', JPATH_ADMINISTRATOR . '/components/com_content/helpers/content.php');
JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
JLoader::register('CategoryHelperAssociation', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/association.php');

/**
 * Content Component Association Helper
 *
 * @since  3.0
 */
abstract class ContentHelperAssociation extends CategoryHelperAssociation
{
	/**
	 * Method to get the associations for a given item
	 *
	 * @param   integer  $id      Id of the item
	 * @param   string   $view    Name of the view
	 * @param   string   $layout  View layout
	 *
	 * @return  array   Array of associations for the item
	 *
	 * @since  3.0
	 */
	public static function getAssociations($id = 0, $view = null, $layout = null)
	{
		$jinput    = JFactory::getApplication()->input;
		$view      = $view === null ? $jinput->get('view') : $view;
		$component = $jinput->getCmd('option');
		$id        = empty($id) ? $jinput->getInt('id') : $id;

		if ($layout === null && $jinput->get('view') == $view && $component == 'com_content')
		{
			$layout = $jinput->get('layout', '', 'string');
		}

		if ($view === 'article')
		{
			if ($id)
			{
				$user      = JFactory::getUser();
				$groups    = implode(',', $user->getAuthorisedViewLevels());
				$db        = JFactory::getDbo();
				$advClause = array();

				// Filter by user groups
				$advClause[] = 'c2.access IN (' . $groups . ')';

				// Filter by current language
				$advClause[] = 'c2.language != ' . $db->quote(JFactory::getLanguage()->getTag());

				if (!$user->authorise('core.edit.state', 'com_content') && !$user->authorise('core.edit', 'com_content'))
				{
					// Filter by start and end dates.
					$nullDate = $db->quote($db->getNullDate());
					$date = JFactory::getDate();

					$nowDate = $db->quote($date->toSql());

					$advClause[] = '(c2.publish_up = ' . $nullDate . ' OR c2.publish_up <= ' . $nowDate . ')';
					$advClause[] = '(c2.publish_down = ' . $nullDate . ' OR c2.publish_down >= ' . $nowDate . ')';

					// Filter by published
					$advClause[] = 'c2.state = 1';
				}

				$associations = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', $id, 'id', 'alias', 'catid', $advClause);

				$return = array();

				foreach ($associations as $tag => $item)
				{
					$return[$tag] = ContentHelperRoute::getArticleRoute($item->id, (int) $item->catid, $item->language, $layout);
				}

				return $return;
			}
		}

		if ($view === 'category' || $view === 'categories')
		{
			return self::getCategoryAssociations($id, 'com_content', $layout);
		}

		return array();
	}

	/**
	 * Method to display in frontend the associations for a given article
	 *
	 * @param   integer  $id  Id of the article
	 *
	 * @return  array  An array containing the association URL and the related language object
	 *
	 * @since  3.7.0
	 */
	public static function displayAssociations($id)
	{
		$return = array();

		if ($associations = self::getAssociations($id, 'article'))
		{
			$levels    = JFactory::getUser()->getAuthorisedViewLevels();
			$languages = JLanguageHelper::getLanguages();

			foreach ($languages as $language)
			{
				// Do not display language when no association
				if (empty($associations[$language->lang_code]))
				{
					continue;
				}

				// Do not display language without frontend UI
				if (!array_key_exists($language->lang_code, JLanguageHelper::getInstalledLanguages(0)))
				{
					continue;
				}

				// Do not display language without specific home menu
				if (!array_key_exists($language->lang_code, JLanguageMultilang::getSiteHomePages()))
				{
					continue;
				}

				// Do not display language without authorized access level
				if (isset($language->access) && $language->access && !in_array($language->access, $levels))
				{
					continue;
				}

				$return[$language->lang_code] = array('item' => $associations[$language->lang_code], 'language' => $language);
			}
		}

		return $return;
	}
}
