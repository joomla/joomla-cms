<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.contact
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');

/**
 * Privacy plugin managing Joomla user contact data
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgPrivacyContact extends PrivacyPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Affects constructor behaviour. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Contacts array
	 *
	 * @var    Array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $contacts = array();

	/**
	 * Processes an export request for Joomla core user contact data
	 *
	 * This event will collect data for the contact core tables:
	 *
	 * - Contact custom fields
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 *
	 * @return  PrivacyExportDomain[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onPrivacyExportRequest(PrivacyTableRequest $request)
	{
		if ((!$request->user_id) && (!$request->email))
		{
			return array();
		}

		/** @var JTableUser $user */
		$user = JUser::getTable();
		$user->load($request->user_id);

		$domains   = array();
		$domains[] = $this->createContactDomain($user);

		// An user may have more than 1 contact linked to them
		foreach ($this->contacts as $contact)
		{
			$domains[] = $this->createContactCustomFieldsDomain($contact);
		}

		return $domains;
	}

	/**
	 * Create the domain for the user contact data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function createContactDomain(JTableUser $user)
	{
		$domain = $this->createDomain('user contact', 'Joomla! user contact data');

		if (!$user->email)
		{
			$query = $this->db->getQuery(true)
				->select('*')
				->from($this->db->quoteName('#__contact_details'))
				->where($this->db->quoteName('user_id') . ' = ' . (int) $user->id)
				->order($this->db->quoteName('ordering') . ' ASC');
		}
		else
		{
			$query = $this->db->getQuery(true)
				->select('*')
				->from($this->db->quoteName('#__contact_details'))
				->where($this->db->quoteName('email_to') . ' = ' . $this->db->quote($user->email))
				->order($this->db->quoteName('ordering') . ' ASC');
		}

		$items = $this->db->setQuery($query)->loadAssocList();

		foreach ($items as $item)
		{
			$domain->addItem($this->createItemFromArray($item));
			$this->contacts[] = (object) $item;
		}

		return $domain;
	}

	/**
	 * Create the domain for the contact custom fields
	 *
	 * @param   Object  $contact  The contact to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function createContactCustomFieldsDomain($contact)
	{
		$domain = $this->createDomain('contact custom fields', 'Joomla! contact custom fields data');

		// Get item's fields, also preparing their value property for manual display
		$fields = FieldsHelper::getFields('com_contact.contact', $contact);

		foreach ($fields as $field)
		{
			$fieldValue = is_array($field->value) ? implode(', ', $field->value) : $field->value;

			$data = array(
				'contact_id'  => $contact->id,
				'field_name'  => $field->name,
				'field_title' => $field->title,
				'field_value' => $fieldValue,
			);

			$domain->addItem($this->createItemFromArray($data));
		}

		return $domain;
	}
}
