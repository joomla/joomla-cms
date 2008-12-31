<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Component Controller
 *
 * @package		Users
 * @subpackage	com_user
 */
class UserController extends JController
{
	function display()
	{
		$document	= &JFactory::getDocument();

		// Set the default view name and format from the Request
		$vName		= JRequest::getWord('view', 'users');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		if ($view = &$this->getView($vName, $vFormat))
		{
			switch ($vName)
			{
				case 'user':
				case 'users':
					$model = $this->getModel($vName);
					break;
			}

			// Push the model into the view (as default)
			$view->setModel($model, true);
			$view->setLayout($lName);
			$view->assignRef('document', $document);

			JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
			$view->display();
		}
	}

	/**
	 * @todo What's this for??
	 */
	function contact()
	{
		$contact_id = JRequest::getVar('contact_id', '', 'post', 'int');
		$this->setRedirect('index.php?option=com_contact&atask=edit&cid[]='. $contact_id);
	}
}
