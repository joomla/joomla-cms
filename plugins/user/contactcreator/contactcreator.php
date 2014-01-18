<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.contactcreator
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Class for Contact Creator
 *
 * A tool to automatically create and synchronise contacts with a user
 *
 * @package     Joomla.Plugin
 * @subpackage  User.contactcreator
 * @since       1.6
 */
class PlgUserContactCreator extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * After user save.
	 * 
	 * @param   array    $user     Array of user information.
	 * @param   boolean  $isnew    True if user is newly registered.
	 * @param   boolean  $success  True if user was successfully stored.
	 * @param   string   $msg      Not used.
	 * 
	 * @return  void
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		// If the user wasn't stored we don't resync.
		if (!$success)
		{
			return false;
		}

		// If the user isn't new we don't sync.
		if (!$isnew)
		{
			return false;
		}

		// Ensure the user id is really an int.
		$user_id = (int) $user['id'];

		// If the user id appears invalid then bail out just in case.
		if (empty($user_id))
		{
			die('invalid userid');

			return false;
		}

		$category = $this->params->get('category', 0);

		// Bail out if we don't have a category.
		if (empty($category))
		{
			JError::raiseWarning(41, JText::_('PLG_CONTACTCREATOR_ERR_NO_CATEGORY'));

			return false;
		}

		$db = JFactory::getDbo();

		// Grab the contact ID for this user; note $user_id is cleaned above.
		$db->setQuery('SELECT id FROM #__contact_details WHERE user_id = ' . $user_id);
		$id = $db->loadResult();

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_contact/tables');
		$contact = JTable::getInstance('contact', 'ContactTable');

		if (!$contact)
		{
			return false;
		}

		if ($id)
		{
			$contact->load($id);
		}
		elseif ($this->params->get('autopublish', 0))
		{
			$contact->published = 1;
		}

		$contact->name = $user['name'];
		$contact->user_id = $user_id;
		$contact->email_to = $user['email'];
		$contact->catid = $category;
		$contact->language = '*';

		$autowebpage = $this->params->get('autowebpage', '');

		if (!empty($autowebpage))
		{
			// Search terms.
			$search_array = array('[name]', '[username]', '[userid]', '[email]');

			// Replacement terms, urlencoded.
			$replace_array = array_map('urlencode', array($user['name'], $user['username'], $user['id'], $user['email']));

			// Now replace it in together.
			$contact->webpage = str_replace($search_array, $replace_array, $autowebpage);
		}

		if ($contact->check())
		{
			$result = $contact->store();
		}

		if (!(isset($result)) || !$result)
		{
			JError::raiseError(42, JText::sprintf('PLG_CONTACTCREATOR_ERR_FAILED_UPDATE', $contact->getError()));
		}
	}
}
