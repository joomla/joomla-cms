<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Content
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * HTML View class for the Articles component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @since 1.0
 */
class ContentViewCopyselect extends JView
{
	function display($tpl = null)
	{
		// Initialize variables
		$db			= & JFactory::getDBO();

		$cid		= JRequest::getVar('cid', array(), 'post', 'array');
		$option		= JRequest::getCmd('option');
		$task		= JRequest::getCmd('task');

		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			$msg = JText::_('Select an item to '.($task=='copy' ? 'copy' : 'move'));
			$mainframe->redirect('index.php?option='.$option, $msg, 'error');
		}

		//seperate contentids
		$cids = implode(',', $cid);
		## Articles query
		$query = 'SELECT a.title' .
				' FROM #__content AS a' .
				' WHERE (a.id IN ('. $cids .'))' .
				' ORDER BY a.title';
		$db->setQuery($query);
		$items = $db->loadObjectList();

		## Section & Category query
		$query = 'SELECT c.id AS `value`, c.title AS `text`' .
				' FROM #__categories AS c ' .
				' WHERE c.extension = "com_content"' .
				' ORDER BY c.title';
		$db->setQuery($query);

		// Add a row for uncategorized content
		$uncat	= JHtml::_('select.option', '0,0', JText::_('UNCATEGORIZED'));
		$rows	= $db->loadObjectList();
		array_unshift($rows, $uncat);
		// build the html select list
		$CatList = JHtml::_(
			'select.genericlist',
			$rows,
			'cat',
			array('list.attr' => 'class="inputbox" size="10"')
		);

		$this->assignRef('option',		$option);
		$this->assignRef('cid',			$cid);
		$this->assignRef('sectCatList',	$CatList);
		$this->assignRef('items',		$items);

		// Render article preview
		parent::display($tpl);
	}
}