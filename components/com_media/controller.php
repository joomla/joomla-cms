<?php
/**
 * @version		$Id: media.php 8660 2007-08-30 23:53:21Z louis $
 * @package		Joomla
 * @subpackage	Massmail
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.controller' );

/**
 * Media Manager Component Controller
 *
 * @package		Joomla
 * @subpackage	Media
 * @version 1.5
 */
class MediaController extends JController
{
	/**
	 * Display the view
	 */
	function display()
	{
		global $mainframe;

		$vName = JRequest::getCmd('view', 'images');
		switch ($vName)
		{
			case 'imagesList':
				$mName = 'list';
				$vLayout = JRequest::getCmd( 'layout', 'default' );

				break;

			case 'images':
			default:
				$vLayout = JRequest::getCmd( 'layout', 'default' );
				$mName = 'manager';
				$vName = 'images';

				break;
		}

		$document = &JFactory::getDocument();
		$vType		= $document->getType();

		// Get/Create the view
		$view = &$this->getView( $vName, $vType);
		$view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.strtolower($vName).DS.'tmpl');

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
