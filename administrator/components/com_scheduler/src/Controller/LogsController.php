<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Controller;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Logs list controller class.
 *
 * @since  __DEPLOY_VERSION__
 */
class LogsController extends AdminController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $text_prefix = 'COM_SCHEDULER_LOGS';

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The name of the model.
     * @param   string  $prefix  The prefix for the PHP class name.
     * @param   array   $config  Array of configuration parameters.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getModel($name = 'Logs', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Clean out the logs.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function purge()
    {
        // Check for request forgeries.
        $this->checkToken();

        $model = $this->getModel('Logs');

        if ($model->purge()) {
            $message = Text::_('COM_SCHEDULER_LOGS_CLEAR');
        } else {
            $message = Text::_('COM_SCHEDULER_CLEAR_FAIL');
        }

        $this->setRedirect('index.php?option=com_scheduler&view=logs', $message);
    }

    /**
     * Removes an item.
     *
     * Overrides Joomla\CMS\MVC\Controller\FormController::delete to check the core.admin permission.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function delete(): void
    {
        $ids = $this->input->get('cid', [], 'array');

        if (!$this->app->getIdentity()->authorise('core.admin', $this->option)) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        if (empty($ids)) {
            $this->setMessage(Text::_('COM_SCHEDULER_NO_LOGS_SELECTED'), 'warning');
        } else {
            // Get the model.
            $model = $this->getModel();

            $ids = ArrayHelper::toInteger($ids);

            // Remove the items.
            if ($model->delete($ids)) {
                $this->setMessage(Text::plural('COM_SCHEDULER_N_ITEMS_DELETED', \count($ids)));
            }
        }

        $this->setRedirect('index.php?option=com_scheduler&view=logs');
    }
}
