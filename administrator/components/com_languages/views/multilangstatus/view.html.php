<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Displays the multilang status.
 *
 * @since  1.7.1
 */
class LanguagesViewMultilangstatus extends JViewLegacy
{
	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		JLoader::register('MultilangstatusHelper', JPATH_ADMINISTRATOR . '/components/com_languages/helpers/multilangstatus.php');

		$this->homes           = MultilangstatusHelper::getHomes();
		$this->language_filter = JLanguageMultilang::isEnabled();
		$this->switchers       = MultilangstatusHelper::getLangswitchers();
		$this->listUsersError  = MultilangstatusHelper::getContacts();
		$this->contentlangs    = MultilangstatusHelper::getContentlangs();
		$this->site_langs      = JLanguageHelper::getInstalledLanguages(0);
		$this->statuses        = MultilangstatusHelper::getStatus();
		$this->homepages       = JLanguageMultilang::getSiteHomePages();
		$this->defaultHome     = MultilangstatusHelper::getDefaultHomeModule();

		parent::display($tpl);
	}
}
