<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package Joomla
 * @subpackage Weblinks
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
		$weblink =& $this->get('weblink');
		
		if ($weblink->url) 
		{	
			// Record the hit
			$model =& $this->getModel();
			$model->incrementHit();
		
			// redirects to url if matching id found
			$mainframe->redirect($weblink->url);
		}

		parent::display($tpl);
	}

	function _displayForm($tpl)
	{
		global $mainframe, $option;

		// Get some objects from the JApplication
		$user		= & JFactory::getUser();
		$pathway	= & $mainframe->getPathWay();
		$document	= & JFactory::getDocument();
		$model      =& $this->getModel();

		// Make sure you are logged in
		if ($user->get('gid') < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}
		
		/*
		 * Disabled until ACL system is implemented.  When enabled the $id variable
		 * will be used instead of a 0
		 */
		$returnid = JRequest::getVar( 'Returnid', 0, '', 'int' );

		//get the weblink
		$weblink =& $this->get('weblink');
		$isNew   = ($weblink->id < 1);
		
		// Is this link checked out?  If not by me fail
		if ($model->isCheckedOut($user->get('id'))) {
			$mainframe->redirect("index2.php?option=$option", "The module $weblink->title is currently being edited by another administrator.");
		}

		// Edit or Create?
		if ($isNew)
		{
			/*
			 * The web link already exists so we are editing it.  Here we want to
			 * manipulate the pathway and pagetitle to indicate this, plus we want
			 * to check the web link out so no one can edit it while we are editing it
			 */
			$model->checkout($user->get('id'));

			// Set page title
			$document->setTitle(JText::_('Links').' - '.JText::_('Edit'));

			// Add pathway item
			$pathway->addItem(JText::_('Edit'), '');
		}
		else
		{
			/*
			 * The web link does not already exist so we are creating a new one.  Here
			 * we want to manipulate the pathway and pagetitle to indicate this.  Also,
			 * we need to initialize some values.
			 */
			$weblink->published = 0;
			$weblink->approved = 1;
			$weblink->ordering = 0;

			// Set page title
			$document->setTitle(JText::_('Links').' - '.JText::_('New'));

			// Add pathway item
			$pathway->addItem(JText::_('New'), '');
		}

		// build list of categories
		$lists['catid'] = JAdminMenus::ComponentCategory('jform[catid]', $option, intval($weblink->catid));

		$this->assign('returnid', $returnid);

		$this->assignRef('lists'   , $lists);
		$this->assignRef('weblink' , $row);

		parent::display($tpl);
	}
}
?>