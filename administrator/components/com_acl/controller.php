<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Component Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 */
class AccessController extends JController
{
	/**
	 * Temporary testing methods
	 */
	function test()
	{
		jimport('joomla.acl.acladmin');

		try
		{
			//JAclAdmin::registerSectionForActions('Foobar', 'com_foobar');
			//JAclAdmin::registerAction('com_foobar', 'Foobar Action 1', 'foobar.action1', 1);
			//JAclAdmin::registerAction('com_foobar', 'Foobar Action 2', 'foobar.action2', 2);
			//JAclAdmin::registerAction('com_foobar', 'Foobar Action 3', 'foobar.action3', 3);

			//echo '<br />Group ID = '.JAclAdmin::registerGroupForUsers('Foobar Group');

			echo '<br />Group ID = '.JAclAdmin::registerGroupForAssets('Foobar Special');

			echo 'It worked';
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
	}

	/**
	 * Default display method
	 */
	function display()
	{
		$document	= &JFactory::getDocument();

		// Set the default view name and format from the Request
		$vName		= JRequest::getWord('view', 'rules');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		if ($view = &$this->getView($vName, $vFormat))
		{
			switch ($vName)
			{
				case 'group':
					$model = $this->getModel('group');
					$model->setState('group_type',	'aro');
					break;

				case 'groups':
					$model = $this->getModel('groups');
					$model->setState('list.group_type',	'aro');
					$model->setState('list.tree',		true);
					$model->setState('list.parent_id',	28);
					break;

				case 'level':
					$model = $this->getModel('group');
					$model->setState('group_type',	'axo');
					break;

				case 'levels':
					$model = $this->getModel('groups');
					$model->setState('list.group_type',	'axo');
					$model->setState('list.tree',		false);
					$model->setState('list.parent_id',	1);
					break;

				case 'rule':
					$model = $this->getModel('acl');
					break;

				case 'rules':
				default:
					$model = $this->getModel('acls');
					break;
			}

			// Push the model into the view (as default)
			$view->setModel($model, true);
			$view->setLayout($lName);
			$view->assignRef('document', $document);
			$view->display();
		}

		// Set up the Linkbar
		JSubMenuHelper::addEntry(JText::_('ACL Link Rules'),			'index.php?option=com_acl&view=rules',	$vName == 'rules');
		JSubMenuHelper::addEntry(JText::_('ACL Link User Groups'),		'index.php?option=com_acl&view=groups',	$vName == 'groups');
		JSubMenuHelper::addEntry(JText::_('ACL Link Access Levels'),	'index.php?option=com_acl&view=levels',	$vName == 'levels');
	}
}