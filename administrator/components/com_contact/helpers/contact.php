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

		if (JComponentHelper::isEnabled('com_fields') && JComponentHelper::getParams('com_contact')->get('custom_fields_enable', '1'))
		{
			JHtmlSidebar::addEntry(
				JText::_('JGLOBAL_FIELDS'),
				'index.php?option=com_fields&context=com_contact.contact',
				$vName == 'fields.fields'
			);
			JHtmlSidebar::addEntry(
				JText::_('JGLOBAL_FIELD_GROUPS'),
				'index.php?option=com_fields&view=groups&context=com_contact.contact',
				$vName == 'fields.groups'
			);
		}
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   stdClass[]  &$items  The contact category objects
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

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   stdClass[]  &$items     The banner tag objects
	 * @param   string      $extension  The name of the active view.
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.6
	 */
	public static function countTagItems(&$items, $extension)
	{
		$db = JFactory::getDbo();
		$parts     = explode('.', $extension);
		$section   = null;
		if (count($parts) > 1)
		{
			$section = $parts[1];
		}
		$join = $db->qn('#__contact_details') . ' AS c ON ct.content_item_id=c.id';
		if ($section === 'category')
		{
			$join = $db->qn('#__categories') . ' AS c ON ct.content_item_id=c.id';
		}
		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select('published as state, count(*) AS count')
				->from($db->qn('#__contentitem_tag_map') . 'AS ct ')
				->where('ct.tag_id = ' . (int) $item->id)
				->where('ct.type_alias =' . $db->q($extension))
				->join('LEFT', $join)
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

	/**
	 * Returns valid contexts
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public static function getContexts()
	{
		JFactory::getLanguage()->load('com_contact', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_contact.contact' => JText::_('COM_CONTACT_FIELDS_CONTEXT_CONTACT'),
			'com_contact.mail'    => JText::_('COM_CONTACT_FIELDS_CONTEXT_MAIL'),
		);

		return $contexts;
	}
}
