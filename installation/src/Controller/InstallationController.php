<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Utilities\ArrayHelper;

/**
 * Default controller class for the Joomla Installer.
 *
 * @since  3.1
 */
class InstallationController extends JSONController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$this->registerTask('remove', 'backup');
	}

	/**
	 * Database check task.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dbcheck()
	{
		$this->checkValidToken();

		// Redirect to the page.
		$r = new \stdClass;
		$r->view = 'setup';

		// Check the form
		/** @var \Joomla\CMS\Installation\Model\SetupModel $model */
		$model = $this->getModel('Setup');
		if ($model->checkForm('setup') === false)
		{
			$this->app->enqueueMessage(Text::_('INSTL_DATABASE_VALIDATION_ERROR'), 'error');
			$r->validated = false;
			$this->sendJsonResponse($r);

			return;
		}

		$r->validated = $model->validateDbConnection();

		$this->sendJsonResponse($r);
	}

	/**
	 * Config task.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function config()
	{
		$this->checkValidToken();

		// Get the options from the session
		$options = $this->getModel('Setup')->getOptions();

		$r = new \stdClass;
		$r->view = 'remove';

		// Attempt to setup the configuration.
		if (!$this->getModel('Configuration')->setup($options))
		{
			$r->view = 'setup';
		}

		$this->sendJsonResponse($r);
	}

	/**
	 * Database task.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function database()
	{
		$this->checkValidToken();

		/** @var \Joomla\CMS\Installation\Model\SetupModel $model */
		$model = $this->getModel('Setup');
		$model->checkForm('setup');

		$r = new \stdClass;

		// Attempt to create the database tables
		/** @var \Joomla\CMS\Installation\Model\DatabaseModel $dbModel */
		$dbModel = $this->getModel('Database');

		if (!$dbModel->initialise() || !$dbModel->installCmsData())
		{
			$r->view = 'database';
		}

		$this->sendJsonResponse($r);
	}

	/**
	 * Backup task.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function backup()
	{
		$this->checkValidToken();

		$r = new \stdClass;
		$r->view = 'install';

		/** @var \Joomla\CMS\Installation\Model\DatabaseModel $model */
		$model = $this->getModel('Database');

		// Attempt to handle the old database.
		if (!$model->handleOldDatabase())
		{
			$r->view = 'database';
		}

		$this->sendJsonResponse($r);
	}

	/**
	 * Languages task.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function languages()
	{
		$this->checkValidToken();

		// Get array of selected languages
		$lids = $this->input->get('cid', [], 'array');
		$lids = ArrayHelper::toInteger($lids, []);

		if (!$lids)
		{
			// No languages have been selected
			$this->app->enqueueMessage(\JText::_('INSTL_LANGUAGES_NO_LANGUAGE_SELECTED'), 'warning');
		}
		else
		{
			// Get the languages model.
			/** @var \Joomla\CMS\Installation\Model\LanguagesModel $model */
			$model = $this->getModel('Languages');

			// Install selected languages
			$model->install($lids);

			// Publish the Content Languages.
			$model->publishContentLanguages();

			$this->app->enqueueMessage(\JText::_('INSTL_LANGUAGES_MORE_LANGUAGES'), 'notice');
		}

		// Redirect to the page.
		$r = new \stdClass;
		$r->view = 'remove';

		$this->sendJsonResponse($r);
	}

	/**
	 * Languages task.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function sample()
	{
		$this->checkValidToken();

		$r = new \stdClass;
		$r->view = 'remove';

		/** @var \Joomla\CMS\Installation\Model\DatabaseModel $model */
		$model = $this->getModel('Database');

		// Check if the database was initialised
		if (!$model->installSampleData())
		{
			$r->view = 'remove';
		}

		$this->sendJsonResponse($r);
	}

	/**
	 * Delete installation folder task.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function delete()
	{
		$this->checkValidToken();

		/** @var \Joomla\CMS\Installation\Model\CleanupModel $model */
		$model = $this->getModel('Cleanup');
		$success = $model->deleteInstallationFolder();

		// If an error was encountered return an error.
		if (!$success)
		{
			$this->app->enqueueMessage(\JText::sprintf('INSTL_COMPLETE_ERROR_FOLDER_DELETE', 'installation'), 'warning');
		}

		$r = new \stdClass;
		$r->view = 'remove';

		$this->sendJsonResponse($r);
	}
}
