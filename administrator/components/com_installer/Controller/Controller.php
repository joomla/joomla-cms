<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Installer\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Controller\Controller as BaseController;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper;

/**
 * Installer Controller
 *
 * @since  1.5
 */
class Controller extends BaseController
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static   This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Get the document object.
		$document = \JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName   = $this->input->get('view', 'install');
		$vFormat = $document->getType();
		$lName   = $this->input->get('layout', 'default', 'string');
		$id      = $this->input->getInt('update_site_id');

		// Check for edit form.
		if ($vName == 'updatesite' && $lName == 'edit' && !$this->checkEditId('com_installer.edit.updatesite', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setMessage(\JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(\JRoute::_('index.php?option=com_installer&view=updatesites', false));

			return false;
		}

		// Get and render the view.
		if ($view = $this->getView($vName, $vFormat))
		{
			$ftp = \JClientHelper::setCredentialsFromRequest('ftp');
			$view->ftp = &$ftp;

			// Get the model for the view.
			$model = $this->getModel($vName);

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->document = $document;

			// Load the submenu.
			InstallerHelper::addSubmenu($vName);
			$view->display();
		}

		return $this;
	}
}
