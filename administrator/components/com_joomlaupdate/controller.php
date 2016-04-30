<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! Update Controller
 *
 * @since  2.5.4
 */
class JoomlaupdateController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 *
	 * @since	2.5.4
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Get the document object.
		$document = JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName   = $this->input->get('view', 'default');
		$vFormat = $document->getType();
		$lName   = $this->input->get('layout', 'default', 'string');

		// Get and render the view.
		if ($view = $this->getView($vName, $vFormat))
		{
			$ftp = JClientHelper::setCredentialsFromRequest('ftp');
			$view->ftp = &$ftp;

			// Get the model for the view.
			/** @var JoomlaupdateModelDefault $model */
			$model = $this->getModel('default');

			// Push the Installer Warnings model into the view, if we can load it
			if (!class_exists('InstallerModelWarnings'))
			{
				@include_once JPATH_ADMINISTRATOR . '/components/com_installer/models/warnings.php';
			}

			$warningsModel = $this->getModel('warnings', 'InstallerModel');

			if (is_object($warningsModel))
			{
				$view->setModel($warningsModel, false);
			}

			// Perform update source preference check and refresh update information.
			$model->applyUpdateSite();
			$model->refreshUpdates();

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->document = $document;
			$view->display();
		}

		return $this;
	}
}
