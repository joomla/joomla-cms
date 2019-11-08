<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContactHelper', JPATH_ADMINISTRATOR . '/components/com_contact/helpers/contact.php');
JLoader::register('ContactHelperRoute', JPATH_SITE . '/components/com_contact/helpers/route.php');
JLoader::register('CategoryHelperAssociation', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/association.php');

/**
 * Contact Component Association Helper
 *
 * @since  3.0
 */
abstract class ContactHelperAssociation extends CategoryHelperAssociation
{
	/**
	 * Method to get the associations for a given item
	 *
	 * @param   integer  $id    Id of the item
	 * @param   string   $view  Name of the view
	 *
	 * @return  array   Array of associations for the item
	 *
	 * @since  3.0
	 */
	public static function getAssociations($id = 0, $view = null)
	{
		$jinput = JFactory::getApplication()->input;
		$view   = $view === null ? $jinput->get('view') : $view;
		$id     = empty($id) ? $jinput->getInt('id') : $id;

		if ($view === 'contact')
		{
			if ($id)
			{
				$associations = JLanguageAssociations::getAssociations('com_contact', '#__contact_details', 'com_contact.item', $id);

				$return = array();

				foreach ($associations as $tag => $item)
				{
					$return[$tag] = ContactHelperRoute::getContactRoute($item->id, (int) $item->catid, $item->language);
				}

				return $return;
			}
		}

		if ($view === 'category' || $view === 'categories')
		{
			return self::getCategoryAssociations($id, 'com_contact');
		}

		return array();

	}
}
