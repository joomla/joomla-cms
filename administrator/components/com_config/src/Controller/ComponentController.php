<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;

/**
 * Note: this view is intended only to be opened in a popup
 *
 * @since  1.5
 */
class ComponentController extends BaseController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		// Map the apply task to the save method.
		$this->registerTask('apply', 'save');
	}

	/**
	 * Method to save global configuration.
	 *
	 * @return  mixed  Calls $app->redirect()
	 *
	 * @since   3.2
	 */
	public function save()
	{
		// Check for request forgeries.
		if (!Session::checkToken())
		{
			$this->setRedirect(Route::_('index.php'), Text::_('JINVALID_TOKEN'), 'error');
		}

		// Set FTP credentials, if given.
		ClientHelper::setCredentialsFromRequest('ftp');

		/** @var \Joomla\Component\Config\Administrator\Model\ComponentModel $model */
		$model = $this->getModel('Component', 'Administrator');
		$form   = $model->getForm();
		$data   = $this->input->get('jform', array(), 'array');
		$id     = $this->input->getInt('id');
		$option = $this->input->get('component');
		$user   = $this->app->getIdentity();

		// Make sure com_joomlaupdate and com_privacy can only be accessed by SuperUser
		if (in_array(strtolower($option), ['com_joomlaupdate', 'com_privacy'])
			&& !$user->authorise('core.admin'))
		{
			$this->setRedirect(Route::_('index.php'), Text::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		// Check if the user is authorised to do this.
		if (!$user->authorise('core.admin', $option) && !$user->authorise('core.options', $option))
		{
			$this->setRedirect(Route::_('index.php'), Text::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		// Remove the permissions rules data if user isn't allowed to edit them.
		if (!$user->authorise('core.admin', $option) && isset($data['params']) && isset($data['params']['rules']))
		{
			unset($data['params']['rules']);
		}

		$returnUri = $this->input->post->get('return', null, 'base64');

		$redirect = '';

		if (!empty($returnUri))
		{
			$redirect = '&return=' . urlencode($returnUri);
		}

		// Validate the posted data.
		$return = $model->validate($form, $data);

		// Check for validation errors.
		if ($return === false)
		{
			/*
			 * The validate method enqueued all messages for us, so we just need to redirect back.
			 */

			// Save the data in the session.
			$this->app->setUserState('com_config.config.global.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(Route::_('index.php?option=com_config&view=component&component=' . $option . $redirect, false));
		}

		// Attempt to save the configuration.
		$data = array(
			'params' => $return,
			'id'     => $id,
			'option' => $option
		);

		try
		{
			$model->save($data);
		}
		catch (\RuntimeException $e)
		{
			// Save the data in the session.
			$this->app->setUserState('com_config.config.global.data', $data);

			// Save failed, go back to the screen and display a notice.
			$this->setRedirect(
				Route::_('index.php?option=com_config&view=component&component=' . $option . $redirect),
				Text::_('JERROR_SAVE_FAILED', $e->getMessage()),
				'error'
			);
		}

		// Set the redirect based on the task.
		switch ($this->input->getCmd('task'))
		{
			case 'apply':
				$this->app->enqueueMessage(Text::_('COM_CONFIG_SAVE_SUCCESS'), 'message');
				$this->app->redirect(Route::_('index.php?option=com_config&view=component&component=' . $option . $redirect, false));

				break;

			case 'save':
				$this->app->enqueueMessage(Text::_('COM_CONFIG_SAVE_SUCCESS'), 'message');
			default:
				$redirect = 'index.php?option=' . $option;

				if (!empty($returnUri))
				{
					$redirect = base64_decode($returnUri);
				}

				// Don't redirect to an external URL.
				if (!Uri::isInternal($redirect))
				{
					$redirect = Uri::base();
				}

				$this->setRedirect(Route::_($redirect, false));

				break;
		}

		return true;
	}

	/**
	 * Method to cancel global configuration component.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function cancel()
	{
		$component = $this->input->getCmd('component');

		$this->setRedirect('index.php?option=' . $component);
	}
}
