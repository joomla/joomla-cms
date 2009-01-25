<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Weblinks
 * @since 1.0
 */
class WeblinksViewWeblink extends JView
{
	function display($tpl = null)
	{
		// Helper classes
		JHtml::addIncludePath(JPATH_COMPONENT.DS.'classes');

		if ($this->getLayout() == 'form') {
			$this->_displayForm($tpl);
			return;
		}

		//get the weblink
		$weblink =& $this->get('data');

		if ($weblink->url) {
			// redirects to url if matching id found
			JFactory::getApplication()->redirect($weblink->url);
		}

		parent::display($tpl);
	}

	function _displayForm($tpl)
	{
		$db		=& JFactory::getDBO();
		$uri 	=& JFactory::getURI();
		$user 	=& JFactory::getUser();
		$model	=& $this->getModel();


		$lists = array();

		//get the weblink
		$weblink	=& $this->get('data');
		$isNew		= ($weblink->id < 1);

		// Edit or Create?
		if ($isNew)
		{
			// initialise new record
			$weblink->published = 1;
			$weblink->approved 	= 1;
			$weblink->order 	= 0;
			$weblink->catid 	= JRequest::getVar('catid', 0, 'post', 'int');
		}

		// build the html select list for ordering
		$query = 'SELECT ordering AS value, title AS text'
			. ' FROM #__weblinks'
			. ' WHERE catid = ' . (int) $weblink->catid
			. ' ORDER BY ordering';

		$lists['ordering'] 			= JHtml::_('list.specificordering',  $weblink, $weblink->id, $query);

		// build list of categories
		$lists['catid'] 			= JHtml::_('list.category',  'catid', 'com_weblinks', intval($weblink->catid));
		// build the html select list
		$lists['state'] 		= JHtml::_('weblink.statelist',  'state', $weblink->state);

		//clean weblink data
		JFilterOutput::objectHTMLSafe($weblink, ENT_QUOTES, 'description');

		$file 	= JPATH_COMPONENT.DS.'models'.DS.'weblink.xml';
		$params = new JParameter($weblink->params, $file);

		$this->assignRef('lists',		$lists);
		$this->assignRef('weblink',		$weblink);
		$this->assignRef('params',		$params);

		parent::display($tpl);
	}
}
