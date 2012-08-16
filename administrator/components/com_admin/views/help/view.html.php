<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Admin component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 * @since       1.6
 */
class AdminViewHelp extends JViewLegacy
{
	/**
	 * @var string the search string
	 */
	protected $help_search = null;

	/**
	 * @var string the page to be viewed
	 */
	protected $page = null;

	/**
	 * @var string the iso language tag
	 */
	protected $lang_tag = null;

	/**
	 * @var array Table of contents
	 */
	protected $toc = null;

	/**
	 * @var string url for the latest version check
	 */
	protected $latest_version_check = 'http://www.joomla.org/download.html';

	/**
	 * @var string url for the start here link.
	 */
	protected $start_here = null;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		// Initialise variables.
		$this->help_search			= $this->get('HelpSearch');
		$this->page					= $this->get('Page');
		$this->toc					= $this->get('Toc');
		$this->lang_tag				= $this->get('LangTag');
		$this->latest_version_check	= $this->get('LatestVersionCheck');

		$this->addToolbar();
		parent::display($tpl);
	}
	/**
	 * Setup the Toolbar
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_ADMIN_HELP'), 'help_header.png');
	}
}
