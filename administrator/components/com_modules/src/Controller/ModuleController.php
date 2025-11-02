<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Controller;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Module controller class.
 *
 * @since  1.6
 */
class ModuleController extends FormController
{
    /**
     * Override parent add method.
     *
     * @return  \Exception|void  True if the record can be added, a \Exception object if not.
     *
     * @since   1.6
     */
    public function add()
    {
        $app = $this->app;

        // Get the result of the parent method. If an error, just return it.
        $result = parent::add();

        if ($result instanceof \Exception) {
            return $result;
        }

        // Look for the Extension ID.
        $extensionId = $this->input->get('eid', 0, 'int');

        if (empty($extensionId)) {
            $redirectUrl = 'index.php?option=' . $this->option . '&view=' . $this->view_item . '&layout=edit';

            $this->setRedirect(Route::_($redirectUrl, false));

            $app->enqueueMessage(Text::_('COM_MODULES_ERROR_INVALID_EXTENSION'), 'warning');
        }

        $app->setUserState('com_modules.add.module.extension_id', $extensionId);
        $app->setUserState('com_modules.add.module.params', null);

        // Parameters could be coming in for a new item, so let's set them.
        $params = $this->input->get('params', [], 'array');
        $app->setUserState('com_modules.add.module.params', $params);
    }

    /**
     * Override parent cancel method to reset the add module state.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  boolean  True if access level checks pass, false otherwise.
     *
     * @since   1.6
     */
    public function cancel($key = null)
    {
        $result = parent::cancel();

        $this->app->setUserState('com_modules.add.module.extension_id', null);
        $this->app->setUserState('com_modules.add.module.params', null);

        if ($return = $this->input->get('return', '', 'BASE64')) {
            $return = base64_decode($return);

            // Don't redirect to an external URL.
            if (!Uri::isInternal($return)) {
                $return = Uri::base();
            }

            $this->app->redirect($return);
        } elseif ($result && $this->input->get('layout') === 'modal') {
            // When editing in modal then redirect to modalreturn layout
            $id     = $this->input->get('id');
            $return = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id)
                . '&layout=modalreturn&from-task=cancel';

            $this->setRedirect(Route::_($return, false));
        }

