<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('NewsfeedsHelper', JPATH_ADMINISTRATOR . '/components/com_newsfeeds/helpers/newsfeeds.php');

/**
 * Utility class for creating HTML Grids
 *
 * @static
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 * @since       1.5
 */
class JHtmlNewsfeed
{
	/**
	 * @param   int $value	The state value
	 * @param   int $i
	 */
	public static function state($value = 0, $i)
	{
		// Array of image, task, title, action
		$states	= array(
			1	=> array('tick.png',		'newsfeeds.unpublish',	'JPUBLISHED',			'COM_NEWSFEEDS_UNPUBLISH_ITEM'),
			0	=> array('publish_x.png',	'newsfeeds.publish',		'JUNPUBLISHED',		'COM_NEWSFEEDS_PUBLISH_ITEM')
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[0]);
		$html	= '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.JText::_($state[3]).'">'
				. JHtml::_('image', 'admin/'.$state[0], JText::_($state[2]), null, true).'</a>';

		return $html;
	}

	/**
	 * Display an HTML select list of state filters
	 *
	 * @param   int $selected	The selected value of the list
	 * @return  string  	The HTML code for the select tag
	 * @since   1.6
	 */
	public static function filterstate($selected)
	{
		// Build the active state filter options.
		$options	= array();
		$options[]	= JHtml::_('select.option', '*', JText::_('JOPTION_ANY'));
		$options[]	= JHtml::_('select.option', '1', JText::_('JPUBLISHED'));
		$options[]	= JHtml::_('select.option', '0', JText::_('JUNPUBLISHED'));

		return JHtml::_('select.genericlist', $options, 'filter_published',
			array(
				'list.attr' => 'class="inputbox" onchange="this.form.submit();"',
				'list.select' => $selected
			)
		);
	}

	/**
	 * @param   int $newsfeedid	The newsfeed item id
	 */
	public static function association($newsfeedid)
	{
		// Get the associations
		$associations = NewsfeedsHelper::getAssociations($newsfeedid);

		foreach ($associations as $tag => $associated)
		{
			$associations[$tag] = (int) $associated->id;
		}

		// Get the associated newsfeed items
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('c.*');
		$query->from('#__newsfeeds as c');
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
			if ($associated != $newsfeedid)
			{
				$text[] = JText::sprintf('COM_NEWSFEEDS_TIP_ASSOCIATED_LANGUAGE', JHtml::_('image', 'mod_languages/'.$items[$associated]->image.'.gif', $items[$associated]->language_title, array('title' => $items[$associated]->language_title), true), $items[$associated]->name, $items[$associated]->category_title);
			}
		}
		return JHtml::_('tooltip', implode('<br />', $text), JText::_('COM_NEWSFEEDS_TIP_ASSOCIATION'), 'admin/icon-16-links.png');
	}
}
