<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Session\Session;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Default controller class for the Joomla Installer.
 *
 * @since  3.1
 */
class InstallationController extends JSONController
{
    /**
     * @param   array                         $config   An optional associative array of configuration settings.
     *                                                  Recognized key values include 'name', 'default_task', 'model_path', and
     *                                                  'view_path' (this list is not meant to be comprehensive).
     * @param   MVCFactoryInterface|null      $factory  The factory.
     * @param   CMSApplication|null           $app      The Application for the dispatcher
     * @param   \Joomla\CMS\Input\Input|null  $input    The Input object.
     *
     * @since   3.0
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        $this->registerTask('populate1', 'populate');
        $this->registerTask('populate2', 'populate');
        $this->registerTask('populate3', 'populate');
        $this->registerTask('custom1', 'populate');
        $this->registerTask('custom2', 'populate');
        $this->registerTask('removeFolder', 'delete');
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
        $r       = new \stdClass();
        $r->view = 'setup';

        // Check the form
        /** @var \Joomla\CMS\Installation\Model\SetupModel $model */
        $model = $this->getModel('Setup');

        if ($model->checkForm('setup') === false) {
            $this->app->enqueueMessage(Text::_('INSTL_DATABASE_VALIDATION_ERROR'), 'error');
            $r->validated = false;
            $this->sendJsonResponse($r);

            return;
        }

        $r->validated = $model->validateDbConnection();

        $this->sendJsonResponse($r);
    }

    /**
     * Create DB task.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function create()
    {
        $this->checkValidToken();

        $r = new \stdClass();

        /** @var \Joomla\CMS\Installation\Model\DatabaseModel $databaseModel */
        $databaseModel = $this->getModel('Database');

        // Create Db
        try {
            $dbCreated = $databaseModel->createDatabase();
        } catch (\RuntimeException $e) {
            $this->app->enqueueMessage($e->getMessage(), 'error');

            $dbCreated = false;
        }

        if (!$dbCreated) {
            $r->view = 'setup';
        } else {
            if (!$databaseModel->handleOldDatabase()) {
                $r->view = 'setup';
            }
        }

        $this->sendJsonResponse($r);
    }

    /**
     * Populate the database.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function populate()
    {
        $this->checkValidToken();
        $step = $this->getTask();
        /** @var \Joomla\CMS\Installation\Model\DatabaseModel $model */
        $model = $this->getModel('Database');

        $r     = new \stdClass();
        $db    = $model->initialise();
        $files = [
            'populate1' => 'base',
            'populate2' => 'supports',
            'populate3' => 'extensions',
            'custom1'   => 'localise',
            'custom2'   => 'custom',
        ];

        $schema     = $files[$step];
        $serverType = $db->getServerType();

        if (in_array($step, ['custom1', 'custom2']) && !is_file('sql/' . $serverType . '/' . $schema . '.sql')) {
            $this->sendJsonResponse($r);

            return;
        }

        if (!isset($files[$step])) {
            $r->view = 'setup';
            Factory::getApplication()->enqueueMessage(Text::_('INSTL_SAMPLE_DATA_NOT_FOUND'), 'error');
            $this->sendJsonResponse($r);
        }

        // Attempt to populate the database with the given file.
        if (!$model->createTables($schema)) {
            $r->view = 'setup';
        }

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

        /** @var \Joomla\CMS\Installation\Model\SetupModel $setUpModel */
        $setUpModel = $this->getModel('Setup');

        // Get the options from the session
        $options = $setUpModel->getOptions();

        $r       = new \stdClass();
        $r->view = 'remove';

        /** @var \Joomla\CMS\Installation\Model\ConfigurationModel $configurationModel */
        $configurationModel = $this->getModel('Configuration');

        // Attempt to setup the configuration.
        if (!$configurationModel->setup($options)) {
            $r->view = 'setup';
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
        $lids = (array) $this->input->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $lids = array_filter($lids);

        if (empty($lids)) {
            // No languages have been selected
            $this->app->enqueueMessage(Text::_('INSTL_LANGUAGES_NO_LANGUAGE_SELECTED'), 'warning');
        } else {
            // Get the languages model.
            /** @var \Joomla\CMS\Installation\Model\LanguagesModel $model */
            $model = $this->getModel('Languages');

            // Install selected languages
            $model->install($lids);
        }

        // Redirect to the page.
        $r       = new \stdClass();
        $r->view = 'remove';

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

        if (!$model->deleteInstallationFolder()) {
            // We can't send a response with sendJsonResponse because our installation classes might not now exist
            $error = [
                'token' => Session::getFormToken(true),
                'error' => true,
                'data'  => [
                    'view' => 'remove',
                ],
                'messages' => [
                    'warning' => [
                        Text::sprintf('INSTL_COMPLETE_ERROR_FOLDER_DELETE', 'installation'),
                    ],
                ],
            ];

            echo json_encode($error);

            return;
        }

        $this->app->getSession()->destroy();

        // We can't send a response with sendJsonResponse because our installation classes now do not exist
        echo json_encode(['error' => false]);
    }
}
