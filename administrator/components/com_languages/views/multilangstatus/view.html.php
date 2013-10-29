<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Displays the multilang status.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 * @since       1.7.1
 */
class LanguagesViewMultilangstatus extends JViewLegacy
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		require_once JPATH_COMPONENT . '/helpers/multilangstatus.php';

		$this->homes			= MultilangstatusHelper::getHomes();
		$this->language_filter	= JLanguageMultilang::isEnabled();
		$this->switchers		= MultilangstatusHelper::getLangswitchers();
		$this->listUsersError	= MultilangstatusHelper::getContacts();
		$this->contentlangs		= MultilangstatusHelper::getContentlangs();
		$this->site_langs		= MultilangstatusHelper::getSitelangs();
		$this->statuses			= MultilangstatusHelper::getStatus();
		$this->homepages		= MultilangstatusHelper::getHomepages();

		parent::display($tpl);
	}
}
