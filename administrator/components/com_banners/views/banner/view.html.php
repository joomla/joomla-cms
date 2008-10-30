<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Banners
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
* @package		Joomla
* @subpackage	Banners
*/
class BannerViewBanner extends JView
{
	function display( $tpl = null )
	{
		global $mainframe, $option;

		$db		=& JFactory::getDBO();
		$uri 	=& JFactory::getURI();
		$user 	=& JFactory::getUser();
		$model	=& $this->getModel();

		// Set toolbar items for the page
		$edit		= JRequest::getVar('edit',true);
		$text = !$edit ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Banner' ).': <small><small>[ ' . $text.' ]</small></small>', 'generic.png' );
		JToolBarHelper::save( 'save' );
		JToolBarHelper::apply('apply');
		JToolBarHelper::cancel( 'cancel' );
		JToolBarHelper::help( 'screen.banners.edit' );

		//get the banner
		$row	=& $this->get('data');

		// fail if checked out not by 'me'
		if ($model->isCheckedOut( $user->get('id') )) {
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'The banner' ), $row->name );
			$mainframe->redirect( 'index.php?option='. $option, $msg );
		}

		// Edit or Create?
		if ($edit)
		{
			$model->checkout( $user->get('id') );
		}
		else
		{
			// initialise new record
			$row->showBanner = 1;
		}

		// Build Client select list
		$sql = 'SELECT cid, name'
		. ' FROM #__bannerclient'
		;
		$db->setQuery($sql);
		if (!$db->query())
		{
			$this->setRedirect( 'index.php?option=com_banners' );
			return JError::raiseWarning( 500, $db->getErrorMsg() );
		}

		$clientlist[] = JHtml::_('select.option',  '0', '- ' . JText::_( 'Select Client' ) . ' -', 'cid', 'name' );
		$clientlist = array_merge( $clientlist, $db->loadObjectList() );
		$lists['cid'] = JHtml::_(
            'select.genericlist',
            $clientlist,
            'cid',
            'class="inputbox" size="1"',
            'cid',
            'name',
            $row->cid
        );

		// Imagelist
		$javascript = 'onchange="changeDisplayImage();"';
		$directory = '/images/banners';
		$lists['imageurl'] = JHtml::_('list.images',  'imageurl', $row->imageurl, $javascript, $directory );

		// build list of categories
		$lists['catid'] = JHtml::_('list.category',  'catid', 'com_banner', intval( $row->catid ) );

		// sticky
		$lists['sticky'] = JHtml::_('select.booleanlist',  'sticky', 'class="inputbox"', $row->sticky );

		// published
		$lists['showBanner'] = JHtml::_('select.booleanlist',  'showBanner', '', $row->showBanner );

		// build the html select list for ordering
		$order_query = 'SELECT ordering AS value, name AS text'
			. ' FROM #__banner'
			. ' WHERE catid = ' . (int) $row->catid
			. ' ORDER BY ordering';

		//clean data
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'custombannercode' );

		$this->assignRef('row', $row);
		$this->assignRef('lists', $lists);
		$this->assignRef('order_query', $order_query);

		parent::display($tpl);
	}
}