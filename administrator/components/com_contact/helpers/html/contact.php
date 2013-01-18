<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContactHelper', JPATH_ADMINISTRATOR . '/components/com_contact/helpers/contact.php');

/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 */
abstract class JHtmlContact
{
	/**
	 * @param   int $contactid	The contact item id
	 */
	public static function association($contactid)
	{
		// Get the associations
		$associations = ContactHelper::getAssociations($contactid);

		foreach ($associations as $tag => $associated)
		{
			$associations[$tag] = (int) $associated->id;
		}

		// Get the associated contact items
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('c.*');
		$query->from('#__contact_details as c');
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
			if ($associated != $contactid)
			{
				$text[] = JText::sprintf('COM_CONTACT_TIP_ASSOCIATED_LANGUAGE', JHtml::_('image', 'mod_languages/'.$items[$associated]->image.'.gif', $items[$associated]->language_title, array('title' => $items[$associated]->language_title), true), $items[$associated]->name, $items[$associated]->category_title);
			}
		}
		return JHtml::_('tooltip', implode('<br />', $text), JText::_('COM_CONTACT_TIP_ASSOCIATION'), 'admin/icon-16-links.png');
	}

	/**
	 * @param   int $value	The featured value
	 * @param   int $i
	 * @param   bool $canChange Whether the value can be changed or not
	 *
	 * @return  string	The anchor tag to toggle featured/unfeatured contacts.
	 * @since   1.6
	 */
	public static function featured($value = 0, $i, $canChange = true)
	{
		// Array of image, task, title, action
		$states	= array(
			0	=> array('disabled.png', 'contacts.featured', 'COM_CONTACT_UNFEATURED', 'COM_CONTACT_TOGGLE_TO_FEATURE'),
			1	=> array('featured.png', 'contacts.unfeatured', 'JFEATURED', 'COM_CONTACT_TOGGLE_TO_UNFEATURE'),
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[1]);
		$html	= JHtml::_('image', 'admin/'.$state[0], JText::_($state[2]), null, true);
		if ($canChange)
		{
			$html	= '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.JText::_($state[3]).'">'
					. $html .'</a>';
		}

		return $html;
	}
}
