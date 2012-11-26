<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('CategoriesHelper', JPATH_ADMINISTRATOR . '/components/com_content/helpers/categories.php');

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 */
abstract class JHtmlCategoriesAdministrator
{
	/**
	 * @param	int $articleid	The article item id
	 */
	public static function association($catid)
	{
		// Get the associations
		$associations = CategoriesHelper::getAssociations($catid);

		JArrayHelper::toInteger($associations);

		// Get the associated menu items
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('c.*');
		$query->from('#__categories as c');
		$query->where('c.id IN ('.implode(',', array_values($associations)).')');
		$query->leftJoin('#__languages as l ON c.language=l.lang_code');
		$query->select('l.image');
		$query->select('l.title as language_title');
		$db->setQuery($query);
		$items = $db->loadObjectList('id');

		// Check for a database error.
		if ($error = $db->getErrorMsg()) {
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
