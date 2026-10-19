<?php

/**
 * @package Joomla.Administrator
 * @subpackage com_workflow
 *
 * @copyright (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for the automation log
 *
 * @since __DEPLOY_VERSION__
 */
class LogsController extends BaseController
{
    /**
     * Clears the requires_intervention flag on an item so it is retried.
     *
     * @return boolean
     *
     * @since __DEPLOY_VERSION__
     */
    public function reset()
    {
        $this->checkToken('get');

        $user      = $this->app->getIdentity();
        $itemId    = $this->input->getInt('item_id', 0);
        $extension = $this->input->getCmd('extension', '');
        $redirect  = 'index.php?option=com_workflow&view=logs&extension=' . $extension;

        if (!$user->authorise('core.admin', 'com_workflow')) {
            $this->setRedirect(Route::_($redirect, false), Text::_('JERROR_ALERTNOAUTHOR'), 'error');

            return false;
        }

        if ($itemId > 0 && $extension !== '') {
            /** @var \Joomla\Component\Workflow\Administrator\Model\LogsModel $model */
            $model = $this->getModel('Logs', 'Administrator');

            // The model checks the permission too, and its answer is the one that counts.
            if (!$model->clearIntervention($itemId, $extension)) {
                $this->setRedirect(Route::_($redirect, false), Text::_('JERROR_ALERTNOAUTHOR'), 'error');

                return false;
            }

            $this->setMessage(Text::_('COM_WORKFLOW_LOGS_RESET_SUCCESS'));
        }

        $this->setRedirect(Route::_($redirect, false));

        return true;
    }
}
