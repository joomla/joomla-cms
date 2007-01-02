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

jimport('joomla.application.component.view');

/**
 * @package Joomla
 * @subpackage Contacts
 */
class ContactViewCategory extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $Itemid, $option;

		$user	 = &JFactory::getUser();
		$pathway = & $mainframe->getPathWay();
		$model	 = &$this->getModel();

		// Get the paramaters of the active menu item
		$menu	=& JSiteHelper::getActiveMenuItem();
		$params	=& JSiteHelper::getMenuParams();

		// Selected Request vars
		$categoryId			= JRequest::getVar( 'catid', 0, '', 'int' );
		$limit				= JRequest::getVar('limit', $mainframe->getCfg('list_limit'), '', 'int');
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
		$options['gid'] 		= $user->get('gid');
		$options['category_id']	= $categoryId;
		$options['limit']		= $limit;
		$options['limitstart']	= $limitstart;
		$options['order by']	= "$filter_order $filter_order_Dir, cd.ordering";

		$categories	= $model->getCategories( $options );
		$contacts	= $model->getContacts( $options );
		$total 		= $model->getContactCount( $options );

		//prepare contacts
		$k = 0;
		for($i = 0; $i <  count( $contacts ); $i++)
		{
			$contact =& $contacts[$i];

			$contact->link	= sefRelToAbs('index.php?option=com_contact&amp;view=contact&amp;id='.$contact->id.'&amp;Itemid='.$Itemid);

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
			$category = JTable::getInstance( 'category' );
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

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		$this->assignRef('items',		$contacts);
		$this->assignRef('lists',		$lists);
		$this->assignRef('pagination',	$pagination);
		//$this->assignRef('data',		$data);
		$this->assignRef('category',	$category);
		$this->assignRef('params',		$params);

		parent::display($tpl);
	}

	function getItems()
	{

	}
}
?>