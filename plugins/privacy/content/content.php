<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.uscontenter
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');

/**
 * Privacy plugin managing Joomla user content data
 *
 * @since  3.9.0
 */
class PlgPrivacyContent extends PrivacyPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.9.0
	 */
	protected $db;

	/**
	 * Affects constructor behaviour. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Contents array
	 *
	 * @var    Array
	 * @since  3.9.0
	 */
	protected $contents = array();

	/**
	 * The language to load.
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	protected $lang = null;

	/**
	 * Processes an export request for Joomla core user content data
	 *
	 * This event will collect data for the content core table
	 *
	 * - Content custom fields
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  PrivacyExportDomain[]
	 *
	 * @since   3.9.0
	 */
	public function onPrivacyExportRequest(PrivacyTableRequest $request, JUser $user = null)
	{
		if (!$user)
		{
			return array();
		}

		$lang = JFactory::getLanguage();

		$langSiteDefault = JComponentHelper::getParams('com_languages')->get('site');

		if ($user)
		{
			$receiver = JUser::getInstance($user->id);

			/*
			 * We don't know if the user has admin access, so we will check if they have an admin language in their parameters,
			 * falling back to the site language, falling back to the site default language.
			 */

			$langCode = $receiver->getParam('admin_language', '');

			if (!$langCode)
			{
				$langCode = $receiver->getParam('language', $langSiteDefault);
			}

			$lang = JLanguage::getInstance($langCode, $lang->getDebug());
		}
		else
		{
			$lang = JLanguage::getInstance($langSiteDefault, $lang->getDebug());
		}

		// Ensure the right language files have been loaded
		$lang->load('plg_privacy_content', JPATH_ADMINISTRATOR, null, false, true)
			|| $lang->load('plg_privacy_content', JPATH_SITE . '/plugins/privacy/content', null, false, true);

		$this->lang = $lang;

		$domains   = array();
		$domains[] = $this->createContentDomain($user);

		foreach ($this->contents as $content)
		{
			$domains[] = $this->createContentCustomFieldsDomain($content);
		}

		return $domains;
	}

	/**
	 * Create the domain for the user content data
	 *
	 * @param   JUser  $user  The user account associated with this request
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.9.0
	 */
	private function createContentDomain(JUser $user)
	{
		$domain = $this->createDomain(
			$this->lang->_('PLG_PRIVACY_CONTENT_DOMAIN_LABEL'),
			$this->lang->_('PLG_PRIVACY_CONTENT_DOMAIN_DESC')
		);

		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->quoteName('#__content'))
			->where($this->db->quoteName('created_by') . ' = ' . (int) $user->id)
			->order($this->db->quoteName('ordering') . ' ASC');

		$items = $this->db->setQuery($query)->loadAssocList();

		foreach ($items as $item)
		{
			$domain->addItem($this->createItemFromArray($item));
			$this->contents[] = (object) $item;
		}

		return $domain;
	}

	/**
	 * Create the domain for the content custom fields
	 *
	 * @param   Object  $content  The content to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.9.0
	 */
	private function createContentCustomFieldsDomain($content)
	{
		$domain = $this->createDomain(
			$this->lang->_('PLG_PRIVACY_CONTENT_DOMAIN_CUSTOMFIELDS_LABEL'),
			$this->lang->_('PLG_PRIVACY_CONTENT_DOMAIN_CUSTOMFIELDS_DESC')
		);

		// Get item's fields, also preparing their value property for manual display
		$fields = FieldsHelper::getFields('com_content.article', $content);

		foreach ($fields as $field)
		{
			$fieldValue = is_array($field->value) ? implode(', ', $field->value) : $field->value;

			$data = array(
				'content_id'  => $content->id,
				'field_name'  => $field->name,
				'field_title' => $field->title,
				'field_value' => $fieldValue,
			);

			$domain->addItem($this->createItemFromArray($data));
		}

		return $domain;
	}
}
