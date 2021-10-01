<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\View\Multilangstatus;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Languages\Administrator\Helper\MultilangstatusHelper;

/**
 * Displays the multilang status.
 *
 * @since  1.7.1
 */
class HtmlView extends BaseHtmlView
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
		$this->homes           = MultilangstatusHelper::getHomes();
		$this->language_filter = Multilanguage::isEnabled();
		$this->switchers       = MultilangstatusHelper::getLangswitchers();
		$this->listUsersError  = MultilangstatusHelper::getContacts();
		$this->contentlangs    = MultilangstatusHelper::getContentlangs();
		$this->site_langs      = LanguageHelper::getInstalledLanguages(0);
		$this->statuses        = MultilangstatusHelper::getStatus();
		$this->homepages       = Multilanguage::getSiteHomePages();
		$this->defaultHome     = MultilangstatusHelper::getDefaultHomeModule();
		$this->default_lang    = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');

		parent::display($tpl);
	}
}
