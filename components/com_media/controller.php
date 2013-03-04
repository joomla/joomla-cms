<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_media
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media Manager Component Controller
 *
 * @package		Joomla.Site
 * @subpackage	com_media
 * @version 1.5
 */
class MediaController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		JPluginHelper::importPlugin('content');
		$vName = JRequest::getCmd('view', 'images');

		switch ($vName)
		{
			case 'imagesList':
				$mName = 'list';
				$vLayout = JRequest::getCmd('layout', 'default');

				break;

			case 'images':
			default:
				$vLayout = JRequest::getCmd('layout', 'default');
				$mName = 'manager';
				$vName = 'images';

				break;
		}

		$document = JFactory::getDocument();
		$vType		= $document->getType();

		// Get/Create the view
		$view = $this->getView($vName, $vType);
		$view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR.'/views/'.strtolower($vName).'/tmpl');

		// Get/Create the model
		if ($model = $this->getModel($mName)) {
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout($vLayout);

		// Display the view
		$view->display();

		return $this;
	}

	function ftpValidate()
	{
		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');
	}
}
