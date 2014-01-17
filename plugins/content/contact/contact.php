<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Contact
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contact Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.Contact
 * @since       3.2
 */
class PlgContentContact extends JPlugin
{
	/**
	 * Plugin that retrieves contact information for contact
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   mixed    &$row     An object with a "text" property
	 * @param   mixed    $params   Additional parameters. See {@see PlgContentContent()}.
	 * @param   integer  $page     Optional page number. Unused. Defaults to zero.
	 *
	 * @return  boolean	True on success.
	 */
	public function onContentPrepare($context, &$row, $params, $page = 0)
	{
		$allowed_contexts = array('com_content.category', 'com_content.article', 'com_content.featured');
		if (!in_array($context, $allowed_contexts))
		{
			return true;
		}

		// Return if we don't have valid params or don't link the author
		if (!($params instanceof JRegistry) || !$params->get('link_author'))
		{
			return true;
		}

		// Return if we don't have a valid article id
		if (!isset($row->id) || !(int) $row->id)
		{
			return true;
		}

		$row->contactid = $this->getContactID($row->created_by);

		if ($row->contactid)
		{
			$needle = 'index.php?option=com_contact&view=contact&id=' . $row->contactid;
			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getItems('link', $needle, true);
			$link = $item ? $needle . '&Itemid=' . $item->id : $needle;
			$row->contact_link = JRoute::_($link);
		}
		else
		{
			$row->contact_link = '';
		}

		return true;
	}

	/**
	 * Retrieve Contact
	 *
	 * @param   int    $created_by
	 *
	 * @return  mixed|null|integer
	 */
	protected function getContactID($created_by)
	{
		static $contacts = array();
		if(isset($contacts[$created_by]))
		{
			return $contacts[$created_by];
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('MAX(contact.id) AS contactid');
		$query->from('#__contact_details AS contact');
		$query->where('contact.published = 1');
		$query->where('contact.user_id = ' . (int) $created_by);

		if (JLanguageMultilang::isEnabled() == 1)
		{
			$query->where('(contact.language in '
				. '(' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') '
				. ' OR contact.language IS NULL)');
		}

		$db->setQuery($query);

		$contacts[$created_by] = $db->loadResult();

		return $contacts[$created_by];
	}
}
