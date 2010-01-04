<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Media Manager Component Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @version 1.5
 */
class MediaController extends JController
{
	/**
	 * Display the view
	 */
	function display()
	{
		$vName = JRequest::getCmd('view', 'media');
		switch ($vName)
		{
			case 'images':
				$vLayout = JRequest::getCmd('layout', 'default');
				$mName = 'manager';

				break;

			case 'imagesList':
				$mName = 'list';
				$vLayout = JRequest::getCmd('layout', 'default');

				break;

			case 'mediaList':
				$app	= &JFactory::getApplication();
				$mName = 'list';
				$vLayout = $app->getUserStateFromRequest('media.list.layout', 'layout', 'thumbs', 'word');

				break;

			case 'media':
			default:
				$vName = 'media';
				$vLayout = JRequest::getCmd('layout', 'default');
				$mName = 'manager';
				break;
		}

		$document = &JFactory::getDocument();
		$vType		= $document->getType();

		// Get/Create the view
		$view = &$this->getView($vName, $vType);

		// Get/Create the model
		if ($model = &$this->getModel($mName)) {
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout($vLayout);

		// Display the view
		$view->display();

	}

	function ftpValidate()
	{
		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
	}
}
