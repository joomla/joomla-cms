<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Base Display Controller
 *
 * @since  3.2
 */
class ConfigControllerCmsbase extends JControllerBase
{
	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $prefix;

	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Check for request forgeries
		if (!JSession::checkToken())
		{
			$this->app->enqueueMessage(JText::_('JINVALID_TOKEN_NOTICE'));
			$this->app->redirect('index.php');
		}

		// Get the application
		$this->app = $this->getApplication();
		$this->app->redirect('index.php?option=' . $this->input->get('option'));

		$this->componentFolder = $this->input->getWord('option', 'com_content');
		$this->viewName        = $this->input->getWord('view');

		return $this;
	}
}
