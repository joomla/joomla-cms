<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Administrator\Controller;

use DateTimeZone;
use Exception;
use InvalidArgumentException;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogsModel;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Actionlogs list controller class.
 *
 * @since  3.9.0
 */
class ActionlogsController extends AdminController
{
    /**
     * Constructor.
     *
     * @param   array                $config   An optional associative array of configuration settings.
     *                                         Recognized key values include 'name', 'default_task', 'model_path', and
     *                                         'view_path' (this list is not meant to be comprehensive).
     * @param   MVCFactoryInterface  $factory  The factory.
     * @param   CMSApplication       $app      The Application for the dispatcher
     * @param   Input                $input    Input
     *
     * @since   3.9.0
     *
     * @throws  Exception
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        $this->registerTask('exportSelectedLogs', 'exportLogs');
    }

    /**
     * Method to export logs
     *
     * @return  void
     *
     * @since   3.9.0
     *
     * @throws  Exception
     */
    public function exportLogs()
    {
        // Check for request forgeries.
        $this->checkToken();

        $task = $this->getTask();

        $pks = [];

        if ($task == 'exportSelectedLogs') {
            // Get selected logs
            $pks = ArrayHelper::toInteger(explode(',', $this->input->post->getString('cids')));
        }

        /** @var ActionlogsModel $model */
        $model = $this->getModel();

        // Get the logs data
        $data = $model->getLogDataAsIterator($pks);

        if (\count($data)) {
            try {
                $rows = ActionlogsHelper::getCsvData($data);
            } catch (InvalidArgumentException $exception) {
                $this->setMessage(Text::_('COM_ACTIONLOGS_ERROR_COULD_NOT_EXPORT_DATA'), 'error');
                $this->setRedirect(Route::_('index.php?option=com_actionlogs&view=actionlogs', false));

                return;
            }

            // Destroy the iterator now
            unset($data);

            $date     = new Date('now', new DateTimeZone('UTC'));
            $filename = 'logs_' . $date->format('Y-m-d_His_T');

            $csvDelimiter = ComponentHelper::getComponent('com_actionlogs')->getParams()->get('csv_delimiter', ',');

            $this->app->setHeader('Content-Type', 'application/csv', true)
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.csv"', true)
                ->setHeader('Cache-Control', 'must-revalidate', true)
                ->sendHeaders();

            $output = fopen("php://output", "w");

            foreach ($rows as $row) {
                fputcsv($output, $row, $csvDelimiter);
            }

            fclose($output);
            $this->app->triggerEvent('onAfterLogExport', []);
            $this->app->close();
        } else {
            $this->setMessage(Text::_('COM_ACTIONLOGS_NO_LOGS_TO_EXPORT'));
            $this->setRedirect(Route::_('index.php?option=com_actionlogs&view=actionlogs', false));
        }
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  object  The model.
     *
     * @since   3.9.0
     */
    public function getModel($name = 'Actionlogs', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        // Return the model
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Clean out the logs
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function purge()
    {
        // Check for request forgeries.
        $this->checkToken();

        $model = $this->getModel();

        if ($model->purge()) {
            $message = Text::_('COM_ACTIONLOGS_PURGE_SUCCESS');
        } else {
            $message = Text::_('COM_ACTIONLOGS_PURGE_FAIL');
        }

        $this->setRedirect(Route::_('index.php?option=com_actionlogs&view=actionlogs', false), $message);
    }
}
