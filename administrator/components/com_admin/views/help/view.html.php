<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * HTML View class for the Admin component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @since		1.6
 */
class AdminViewHelp extends JView
{
	/**
	 * @var string the help url
	 */
	protected $help_url=null;
	/**
	 * @var string the full help url
	 */
	protected $full_help_url=null;
	/**
	 * @var string the search string
	 */
	protected $help_search=null;
	/**
	 * @var string the page to be viewed
	 */
	protected $page=null;
	/**
	 * @var string the iso language tag
	 */
	protected $lang_tag=null;
	/**
	 * @var array Table of contents
	 */
	protected $toc=null;
	/**
	 * @var string url for the latest version check
	 */
	protected $latest_version_check= 'http://www.joomla.org/content/blogcategory/57/111/';

	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		// Get the values	
		$help_url = & $this->get('HelpURL');
		$full_help_url = & $this->get('FullHelpURL');
		$help_search = & $this->get('HelpSearch');
		$page = & $this->get('Page');
		$toc = & $this->get('Toc');
		$lang_tag = & $this->get('LangTag');
		$latest_version_check = & $this->get('LatestVersionCheck');

		// Assign values to the view
		$this->assignRef('help_url', $help_url);
		$this->assignRef('full_help_url', $full_help_url);
		$this->assignRef('help_search', $help_search);
		$this->assignRef('page', $page);
		$this->assignRef('toc', $toc);
		$this->assignRef('lang_tag', $lang_tag);
		$this->assignRef('latest_version_check', $latest_version_check);

		// Setup the toolbar
		$this->_setToolBar();

		// Display the view
		parent::display($tpl);
	}
	/**
	 * Setup the Toolbar
	 *
	 * @since	1.6
	 */
	protected function _setToolBar()
	{
		JToolBarHelper::title(JText::_('Admin_Help'), 'help_header.png');
	}
}

