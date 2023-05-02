<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Languages Override Controller
 *
 * @since  2.5
 */
class OverrideController extends FormController
{
    /**
     * Method to edit an existing override.
     *
     * @param   string  $key     The name of the primary key of the URL variable (not used here).
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (not used here).
     *
     * @return  void
     *
     * @since   2.5
     */
    public function edit($key = null, $urlVar = null)
    {
        // Do not cache the response to this, its a redirect
        $this->app->allowCache(false);

        $cid     = (array) $this->input->post->get('cid', [], 'string');
        $context = "$this->option.edit.$this->context";

        // Get the constant name.
        $recordId = (count($cid) ? $cid[0] : $this->input->get('id'));

        // Access check.
        if (!$this->allowEdit()) {
            $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');
            $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));

            return;
        }

        $this->app->setUserState($context . '.data', null);
        $this->setRedirect('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, 'id'));
    }

    /**
     * Method to save an override.
     *
     * @param   string  $key     The name of the primary key of the URL variable (not used here).
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (not used here).
     *
     * @return  void
     *
     * @since   2.5
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();

        $app     = $this->app;
        $model   = $this->getModel();
        $data    = $this->input->post->get('jform', [], 'array');
        $context = "$this->option.edit.$this->context";
        $task    = $this->getTask();

        $recordId   = $this->input->get('id');
        $data['id'] = $recordId;

        // Access check.
        if (!$this->allowSave($data, 'id')) {
            $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');
            $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));

            return;
        }

        // Validate the posted data.
        $form = $model->getForm($data, false);

        if (!$form) {
            $app->enqueueMessage($model->getError(), 'error');

            return;
        }

        // Test whether the data is valid.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof \Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the data in the session.
            $app->setUserState($context . '.data', $data);

            // Redirect back to the edit screen.
            $this->setRedirect(
                Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, 'id'), false)
            );

            return;
        }

        // Attempt to save the data.
        if (!$model->save($validData)) {
            // Save the data in the session.
            $app->setUserState($context . '.data', $validData);

            // Redirect back to the edit screen.
            $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
            $this->setRedirect(
                Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, 'id'), false)
            );

            return;
        }

        // Add message of success.
        $this->setMessage(Text::_('COM_LANGUAGES_VIEW_OVERRIDE_SAVE_SUCCESS'));

        // Redirect the user and adjust session state based on the chosen task.
        switch ($task) {
            case 'apply':
                // Set the record data in the session.
                $app->setUserState($context . '.data', null);

                // Redirect back to the edit screen
                $this->setRedirect(
                    Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($validData['key'], 'id'), false)
                );
                break;

            case 'save2new':
                // Clear the record id and data from the session.
                $app->setUserState($context . '.data', null);

                // Redirect back to the edit screen
                $this->setRedirect(
                    Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend(null, 'id'), false)
                );
                break;

            default:
                // Clear the record id and data from the session.
                $app->setUserState($context . '.data', null);

                // Redirect to the list screen.
                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));
                break;
        }
    }

    /**
     * Method to cancel an edit.
     *
     * @param   string  $key  The name of the primary key of the URL variable (not used here).
     *
     * @return  void
     *
     * @since   2.5
     */
    public function cancel($key = null)
    {
        $this->checkToken();

        $context = "$this->option.edit.$this->context";

        $this->app->setUserState($context . '.data', null);
        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));
    }
}
