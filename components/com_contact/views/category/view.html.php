<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * @package		Joomla
 * @subpackage	Contacts
 */
class ContactViewCategory extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		$user	  = &JFactory::getUser();
		$uri 	  =& JFactory::getURI();
		$model	  = &$this->getModel();
		$document =& JFactory::getDocument();

		// Get the parameters of the active menu item
		$menus	=& JMenu::getInstance();
		$menu	= $menus->getActive();

		$pparams = &$mainframe->getPageParameters('com_contact');

		// Selected Request vars
		$categoryId			= JRequest::getVar('catid',				0,				'', 'int');
		$limit				= JRequest::getVar('limit',				$mainframe->getCfg('list_limit'),	'', 'int');
		$limitstart			= JRequest::getVar('limitstart',		0,				'', 'int');
		$filter_order		= JRequest::getVar('filter_order',		'cd.ordering',	'', 'cmd');
		$filter_order_Dir	= JRequest::getVar('filter_order_Dir',	'ASC',			'', 'word');

		// Set some defaults against system variables
		$pparams->def('page_title',	$menu->name);

		// query options
		$options['aid'] 		= $user->get('aid', 0);
		$options['category_id']	= $categoryId;
		$options['limit']		= $limit;
		$options['limitstart']	= $limitstart;
		$options['order by']	= "$filter_order $filter_order_Dir, cd.ordering";

		$categories	= $model->getCategories( $options );
		$contacts	= $model->getContacts( $options );
		$total 		= $model->getContactCount( $options );

		//add alternate feed link
		if($pparams->get('show_feed_link', 1) == 1)
		{
			$link	= 'index.php?option=com_contact&view=category&format=feed&id='.$categoryId;
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}

		//prepare contacts
		$k = 0;
		for($i = 0; $i <  count( $contacts ); $i++)
		{
			$contact =& $contacts[$i];

			$contact->link	   = JRoute::_('index.php?option=com_contact&view=contact&id='.$contact->slug);
			$contact->email_to = JHTML::_('email.cloak', $contact->email_to);

			$contact->odd	= $k;
			$contact->count = $i;
			$k = 1 - $k;
		}

		// find current category
		// TODO: Move to model
		$category = null;
		foreach ($categories as $i => $_cat)
		{
			if ($_cat->id == $categoryId) {
				$category = &$categories[$i];
				break;
			}
		}
		if ($category == null) {
			$db = &JFactory::getDBO();
			$category =& JTable::getInstance( 'category' );
		}

		// Set the page title and pathway
		if ($category->title)
		{
			// Add the category breadcrumbs item
			$document->setTitle(JText::_('Contact').' - '.$category->title);
		} else {
			$document->setTitle(JText::_('Contact'));
		}

		// table ordering
		if ( $filter_order_Dir == 'DESC' ) {
			$lists['order_Dir'] = 'ASC';
		} else {
			$lists['order_Dir'] = 'DESC';
		}
		$lists['order'] = $filter_order;
		$selected = '';

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		$this->assignRef('items',		$contacts);
		$this->assignRef('lists',		$lists);
		$this->assignRef('pagination',	$pagination);
		//$this->assignRef('data',		$data);
		$this->assignRef('category',	$category);
		$this->assignRef('params',		$pparams);
		$this->assignRef('action',		$uri->toString());

		parent::display($tpl);
	}

	function getItems()
	{

	}
}
?>
