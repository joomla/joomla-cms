<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Newsfeeds
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Newsfeeds component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Newsfeeds
 * @since 1.0
 */
class NewsfeedsViewNewsfeed extends JView
{
	function display($tpl = null)
	{
		$user 		=& JFactory::getUser();
		$option 	= JRequest::getCmd( 'option' );
		$model		=& $this->getModel();

		// Set toolbar items for the page
		$edit	= JRequest::getVar('edit',true);
		$text 	= ( $edit ? JText::_( 'Edit' ) : JText::_( 'New' ) );

		JToolBarHelper::title(  JText::_( 'Newsfeed' ).': <small><small>[ '. $text.' ]</small></small>' );
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ($edit) {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		} else {
			JToolBarHelper::cancel();
		}
		JToolBarHelper::help( 'screen.newsfeeds.edit' );

		$newsfeed	=& $this->get('data');
		$isNew		= ($newsfeed->id < 1);

		// fail if checked out not by 'me'
		if ($model->isCheckedOut( $user->get('id') )) {
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'The newsfeed' ), $newsfeed->name );
			$mainframe->redirect( 'index.php?option='. $option, $msg );
		}

		if (!$isNew) {
			// do stuff for existing records
			$model->checkout( $user->get('id') );
		} else {
			// do stuff for new records
			$newsfeed->ordering 		= 0;
			$newsfeed->numarticles 	= 5;
			$newsfeed->cache_time 	= 3600;
			$newsfeed->published 	= 1;
			$newsfeed->catid 	= JRequest::getVar( 'catid', 0, 'post', 'int' );
		}

		// build the html select list for ordering
		$order_query = 'SELECT a.ordering AS value, a.name AS text'
			. ' FROM #__newsfeeds AS a'
			. ' WHERE catid = ' . (int) $newsfeed->catid
			. ' ORDER BY a.ordering'
		;

		$this->assignRef('user',		$user);
		$this->assignRef('order_query',	$order_query);
		$this->assignRef('newsfeed',	$newsfeed);

		parent::display($tpl);
	}
}