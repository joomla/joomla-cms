<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * Admin Component Help Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @since		1.6
 */
class AdminModelHelp extends JModel
{
	/**
	 * @var string the help url
	 */
	protected $help_url = null;

	/**
	 * @var string the full help url
	 */
	protected $full_help_url = null;

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
	protected $latest_version_check= 'http://www.joomla.org/content/blogcategory/57/111/';

	/**
	 * Method to get the Help URL
	 * @return string Help URL
	 */
	function &getHelpURL()
	{
		if (is_null($this->help_url))
		{
			$app = & JFactory::getApplication();
			$this->help_url = $app->getCfg('helpurl');
			$this->help_url = 'http://help.joomla.org';
		}
		return $this->help_url;
	}
	/**
	 * Method to get the Full Help URL
	 * @return string Full Help URL
	 */
	function &getFullHelpURL()
	{
		if (is_null($this->full_help_url)) {
			$this->full_help_url = $this->getHelpURL() . '/index2.php?option=com_content&amp;task=findkey&amp;pop=1&amp;keyref=';
		}
		return $this->full_help_url;
	}
	/**
	 * Method to get the help search string
	 * @return string Help search string
	 */
	function &getHelpSearch()
	{
		if (is_null($this->help_search)) {
			$this->help_search = &JRequest::getString('helpsearch');
		}
		return $this->help_search;
	}
	/**
	 * Method to get the page
	 * @return string page
	 */
	function &getPage()
	{
		if (is_null($this->page))
		{
			$this->page = & JRequest::getCmd('page', 'joomla.whatsnew.html');
			if (!eregi('\.html$', $this->page)) {
				$this->page.= '.xml';
			}
		}
		return $this->page;
	}
	/**
	 * Method to get the lang tag
	 * @return string lang iso tag
	 */
	function &getLangTag()
	{
		if (is_null($this->lang_tag))
		{
			$lang = & JFactory::getLanguage();
			$this->lang_tag = $lang->getTag();
			jimport('joomla.filesystem.folder');
			if (!JFolder::exists(JPATH_BASE . DS . 'help' . DS . $this->lang_tag)) {
				$this->lang_tag = 'en-GB'; // use english as fallback
			}

		}
		return $this->lang_tag;
	}
	/**
	 * Method to get the toc
	 * @return array Table of contents
	 */
	function &getToc()
	{
		if (is_null($this->toc))
		{
			// Get vars
			$lang_tag = &$this->getLangTag();
			$help_url = &$this->getHelpURL();
			$help_search = &$this->getHelpSearch();

			// Get Help files
			$files = JFolder::files(JPATH_BASE . DS . 'help' . DS . $lang_tag, '\.xml$|\.html$');
			$this->toc = array();
			foreach($files as $file)
			{
				$buffer = file_get_contents(JPATH_BASE . DS . 'help' . DS . $lang_tag . DS . $file);
				if (preg_match('#<title>(.*?)</title>#', $buffer, $m))
				{
					$title = trim($m[1]);
					if ($title) {
						if ($help_url) {
							// strip the extension
							$file = preg_replace('#\.xml$|\.html$#', '', $file);
						}
						if ($help_search)
						{
							if (JString::strpos(JString::strtolower(strip_tags($buffer)), JString::strtolower($help_search)) !== false) {
								// Add an item in the Table of Contents
								$this->toc[$file] = $title;
							}
						}
						else
						{
							// Add an item in the Table of Contents
							$this->toc[$file] = $title;
						}
					}
				}
			}
			// Sort the Table of Contents
			asort($this->toc);
		}
		return $this->toc;
	}
	/**
	 * Method to get the latest version check;
	 * @return string Latest Version Check URL
	 */
	function &getLatestVersionCheck()
	{
		return $this->latest_version_check;
	}
}
