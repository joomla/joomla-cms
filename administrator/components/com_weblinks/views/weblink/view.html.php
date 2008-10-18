<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Weblinks
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.0
 */
class WeblinksViewWeblink extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		if($this->getLayout() == 'form') {
			$this->_displayForm($tpl);
			return;
		}

		//get the weblink
		$weblink =& $this->get('data');

		if ($weblink->url) {
			// redirects to url if matching id found
			$mainframe->redirect($weblink->url);
		}

		parent::display($tpl);
	}

	function _displayForm($tpl)
	{
		global $mainframe, $option;

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
			$weblink->catid 	= JRequest::getVar( 'catid', 0, 'post', 'int' );
		}

		// build the html select list for ordering
		$query = 'SELECT ordering AS value, title AS text'
			. ' FROM #__weblinks'
			. ' WHERE catid = ' . (int) $weblink->catid
			. ' ORDER BY ordering';

		$lists['ordering'] 			= JHTML::_('list.specificordering',  $weblink, $weblink->id, $query );

		// build list of categories
		$lists['catid'] 			= JHTML::_('list.category',  'catid', $option, intval( $weblink->catid ) );
		// build the html select list
		$lists['state'] 		= JHTML::_('weblink.statelist',  'state', $weblink->state );

		//clean weblink data
		JFilterOutput::objectHTMLSafe( $weblink, ENT_QUOTES, 'description' );

		$file 	= JPATH_COMPONENT.DS.'models'.DS.'weblink.xml';
		$params = new JParameter( $weblink->params, $file );

		$this->assignRef('lists',		$lists);
		$this->assignRef('weblink',		$weblink);
		$this->assignRef('params',		$params);

		parent::display($tpl);
	}
}
