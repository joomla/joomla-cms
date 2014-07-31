<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Displays the multilang status.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.7.1
 */
class LanguagesViewMultilangstatus extends JViewLegacy
{
	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		require_once JPATH_COMPONENT . '/helpers/multilangstatus.php';

		$this->homes			= multilangstatusHelper::getHomes();
		$this->language_filter	= JLanguageMultilang::isEnabled();
		$this->switchers		= multilangstatusHelper::getLangswitchers();
		$this->listUsersError	= multilangstatusHelper::getContacts();
		$this->contentlangs		= multilangstatusHelper::getContentlangs();
		$this->site_langs		= multilangstatusHelper::getSitelangs();
		$this->statuses			= multilangstatusHelper::getStatus();
		$this->homepages		= multilangstatusHelper::getHomepages();

		parent::display($tpl);
	}
}
