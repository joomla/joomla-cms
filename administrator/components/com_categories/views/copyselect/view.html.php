<?php
/**
* @version		$Id: $
* @package		Joomla
* @subpackage	Categories
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Categories component
 *
 * @static
 * @package		Joomla
 * @subpackage	Categories
 * @since 1.0
 */
class CategoriesViewCopySelect extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$db =& JFactory::getDBO();
		$sectionOld = JRequest::getCmd( 'section', 'com_content', 'post' );
		$redirect = $sectionOld;
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to copy', true ));
		}

		## query to list selected categories
		$cids = implode( ',', $cid );
		$query = 'SELECT a.title, a.section'
		. ' FROM #__categories AS a'
		. ' WHERE a.id IN ( '.$cids.' )'
		;
		$db->setQuery( $query );
		$items = $db->loadObjectList();

		## query to list items from categories
		$query = 'SELECT a.title, a.id'
		. ' FROM #__content AS a'
		. ' WHERE a.catid IN ( '.$cids.' )'
		. ' ORDER BY a.catid, a.title'
		;
		$db->setQuery( $query );
		$contents = $db->loadObjectList();

		## query to choose section to move to
		$query = 'SELECT a.title AS `text`, a.id AS `value`'
		. ' FROM #__sections AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.name'
		;
		$db->setQuery( $query );
		$sections = $db->loadObjectList();

		// build the html select list
		$lists['SectionList'] = JHTML::_('select.genericlist',   $sections, 'sectionmove', 'class="inputbox" size="10"', 'value', 'text', null );

		$this->assignRef('lists',		$lists);
		$this->assignRef('redirect',	$redirect);
		$this->assignRef('sectionOld',	$sectionOld);
		$this->assignRef('items',		$items);
		$this->assignRef('contents',	$contents);
		$this->assignRef('cid',			$cid);

		parent::display($tpl);
	}
}