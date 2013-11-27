<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Cancel Controller for global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.2
 */
class JControllerCancel extends JControllerCmsbase
{
	/**
	 * Generic method to cancel
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Get the application
		$app = $this->getApplication();

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit'))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'));
			$this->app->redirect('index.php');
		}

		$optionName     = $this->input->getWord('option', 'com_content');
		$this->viewName     = $this->input->getWord('view');

		$this->context = $optionName . ' . ' . $this->viewName;

		$modelName = $this->prefix . 'Model' . ucfirst($this->viewName);
		$model = new $modelName ;


		$this->context = $optionName . $this->viewName;

		// By default cancel goes to the default component view.
		$this->app->redirect(JRoute::_('index.php?option=' . $optionName, false));

	}
}
