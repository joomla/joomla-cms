<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Contact
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('joomla.application.view');

/**
 * @pacakge Joomla
 * @subpackage Contacts
 */
class ContactViewCategory extends JView
{
	/**
	 * Name of the view.
	 * 
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Category';

	function display()
	{
		$document	= & JFactory::getDocument();
		switch ($document->getType())
		{
			case 'feed':
				$this->_displayFeed();
				break;
			default:
				$this->_displayHTML();
				break;
		}
	}
	
	function items()
	{
		global $mainframe, $Itemid;
		
		$k = 0;
		for($i = 0; $i <  count($this->items); $i++)
		{
			$item =& $this->items[$i];

			$item->link =  sefRelToAbs('index.php?option=com_contact&amp;view=contact&amp;contact_id='. $item->id .'&amp;Itemid='. $Itemid);

			if ( $item->email_to ) {
				$item->email_to = mosHTML::emailCloaking( $item->email_to, 1 );
			}
			
			$item->odd   = $k;
			$item->count = $i;
			$k = 1 - $k;
		}
		
		$this->_loadTemplate('table_items');
	}

	function _displayHTML()
	{
		global $mainframe, $Itemid, $option;

		$user	 = &JFactory::getUser();
		$pathway = & $mainframe->getPathWay();
		$model	 = &$this->getModel();

		// Get the paramaters of the active menu item
		$menus   =& JMenu::getInstance();
		$menu    = $menus->getItem($Itemid);
		$params  =& $menus->getParams($Itemid);

		// Selected Request vars
		$categoryId			= JRequest::getVar( 'catid', $params->get('category_id', 0 ), '', 'int' );
		$limit				= JRequest::getVar('limit', $params->get('display_num'), '', 'int');
		$limitstart			= JRequest::getVar('limitstart', 0, '', 'int');
		$filter_order		= JRequest::getVar('filter_order', 		'cd.ordering');
		$filter_order_Dir	= JRequest::getVar('filter_order_Dir', 	'ASC');
		
		// Set some defaults against system variables
		$params->def('header', 				$menu->name);
		$params->def('headings', 			1);
		$params->def('position', 			1);
		$params->def('email', 				1);
		$params->def('telephone', 			1);
		$params->def('fax', 				1);
		$params->def('page_title',			1);
		$params->def('back_button', 		$mainframe->getCfg('back_button'));
		$params->def('description_text', 	JText::_('The Contact list for this Website.'));
		$params->def('image_align', 		'right');
		$params->def('display_num', 		$limit);

		// query options
		$pptions['gid'] 		= $user->get('gid');
		$options['category_id']	= $categoryId;
		$options['limit']		= $limit;
		$options['limitstart']	= $limitstart;
		$options['order by']	= "$filter_order $filter_order_Dir, cd.ordering";

		$categories   = $model->getCategories( $options );
		$contacts     = $model->getContacts( $options );
		$total 		  = $model->getContactCount( $options );

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
			$category = JTable::getInstance( 'category', $db );
		}

		// Set the page title and pathway
		if ($category->name) 
		{
			// Add the category breadcrumbs item
			$pathway->addItem($category->name, '');
			$mainframe->setPageTitle(JText::_('Contact').' - '.$category->name);
		} else {
			$mainframe->SetPageTitle(JText::_('Contact'));
		}

		// table ordering
		if ( $filter_order_Dir == 'DESC' ) {
			$lists['order_Dir'] = 'ASC';
		} else {
			$lists['order_Dir'] = 'DESC';
		}
		$lists['order'] = $filter_order;
		$selected = '';
		
		jimport('joomla.presentation.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);
		$link 		= "index.php?option=com_contact&amp;catid=$categoryId&amp;Itemid=$Itemid";
		
		$data = new stdClass();
		$data->link = $link;
			
		$this->set('items'     , $contacts);
		$this->set('lists'     , $lists);
		$this->set('pagination', $pagination);
		$this->set('data'      , $data);
		$this->set('category'  , $category);
		$this->set('params'    , $params);

		$this->_loadTemplate('table');
	}

	function _displayFeed()
	{
		global $mainframe, $Itemid;

		$db		  =& JFactory::getDBO();
		$document =& JFactory::getDocument();

		$limit 			= JRequest::getVar('limit', 0, '', 'int');
		$limitstart 	= JRequest::getVar('limitstart', 0, '', 'int');
		$catid  		= JRequest::getVar('catid', 0);

		$where  = "\n WHERE a.published = 1";

		if ( $catid ) {
			$where .= "\n AND a.catid = $catid";
		}

    	$query = "SELECT"
    	. "\n a.name AS title,"
    	. "\n CONCAT( '$link', a.catid, '&id=', a.id ) AS link,"
    	. "\n CONCAT( a.con_position, ' - ',a.misc ) AS description,"
    	. "\n '' AS date,"
		. "\n c.title AS category,"
    	. "\n a.id AS id"
    	. "\n FROM #__contact_details AS a"
		. "\n LEFT JOIN #__categories AS c ON c.id = a.catid"
    	. $where
    	. "\n ORDER BY a.catid, a.ordering"
    	;
		$db->setQuery( $query, 0, $limit );
    	$rows = $db->loadObjectList();

		foreach ( $rows as $row )
		{
			// strip html from feed item title
			$title = htmlspecialchars( $row->title );
			$title = html_entity_decode( $title );

			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			$link = 'index.php?option=com_contact&task=view&id='. $row->id . '&catid='.$row->catid. '&Itemid='. $Itemid;;
			$link = sefRelToAbs( $link );

			// strip html from feed item description text
			$description = $row->description;
			$date = ( $row->date ? date( 'r', $row->date ) : '' );

			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $title;
			$item->link 		= $link;
			$item->description 	= $description;
			$item->date			= $date;
			$item->category   	= $row->category;

			// loads item info into rss array
			$document->addItem( $item );
		}
	}
}
?>