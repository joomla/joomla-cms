<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Tracks list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @since		1.6
 */
class BannersControllerTracks extends JController
{
	protected $_context = 'com_banners.tracks';
	/**
	 * Proxy for getModel.
	 */
	public function &getModel($name = 'Tracks', $prefix = 'BannersModel')
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Method to remove a record.
	 */
	public function delete()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Get the model.
		$model = $this->getModel();

		// Load the filter state.
		$app = &JFactory::getApplication();

		$type = $app->getUserState($this->_context.'.filter.type');
		$model->setState('filter.type', $type);

		$begin = $app->getUserState($this->_context.'.filter.begin');
		$model->setState('filter.begin', $begin);

		$end = $app->getUserState($this->_context.'.filter.end');
		$model->setState('filter.end', $end);

		$categoryId = $app->getUserState($this->_context.'.filter.category_id');
		$model->setState('filter.category_id', $categoryId);

		$clientId = $app->getUserState($this->_context.'.filter.client_id');
		$model->setState('filter.client_id', $clientId);

		$model->setState('list.limit', 0);
		$model->setState('list.start', 0);

		$count = $model->getTotal();
		// Remove the items.
		if (!$model->delete()) {
			JError::raiseWarning(500, $model->getError());
		}
		else {
			$this->setMessage(JText::sprintf('JController_N_Items_deleted', $count));
		}

		$this->setRedirect('index.php?option=com_banners&view=tracks');
	}
	public function display()
	{
		// Get the document object.
		$document	= &JFactory::getDocument();
		$vName		= 'tracks';
		$vFormat	= 'raw';

		// Get and render the view.
		if ($view = &$this->getView($vName, $vFormat))
		{
			// Get the model for the view.
			$model = &$this->getModel($vName);

			// Load the filter state.
			$app = &JFactory::getApplication();

			$type = $app->getUserState($this->_context.'.filter.type');
			$model->setState('filter.type', $type);

			$begin = $app->getUserState($this->_context.'.filter.begin');
			$model->setState('filter.begin', $begin);

			$end = $app->getUserState($this->_context.'.filter.end');
			$model->setState('filter.end', $end);

			$categoryId = $app->getUserState($this->_context.'.filter.category_id');
			$model->setState('filter.category_id', $categoryId);

			$clientId = $app->getUserState($this->_context.'.filter.client_id');
			$model->setState('filter.client_id', $clientId);

			$model->setState('list.limit', 0);
			$model->setState('list.start', 0);

			$form = JRequest::getVar('jform');
			$model->setState('basename',$form['basename']);
			$model->setState('compressed',$form['compressed']);

			$config =& JFactory::getConfig();
			$cookie_domain = $config->getValue('config.cookie_domain', '');
			$cookie_path = $config->getValue('config.cookie_path', '/');
			jimport('joomla.utilities.utility');
			setcookie(JUtility::getHash($this->_context.'.basename'), $form['basename'], time() + 365 * 86400, $cookie_path, $cookie_domain);
			setcookie(JUtility::getHash($this->_context.'.compressed'), $form['compressed'], time() + 365 * 86400, $cookie_path, $cookie_domain);

			// Push the model into the view (as default).
			$view->setModel($model, true);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
	}
}
