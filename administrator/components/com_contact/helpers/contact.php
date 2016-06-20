<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contact component helper.
 *
 * @since  1.6
 */
class ContactHelper extends JHelperContent
{
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
			JText::_('COM_CONTACT_SUBMENU_CONTACTS'),
			'index.php?option=com_contact&view=contacts',
			$vName == 'contacts'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_CONTACT_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_contact',
			$vName == 'categories'
		);
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   stdClass[]  &$items  The banner category objects
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.5
	 */
	public static function countItems(&$items)
	{
		$db = JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select('published AS state, count(*) AS count')
				->from($db->qn('#__contact_details'))
				->where('catid = ' . (int) $item->id)
				->group('published');
			$db->setQuery($query);
			$contacts = $db->loadObjectList();

			foreach ($contacts as $contact)
			{
				if ($contact->state == 1)
				{
					$item->count_published = $contact->count;
				}

				if ($contact->state == 0)
				{
					$item->count_unpublished = $contact->count;
				}

				if ($contact->state == 2)
				{
					$item->count_archived = $contact->count;
				}

				if ($contact->state == -2)
				{
					$item->count_trashed = $contact->count;
				}
			}
		}

		return $items;
	}
}
