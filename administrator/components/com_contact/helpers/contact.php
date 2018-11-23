<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
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
	 * @param   stdClass[]  &$items  The category objects
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.5
	 */
	public static function countItems(&$items)
	{
		$config = (object) array(
			'related_tbl'   => 'contact_details',
			'state_col'     => 'published',
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
			'related_tbl'   => ($section === 'category' ? 'categories' : 'contact_details'),
			'state_col'     => 'published',
			'group_col'     => 'tag_id',
			'extension'     => $extension,
			'relation_type' => 'tag_assigments',
		);

		return parent::countRelations($items, $config);
	}

	/**
	 * Returns a valid section for contacts. If it is not valid then null
	 * is returned.
	 *
	 * @param   string  $section  The section to get the mapping for
	 * @param   object  $item     optional item object
	 *
	 * @return  string|null  The new section
	 *
	 * @since   3.7.0
	 */
	public static function validateSection($section, $item)
	{
		if (JFactory::getApplication()->isClient('site') && $section == 'contact' && $item instanceof JForm)
		{
			// The contact form needs to be the mail section
			$section = 'mail';
		}

		if (JFactory::getApplication()->isClient('site') && $section == 'category')
		{
			// The contact form needs to be the mail section
			$section = 'contact';
		}

		if ($section != 'mail' && $section != 'contact')
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
		JFactory::getLanguage()->load('com_contact', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_contact.contact'    => JText::_('COM_CONTACT_FIELDS_CONTEXT_CONTACT'),
			'com_contact.mail'       => JText::_('COM_CONTACT_FIELDS_CONTEXT_MAIL'),
			'com_contact.categories' => JText::_('JCATEGORY')
		);

		return $contexts;
	}
}
