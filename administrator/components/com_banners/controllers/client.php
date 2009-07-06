<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * @package		Joomla.Administrator
 * @subpackage	Banners
 */
class BannerControllerClient extends JController
{
	/**
	 * Constructor
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
		// Register Extra tasks
		$this->registerTask('add',		'edit');
		$this->registerTask('apply',	'save');
	}

	function display()
	{
		$app	= &JFactory::getApplication();

		$db		= &JFactory::getDbo();
		$user	= &JFactory::getUser();
		$context			= 'com_banners.bannerclient.list.';
		$filter_order		= $app->getUserStateFromRequest($context.'filter_order',		'filter_order',		'a.name',	'cmd');
		$filter_order_Dir	= $app->getUserStateFromRequest($context.'filter_order_Dir',	'filter_order_Dir',	'',			'word');
		$search				= $app->getUserStateFromRequest($context.'search',			'search',			'',			'string');
		$search				= JString::strtolower($search);

		$limit		= $app->getUserStateFromRequest('global.list.limit',		'limit',		$app->getCfg('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest($context.'limitstart',	'limitstart',	0, 'int');

		$where = array();

		if ($search) {
			$where[] = 'LOWER(a.name) LIKE '.$db->Quote('%'.$db->getEscaped($search, true).'%', false);
		}

		$where		= (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
		$orderby = ' ORDER BY '. $filter_order .' '. $filter_order_Dir .', a.cid';

		// get the total number of records
		$query = 'SELECT a.*, count(b.bid) AS nbanners, u.name AS editor'
		. ' FROM #__bannerclient AS a'
		. ' LEFT JOIN #__banner AS b ON a.cid = b.cid'
		. ' LEFT JOIN #__users AS u ON u.id = a.checked_out'
		. $where
		. ' GROUP BY a.cid'
		. $orderby
		;

		$db->setQuery($query);
		$db->query();
		$total = $db->getNumRows();

		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);

		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
		$rows = $db->loadObjectList();

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search']= $search;

		require_once(JPATH_COMPONENT.DS.'views'.DS.'client.php');
		BannersViewClients::clients($rows, $pageNav, $lists);
	}

	/**
	 * Edit a banner client record
	 */
	function edit()
	{
		// Initialize variables
		$db		= &JFactory::getDbo();
		$user	= &JFactory::getUser();

		$userId	= $user->get ('id');

		if ($this->_task == 'edit') {
			$cid	= JRequest::getVar('cid', array(0), 'method', 'array');
		} else {
			$cid	= array(0);
		}

		$row = &JTable::getInstance('bannerclient', 'Table');
		$row->load((int) $cid[0]);

		// fail if checked out not by 'me'
		if ($row->isCheckedOut($userId)) {
			$this->setRedirect('index.php?option=com_banners&c=client');
			return JError::raiseWarning(JText::sprintf('WARNEDITEDBYPERSON', $row->name));
		}

		if ($row->cid) {
			// do stuff for existing record
			$row->checkout($userId);
		} else {
			// do stuff for new record
			$row->published = 0;
			$row->approved = 0;
		}

		require_once(JPATH_COMPONENT.DS.'views'.DS.'client.php');
		BannersViewClients::client($row);
	}

	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$this->setRedirect('index.php?option=com_banners&c=client');

		// Initialize variables
		$db		= &JFactory::getDbo();
		$table	= &JTable::getInstance('bannerclient', 'Table');

		if (!$table->bind(JRequest::get('post'))) {
			return JError::raiseWarning(500, $table->getError());
		}
		if (!$table->check()) {
			return JError::raiseWarning(500, $table->getError());
		}
		if (!$table->store()) {
			return JError::raiseWarning(500, $table->getError());
		}
		$table->checkin();

		switch (JRequest::getCmd('task'))
		{
			case 'apply':
				$this->setRedirect('index.php?option=com_banners&c=client&task=edit&cid[]='. $table->cid);
				break;
		}

		$this->setMessage(JText::_('Item Saved'));
	}

	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$this->setRedirect('index.php?option=com_banners&c=client');

		// Initialize variables
		$db			= &JFactory::getDbo();
		$table		= &JTable::getInstance('bannerclient', 'Table');
		$table->cid	= JRequest::getVar('cid', 0, 'post', 'int');
		$table->checkin();
	}

	function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$this->setRedirect('index.php?option=com_banners&c=client');

		// Initialize variables
		$db		= &JFactory::getDbo();
		$cid	= JRequest::getVar('cid', array(0), 'post', 'array');
		$table	= &JTable::getInstance('bannerclient', 'Table');
		$n		= count($cid);

		for ($i = 0; $i < $n; $i++)
		{
			$query = 'SELECT COUNT(bid)'
			. ' FROM #__banner'
			. ' WHERE cid = '. (int) $cid[$i]
			;
			$db->setQuery($query);
			$count = $db->loadResult();
			if ($count === null) {
				return JError::raiseWarning(500, $db->getErrorMsg());
			}
			else if ($count > 0) {
				return JError::raiseWarning(500, JText::_('WARNCANNOTDELCLIENTBANNER'));
			}
			else {
				if (!$table->delete((int) $cid[$i])) {
					return JError::raiseWarning(500, $table->getError());
				}
			}
		}

		$this->setMessage(JText::sprintf('Items removed', $n));
	}
}