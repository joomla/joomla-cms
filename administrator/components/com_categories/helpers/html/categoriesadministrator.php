<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('CategoriesHelper', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/categories.php');

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 */
abstract class JHtmlCategoriesAdministrator
{
	/**
	 * @param   int $catid	The category item id
	 */
	public static function association($catid, $extension = 'com_content')
	{
		// Get the associations
		$associations = CategoriesHelper::getAssociations($catid, $extension);

		JArrayHelper::toInteger($associations);

		// Get the associated categories
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('c.*')
			->from('#__categories as c')
			->where('c.id IN ('.implode(',', array_values($associations)).')')
			->join('LEFT', '#__languages as l ON c.language=l.lang_code')
			->select('l.image')
			->select('l.title as language_title');
		$db->setQuery($query);
		$items = $db->loadObjectList('id');

		// Check for a database error.
		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);
			return false;
		}

		// Construct html
		$text = array();
		foreach ($associations as $tag => $associated)
		{
			if ($associated != $catid)
			{
				$text[] = JText::sprintf('COM_CATEGORIES_TIP_ASSOCIATED_LANGUAGE', JHtml::_('image', 'mod_languages/'.$items[$associated]->image.'.gif', $items[$associated]->language_title, array('title' => $items[$associated]->language_title), true), $items[$associated]->title);
			}
		}
		return JHtml::_('tooltip', implode('<br />', $text), JText::_('COM_CATEGORIES_TIP_ASSOCIATION'), 'admin/icon-16-links.png');
	}

}
