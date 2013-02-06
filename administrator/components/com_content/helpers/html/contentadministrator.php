<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContentHelper', JPATH_ADMINISTRATOR . '/components/com_content/helpers/content.php');

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 */
abstract class JHtmlContentAdministrator
{
	/**
	 * @param   int $articleid	The article item id
	 */
	public static function association($articleid)
	{
		// Get the associations
		$associations = ContentHelper::getAssociations($articleid);

		foreach ($associations as $tag => $associated)
		{
			$associations[$tag] = (int) $associated->id;
		}

		// Get the associated menu items
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('c.*');
		$query->from('#__content as c');
		$query->select('cat.title as category_title');
		$query->leftJoin('#__categories as cat ON cat.id=c.catid');
		$query->where('c.id IN ('.implode(',', array_values($associations)).')');
		$query->leftJoin('#__languages as l ON c.language=l.lang_code');
		$query->select('l.image');
		$query->select('l.title as language_title');
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
			if ($associated != $articleid)
			{
				$text[] = JText::sprintf('COM_CONTENT_TIP_ASSOCIATED_LANGUAGE', JHtml::_('image', 'mod_languages/'.$items[$associated]->image.'.gif', $items[$associated]->language_title, array('title' => $items[$associated]->language_title), true), $items[$associated]->title, $items[$associated]->category_title);
			}
		}
		return JHtml::_('tooltip', implode('<br />', $text), JText::_('COM_CONTENT_TIP_ASSOCIATION'), 'admin/icon-16-links.png');
	}

	/**
	 * @param   int $value	The state value
	 * @param   int $i
	 */
	public static function featured($value = 0, $i, $canChange = true)
	{
		JHtml::_('bootstrap.tooltip');

		// Array of image, task, title, action
		$states	= array(
			0	=> array('star-empty',	'articles.featured',	'COM_CONTENT_UNFEATURED',	'COM_CONTENT_TOGGLE_TO_FEATURE'),
			1	=> array('star',		'articles.unfeatured',	'COM_CONTENT_FEATURED',		'COM_CONTENT_TOGGLE_TO_UNFEATURE'),
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon	= $state[0];
		if ($canChange)
		{
			$html	= '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" class="btn btn-micro hasTooltip' . ($value == 1 ? ' active' : '') . '" title="'.JText::_($state[3]).'"><i class="icon-'
					. $icon.'"></i></a>';
		}

		return $html;
	}
}
