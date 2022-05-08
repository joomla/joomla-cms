<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Component Controller
 *
 * @since  1.5
 */
class ConfigController extends BaseController
{
	/**
	 * @param   array                         $config   An optional associative array of configuration settings.
	 *                                                  Recognized key values include 'name', 'default_task', 'model_path', and
	 *                                                  'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface|null      $factory  The factory.
	 * @param   CMSApplication|null           $app      The JApplication for the dispatcher
	 * @param   \Joomla\CMS\Input\Input|null  $input    The Input object for the request
	 *
	 * @since   1.6
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$this->registerTask('apply', 'save');
	}

	/**
	 * Method to handle cancel
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function cancel()
	{
		// Redirect back to home(base) page
		$this->setRedirect(Uri::base());
	}

	/**
	 * Method to save global configuration.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function save()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Check if the user is authorized to do this.
		if (!$this->app->getIdentity()->authorise('core.admin'))
		{
			$this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'));
			$this->app->redirect('index.php');
		}

		// Set FTP credentials, if given.
		ClientHelper::setCredentialsFromRequest('ftp');

		$model = $this->getModel();

		$form  = $model->getForm();
		$data  = $this->app->input->post->get('jform', array(), 'array');

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
			$this->app->redirect(Route::_('index.php?option=com_config&view=config', false));
		}

		// Attempt to save the configuration.
		$data = $return;

		// Access backend com_config
		$saveClass = $this->factory->createController('Application', 'Administrator', [], $this->app, $this->input);

		// Get a document object
		$document = $this->app->getDocument();

		// Set backend required params
		$document->setType('json');

		// Execute backend controller
		$return = $saveClass->save();

		// Reset params back after requesting from service
		$document->setType('html');

		// Check the return value.
		if ($return === false)
		{
			/*
			 * The save method enqueued all messages for us, so we just need to redirect back.
			 */

			// Save the data in the session.
			$this->app->setUserState('com_config.config.global.data', $data);

			// Save failed, go back to the screen and display a notice.
			$this->app->redirect(Route::_('index.php?option=com_config&view=config', false));
		}

		// Redirect back to com_config display
		$this->app->enqueueMessage(Text::_('COM_CONFIG_SAVE_SUCCESS'));
		$this->app->redirect(Route::_('index.php?option=com_config&view=config', false));

		return true;
	}
}
