<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Sections
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Sections component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Sections
 * @since 1.0
 */
class SectionsViewCopySelect extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		$db =& JFactory::getDBO();

		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
		JArrayHelper::toInteger($cid);

		if ( count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to move', true ) );
		}

		## query to list selected sections
		$cids = implode( ',', $cid );
		$query = 'SELECT a.title, a.id'
		. ' FROM #__sections AS a'
		. ' WHERE a.id IN ( '.$cids.' )'
		;
		$db->setQuery( $query );
		$sections = $db->loadObjectList();

		## query to list selected categories
		$cids = implode( ',', $cid );
		$query = 'SELECT a.title, a.id'
		. ' FROM #__categories AS a'
		. ' WHERE a.section IN ( '.$cids.' )'
		;
		$db->setQuery( $query );
		$categories = $db->loadObjectList();

		## query to list items from categories
		$query = 'SELECT a.title, a.id'
		. ' FROM #__content AS a'
		. ' WHERE a.sectionid IN ( '.$cids.' )'
		. ' ORDER BY a.sectionid, a.catid, a.title'
		;
		$db->setQuery( $query );
		$contents = $db->loadObjectList();

		$this->assignRef('sections',	$sections);
		$this->assignRef('categories',	$categories);
		$this->assignRef('contents',	$contents);
		$this->assignRef('cid',			$cid);

		parent::display($tpl);
	}
}