        return $result;
    }

    /**
     * Override parent allowSave method.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowSave($data, $key = 'id')
    {
        // Use custom position if selected
        if (isset($data['custom_position'])) {
            if (empty($data['position'])) {
                $data['position'] = $data['custom_position'];
            }

            unset($data['custom_position']);
        }

        return parent::allowSave($data, $key);
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean
     *
     * @since   3.2
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        // Initialise variables.
        $recordId = isset($data[$key]) ? (int) $data[$key] : 0;

        // Zero record (id:0), return component edit permission by calling parent controller method
        if (!$recordId) {
            return parent::allowEdit($data, $key);
        }

        // Check edit on the record asset (explicit or inherited)
        if ($this->app->getIdentity()->authorise('core.edit', 'com_modules.module.' . $recordId)) {
            return true;
        }

        return false;
    }

    /**
     * Method to run batch operations.
     *
     * @param   string  $model  The model
     *
     * @return  boolean  True on success.
     *
     * @since   1.7
     */
    public function batch($model = null)
    {
        $this->checkToken();

        // Set the model
        $model = $this->getModel('Module', 'Administrator', []);

        // Preset the redirect
        $redirectUrl = 'index.php?option=com_modules&view=modules' . $this->getRedirectToListAppend();

        $this->setRedirect(Route::_($redirectUrl, false));

        return parent::batch($model);
    }

    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param   BaseDatabaseModel  $model      The data model object.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = [])
    {
        $task = $this->getTask();

        switch ($task) {
            case 'save2new':
                $this->app->setUserState('com_modules.add.module.extension_id', $model->getState('module.extension_id'));
                break;

            default:
                if ($this->input->get('layout') === 'modal' && $this->task === 'save') {
                    // When editing in modal then redirect to modalreturn layout
                    $id     = $model->getState('module.id', '');
                    $return = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id)
                        . '&layout=modalreturn&from-task=save';

                    $this->setRedirect(Route::_($return, false));
                }

                $this->app->setUserState('com_modules.add.module.extension_id', null);
                break;
        }

        $this->app->setUserState('com_modules.add.module.params', null);
    }

    /**
     * Method to save a record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key
     *
     * @return  boolean  True if successful, false otherwise.
     */
    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();

        if ($this->app->getDocument()->getType() == 'json') {
            $model      = $this->getModel();
            $data       = $this->input->post->get('jform', [], 'array');
            $item       = $model->getItem($this->input->get('id'));
            $properties = $item->getProperties();

            if (isset($data['params'])) {
                unset($properties['params']);
            }

            // Replace changed properties
            $data = array_replace_recursive($properties, $data);

            if (!empty($data['assigned'])) {
                $data['assigned'] = array_map('abs', $data['assigned']);
            }

            // Add new data to input before process by parent save()
            $this->input->post->set('jform', $data);

            // Add path of forms directory
            Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_modules/models/forms');
        }

        return parent::save($key, $urlVar);
    }

    /**
     * Method to get the other modules in the same position
     *
     * @return  void
     *
     * @since   3.6.3
     */
    public function orderPosition()
    {
        $app = $this->app;

        // Send json mime type.
        $app->mimeType = 'application/json';
        $app->setHeader('Content-Type', $app->mimeType . '; charset=' . $app->charSet);
        $app->sendHeaders();

        // Check if user token is valid.
        if (!Session::checkToken('get')) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN_NOTICE'), 'error');
            echo new JsonResponse();
            $app->close();
        }

        $clientId = $this->input->getValue('client_id');
        $position = $this->input->getValue('position');
        $moduleId = $this->input->getValue('module_id');

        // Access check.
        if (
            !$this->app->getIdentity()->authorise('core.create', 'com_modules')
            && !$this->app->getIdentity()->authorise('core.edit.state', 'com_modules')
            && ($moduleId && !$this->app->getIdentity()->authorise('core.edit.state', 'com_modules.module.' . $moduleId))
        ) {
            $app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 'error');
            echo new JsonResponse();
            $app->close();
        }

        $model = $this->getModel('Modules', 'Administrator', ['ignore_request' => true]);
        $model->setState('client_id', $clientId);
        $model->setState('filter.position', $position);
        $model->setState('list.ordering', 'a.ordering');

        try {
            $orders = $model->getItems();
        } catch (\RuntimeException $e) {
            $app->enqueueMessage($e->getMessage(), 'error');

            return;
        }

        $orders2 = [];

        if (\count($orders)) {
            foreach ($orders as $order) {
                if (!isset($orders2[$order->position])) {
                    $orders2[$order->position] = 0;
                }

                $orders2[$order->position]++;
                $ord   = $orders2[$order->position];
                $title = Text::sprintf('COM_MODULES_OPTION_ORDER_POSITION', $ord, htmlspecialchars($order->title, ENT_QUOTES, 'UTF-8'));

                $html[] = $order->position . ',' . $ord . ',' . $title;
            }
        } else {
            $html[] = $position . ',' . 1 . ',' . Text::_('JNONE');
        }

        echo new JsonResponse($html);
        $app->close();
    }

    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param   integer  $recordId  The primary key id for the item.
     * @param   string   $urlVar    The name of the URL variable for the id.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since  4.0.0
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
    {
        $append = parent::getRedirectToItemAppend($recordId);
        $append .= '&client_id=' . $this->input->getInt('client_id');

        return $append;
    }

    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since  4.0.0
     */
    protected function getRedirectToListAppend()
    {
        $append = parent::getRedirectToListAppend();
        $append .= '&client_id=' . $this->input->getInt('client_id');

        return $append;
    }
}
