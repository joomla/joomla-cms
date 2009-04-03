<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Cache Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	Cache
 * @since 1.6
 */
class CacheController extends JController
{
	public function display()
	{
		// Get the document object.
		$document	= &JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'cache');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		// Get and render the view.
		if ($view = &$this->getView($vName, $vFormat))
		{
			switch ($vName)
			{
				case 'purge':
					break;
				case 'cache':
				default:
					$model = &$this->getModel($vName);
					$view->setModel($model, true);
					break;
			}

			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
	}
	
	public function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid = JRequest::getVar('cid', array(), 'post', 'array');

		$model = $this->getModel('cache');
		$model->cleanlist($cid);

		$this->setRedirect('index.php?option=com_cache');
	}
	
	public function purge()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('cache');
		$ret = $model->clean();

		$msg = JText::_('Expired items have been purged');
		$msgType = 'message';

		if ($ret === false) {
			$msg = JText::_('Error puring expired items');
			$msgType = 'error';
		}

		$this->setRedirect('index.php?option=com_cache&view=purge', $msg, $msgType);
	}
}
