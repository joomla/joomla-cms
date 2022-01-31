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
class TemplatesController extends BaseController
{
	/**
	 * @param   array                         $config   An optional associative array of configuration settings.
	 *                                                  Recognized key values include 'name', 'default_task', 'model_path', and
	 *                                                  'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface|null      $factory  The factory.
	 * @param   CMSApplication|null           $app      The Application for the dispatcher
	 * @param   \Joomla\CMS\Input\Input|null  $input    The Input object for the request
	 *
	 * @since   1.6
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		// Apply, Save & New, and Save As copy should be standard on forms.
		$this->registerTask('apply', 'save');
	}

	/**
	 * Method to handle cancel
	 *
	 * @return  boolean  True on success.
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
			$this->setRedirect('index.php', Text::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		// Set FTP credentials, if given.
		ClientHelper::setCredentialsFromRequest('ftp');

		$app = $this->app;

		// Access backend com_templates
		$controllerClass = $app->bootComponent('com_templates')
			->getMVCFactory()->createController('Style', 'Administrator', [], $app, $app->input);

		// Get a document object
		$document = $app->getDocument();

		// Set backend required params
		$document->setType('json');
		$this->input->set('id', $app->getTemplate(true)->id);

		// Execute backend controller
		$return = $controllerClass->save();

		// Reset params back after requesting from service
		$document->setType('html');

		// Check the return value.
		if ($return === false)
		{
			// Save failed, go back to the screen and display a notice.
			$this->setMessage(Text::sprintf('JERROR_SAVE_FAILED'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_config&view=templates', false));

			return false;
		}

		// Set the success message.
		$this->setMessage(Text::_('COM_CONFIG_SAVE_SUCCESS'));

		// Redirect back to com_config display
		$this->setRedirect(Route::_('index.php?option=com_config&view=templates', false));

		return true;
	}
}
