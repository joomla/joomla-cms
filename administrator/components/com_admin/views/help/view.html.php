<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Admin
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * HTML View class for the Admin component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Admin
 * @since 1.0
 */
class AdminViewHelp extends JView
{
	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		jimport('joomla.filesystem.folder');
		jimport('joomla.language.help');

		// Get Help URL - an empty helpurl is interpreted as local help files!
		$helpurl	= $mainframe->getCfg('helpurl');
		if ($helpurl == 'http://help.mamboserver.com') {
			$helpurl = 'http://help.joomla.org';
		}
		$fullhelpurl = $helpurl . '/index2.php?option=com_content&amp;task=findkey&amp;pop=1&amp;keyref=';

		$helpsearch = JRequest::getString('helpsearch');
		$page		= JRequest::getCmd('page', 'joomla.whatsnew15.html');
		$toc		= AdminViewHelp::getHelpToc($helpsearch);
		$lang		=& JFactory::getLanguage();
		$langTag = $lang->getTag();
		if(!JFolder::exists(JPATH_BASE.DS.'help'.DS.$langTag)) {
			$langTag = 'en-GB';		// use english as fallback
		}

		if (!eregi('\.html$', $page)) {
			$page .= '.xml';
		}

		// Toolbar
		JToolBarHelper::title(JText::_('Help'), 'help_header.png');

		$this->assignRef('fullhelpurl',	$fullhelpurl);
		$this->assignRef('helpsearch',	$helpsearch);
		$this->assignRef('page',		$page);
		$this->assignRef('toc',			$toc);
		$this->assignRef('lang',		$lang);
		$this->assignRef('langTag',		$langTag);

		parent::display($tpl);
	}

	/**
	 * Compiles the help table of contents
	 * @param string A specific keyword on which to filter the resulting list
	 */
	function getHelpTOC($helpsearch)
	{
		$mainframe = JFactory::getApplication();

		$lang =& JFactory::getLanguage();
		jimport('joomla.filesystem.folder');

		$helpurl		= $mainframe->getCfg('helpurl');

		// Check for files in the actual language
		$langTag = $lang->getTag();
		if(!JFolder::exists(JPATH_BASE.DS.'help'.DS.$langTag)) {
			$langTag = 'en-GB';		// use english as fallback
		}
		$files = JFolder::files(JPATH_BASE.DS.'help'.DS.$langTag, '\.xml$|\.html$');

		$toc = array();
		foreach ($files as $file) {
			$buffer = file_get_contents(JPATH_BASE.DS.'help'.DS.$langTag.DS.$file);
			if (preg_match('#<title>(.*?)</title>#', $buffer, $m)) {
				$title = trim($m[1]);
				if ($title) {
					if ($helpurl) {
						// strip the extension
						$file = preg_replace('#\.xml$|\.html$#', '', $file);
					}
					if ($helpsearch) {
						if (JString::strpos(strip_tags($buffer), $helpsearch) !== false) {
							$toc[$file] = $title;
						}
					} else {
						$toc[$file] = $title;
					}
				}
			}
		}
		asort($toc);
		return $toc;
	}
}