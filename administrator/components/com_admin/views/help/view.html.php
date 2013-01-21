<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
	 * @since  1.6
	 */
	protected $help_search = null;

	/**
	 * @var string the page to be viewed
	 * @since  1.6
	 */
	protected $page = null;

	/**
	 * @var string the iso language tag
	 * @since  1.6
	 */
	protected $lang_tag = null;

	/**
	 * @var array Table of contents
	 * @since  1.6
	 */
	protected $toc = null;

	/**
	 * @var string url for the latest version check
	 * @since  1.6
	 */
	protected $latest_version_check = 'http://www.joomla.org/download.html';

	/**
	 * @var string url for the start here link.
	 * @since  1.6
	 */
	protected $start_here = null;

	/**
	 * Method to display the view
	 *
	 * @param  string  $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since  1.6
	 */
	public function display($tpl = null)
	{
		$this->help_search			= $this->get('HelpSearch');
		$this->page					= $this->get('Page');
		$this->toc					= $this->get('Toc');
		$this->lang_tag				= $this->get('LangTag');
		$this->latest_version_check	= $this->get('LatestVersionCheck');

		$this->addToolbar();
		parent::display($tpl);
	}
	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_ADMIN_HELP'), 'help_header.png');
	}
}
