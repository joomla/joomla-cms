<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.contactcreator
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Class for Contact Creator
 *
 * A tool to automatically create and synchronise contacts with a user
 *
 * @since  1.6
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
	 * Utility method to act on a user after it has been saved.
	 *
	 * This method creates a contact for the saved user
	 *
	 * @param   array    $user     Holds the new user data.
	 * @param   boolean  $isnew    True if a new user is stored.
	 * @param   boolean  $success  True if user was succesfully stored in the database.
	 * @param   string   $msg      Message.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		// If the user wasn't stored we don't resync
		if (!$success)
		{
			return false;
		}

		// If the user isn't new we don't sync
		if (!$isnew)
		{
			return false;
		}

		// Ensure the user id is really an int
		$user_id = (int) $user['id'];

		// If the user id appears invalid then bail out just in case
		if (empty($user_id))
		{
			return false;
		}

		$categoryId = $this->params->get('category', 0);

		if (empty($categoryId))
		{
			JError::raiseWarning('', JText::_('PLG_CONTACTCREATOR_ERR_NO_CATEGORY'));

			return false;
		}

		if ($contact = $this->getContactTable())
		{
			/**
			 * Try to pre-load a contact for this user. Apparently only possible if other plugin creates it
			 * Note: $user_id is cleaned above
			 */
			if (!$contact->load(array('user_id' => (int) $user_id)))
			{
				$contact->published = $this->params->get('autopublish', 0);
			}

			$contact->name     = $user['name'];
			$contact->user_id  = $user_id;
			$contact->email_to = $user['email'];
			$contact->catid    = $categoryId;
			$contact->access   = (int) JFactory::getConfig()->get('access');
			$contact->language = '*';
			$contact->generateAlias();

			// Check if the contact already exists to generate new name & alias if required
			if ($contact->id == 0)
			{
				list($name, $alias) = $this->generateAliasAndName($contact->alias, $contact->name, $categoryId);

				$contact->name  = $name;
				$contact->alias = $alias;
			}

			$autowebpage = $this->params->get('autowebpage', '');

			if (!empty($autowebpage))
			{
				// Search terms
				$search_array = array('[name]', '[username]', '[userid]', '[email]');

				// Replacement terms, urlencoded
				$replace_array = array_map('urlencode', array($user['name'], $user['username'], $user['id'], $user['email']));

				// Now replace it in together
				$contact->webpage = str_replace($search_array, $replace_array, $autowebpage);
			}

			if ($contact->check() && $contact->store())
			{
				return true;
			}
		}

		JError::raiseWarning('', JText::_('PLG_CONTACTCREATOR_ERR_FAILED_CREATING_CONTACT'));

		return false;
	}

	/**
	 * Method to change the name & alias if alias is already in use
	 *
	 * @param   string   $alias       The alias.
	 * @param   string   $name        The name.
	 * @param   integer  $categoryId  Category identifier
	 *
	 * @return  array  Contains the modified title and alias.
	 *
	 * @since   3.2.3
	 */
	protected function generateAliasAndName($alias, $name, $categoryId)
	{
		$table = $this->getContactTable();

		while ($table->load(array('alias' => $alias, 'catid' => $categoryId)))
		{
			if ($name == $table->name)
			{
				$name = StringHelper::increment($name);
			}

			$alias = StringHelper::increment($alias, 'dash');
		}

		return array($name, $alias);
	}

	/**
	 * Get an instance of the contact table
	 *
	 * @return  ContactTableContact
	 *
	 * @since   3.2.3
	 */
	protected function getContactTable()
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_contact/tables');

		return JTable::getInstance('contact', 'ContactTable');
	}
}
