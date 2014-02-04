<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Cancel Controller for Admin
 *
 * @package     Joomla.Libraries
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigControllerCanceladmin extends ConfigControllerCancel
{
	/**
	 * The context for storing internal data, e.g. record.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $context;

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $option;

	/**
	 * URL for redirection.
	 *
	 * @var    string
	 * @since  3.2
	 * @note   Replaces _redirect.
	 */
	protected $redirect;

	/**
	 * Method to handle admin cancel
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Check for request forgeries.
		if (!JSession::checkToken())
		{
			$this->app->enqueueMessage(JText::_('JINVALID_TOKEN'));
			$this->app->redirect('index.php');
		}

		if (empty($this->context))
		{
			$this->context = $this->option . '.edit' . $this->context;
		}

		// Redirect.
		$this->app->setUserState($this->context . '.data', null);

		if (!empty($this->redirect))
		{
			$this->app->redirect($this->redirect);
		}
		else
		{
			parent::execute();
		}
	}
}
