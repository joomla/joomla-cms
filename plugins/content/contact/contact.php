<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Contact
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Contact Plugin
 *
 * @since  3.2
 */
class PlgContentContact extends JPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.3
	 */
	protected $db;

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
		if (!($params instanceof Registry) || !$params->get('link_author'))
		{
			return true;
		}

		// Return if an alias is used
		if ((int) $this->params->get('link_to_alias', 0) === 0 && $row->created_by_alias != '')
		{
			return true;
		}

		// Return if we don't have a valid article id
		if (!isset($row->id) || !(int) $row->id)
		{
			return true;
		}

		$contact        = $this->getContactData($row->created_by);
		$row->contactid = $contact->contactid;
		$row->webpage   = $contact->webpage;
		$row->email     = $contact->email_to;
		$url            = $this->params->get('url', 'url');

		if ($row->contactid && $url === 'url')
		{
			JLoader::register('ContactHelperRoute', JPATH_SITE . '/components/com_contact/helpers/route.php');
			$row->contact_link = JRoute::_(ContactHelperRoute::getContactRoute($contact->contactid . ':' . $contact->alias, $contact->catid));
		}
		elseif ($row->webpage && $url === 'webpage')
		{
			$row->contact_link = $row->webpage;
		}
		elseif ($row->email && $url === 'email')
		{
			$row->contact_link = 'mailto:' . $row->email;
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
	 * @param   int  $created_by  Id of the user who created the contact
	 *
	 * @return  mixed|null|integer
	 */
	protected function getContactData($created_by)
	{
		static $contacts = array();

		if (isset($contacts[$created_by]))
		{
			return $contacts[$created_by];
		}

		$query = $this->db->getQuery(true);

		$query->select('MAX(contact.id) AS contactid, contact.alias, contact.catid, contact.webpage, contact.email_to');
		$query->from($this->db->quoteName('#__contact_details', 'contact'));
		$query->where('contact.published = 1');
		$query->where('contact.user_id = ' . (int) $created_by);

		if (JLanguageMultilang::isEnabled() === true)
		{
			$query->where('(contact.language in '
				. '(' . $this->db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->db->quote('*') . ') '
				. ' OR contact.language IS NULL)');
		}

		$this->db->setQuery($query);

		$contacts[$created_by] = $this->db->loadObject();

		return $contacts[$created_by];
	}
}
