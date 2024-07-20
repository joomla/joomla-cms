<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Update Controller
 *
 * @since  2.5.4
 */
class DisplayController extends BaseController
{
    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached.
     * @param   array    $urlparams  An array of safe URL parameters and their variable types.
     *                   @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  static   This object to support chaining.
     *
     * @since   2.5.4
     */
    public function display($cachable = false, $urlparams = false)
    {
        // Get the document object.
        $document = $this->app->getDocument();

        // Set the default view name and format from the Request.
        $vName   = $this->input->get('view', 'Joomlaupdate');
        $vFormat = $document->getType();
        $lName   = $this->input->get('layout', 'default', 'string');

        // Get and render the view.
        if ($view = $this->getView($vName, $vFormat)) {
            // Only super user can access file upload
            if ($view == 'upload' && !$this->app->getIdentity()->authorise('core.admin', 'com_joomlaupdate')) {
                $this->app->redirect(Route::_('index.php?option=com_joomlaupdate', true));
            }

            // Get the model for the view.
            /** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
            $model = $this->getModel('Update');

            /** @var ?\Joomla\Component\Installer\Administrator\Model\WarningsModel $warningsModel */
            $warningsModel = $this->app->bootComponent('com_installer')
                ->getMVCFactory()->createModel('Warnings', 'Administrator', ['ignore_request' => true]);

            if ($warningsModel !== null) {
                $view->setModel($warningsModel, false);
            }

            // Check for update result
            if ($lName === 'complete') {
                $state = $model->getState();
                $state->set('update_finished_with_error', $this->app->getUserState('com_joomlaupdate.update_finished_with_error'));
                $state->set('update_errors', (array) $this->app->getUserState('com_joomlaupdate.update_errors', []));
                $state->set('update_channel_reset', $this->app->getUserState('com_joomlaupdate.update_channel_reset'));
                $state->set('installer_message', $this->app->getUserState('com_joomlaupdate.installer_message'));
                $state->set('log_file', $this->app->get('log_path') . '/joomla_update.php');
            }

            // Perform update source preference check and refresh update information.
            $model->applyUpdateSite();
            $model->refreshUpdates();

            // Push the model into the view (as default).
            $view->setModel($model, true);
            $view->setLayout($lName);

            // Push document object into the view.
            $view->document = $document;
            $view->display();
        }

        return $this;
    }

    /**
     * Provide the data for a badge in a menu item via JSON
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function getMenuBadgeData()
    {
        if (!$this->app->getIdentity()->authorise('core.manage', 'com_joomlaupdate')) {
            throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
        }

        $model = $this->getModel('Update');

        $model->refreshUpdates();

        $joomlaUpdate = $model->getUpdateInformation();

        $hasUpdate = $joomlaUpdate['hasUpdate'] ? $joomlaUpdate['latest'] : '';

        echo new JsonResponse($hasUpdate);
    }
}
