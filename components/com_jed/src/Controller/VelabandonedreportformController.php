<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

use function defined;

/**
 * Vel Abandoned Report Form controller class.
 *
 * @since 4.0.0
 */
class VelabandonedreportformController extends FormController
{
    /**
     * Method to abort current operation
     *
     * @param   null  $key
     *
     * @return void
     *
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function cancel($key = null)
    {
        $app = Factory::getApplication();

        // Get the current edit id.
        $editId = (int) $app->getUserState('com_jed.edit.velabandonedreport.id');

        // Get the model.
        $model = $this->getModel('Velabandonedreportform', 'Site');

        // Check in the item
        if ($editId) {
            $model->checkin($editId);
        }

        $menu = Factory::getApplication()->getMenu();
        $item = $menu->getActive();
        $url  = (empty($item->link) ? 'index.php?option=com_jed&view=velabandonedreports' : $item->link);
        $this->setRedirect(Route::_($url, false));
    }

    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     * @param   null  $key
     * @param   null  $urlVar
     *
     * @return void
     *
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function edit($key = null, $urlVar = null)
    {
        $app = Factory::getApplication();

        // Get the previous edit id (if any) and the current edit id.
        $previousId = (int) $app->getUserState('com_jed.edit.velabandonedreport.id');
        $editId     = $app->input->getInt('id', 0);

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_jed.edit.velabandonedreport.id', $editId);

        // Get the model.
        $model = $this->getModel('Velabandonedreportform', 'Site');

        // Check out the item
        if ($editId) {
            $model->checkout($editId);
        }

        // Check in the previous user.
        if ($previousId) {
            $model->checkin($previousId);
        }

        // Redirect to the edit screen.
        $this->setRedirect(Route::_('index.php?option=com_jed&view=velabandonedreportform&layout=edit', false));
    }

    /**
     * Method to save data.
     *
     * @param   null  $key
     * @param   null  $urlVar
     *
     * @return void
     *
     * @since 4.0.0
     * @throws Exception
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();

        // Initialise variables.
        $app   = Factory::getApplication();
        $model = $this->getModel('Velabandonedreportform', 'Site');

        // Get the user data.
        $data = Factory::getApplication()->input->get('jform', [], 'array');

        // Validate the posted data.
        $form = $model->getForm();

        if (!$form) {
            throw new Exception($model->getError(), 500);
        }

        // Validate the posted data.
        $data = $model->validate($form, $data);

        // Check for errors.
        if ($data === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            $input = $app->input;
            $jform = $input->get('jform', [], 'ARRAY');

            // Save the data in the session.
            $app->setUserState('com_jed.edit.velabandonedreport.data', $jform);

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_jed.edit.velabandonedreport.id');
            $this->setRedirect(Route::_('index.php?option=com_jed&view=velabandonedreportform&layout=edit&id=' . $id, false));

            $this->redirect();
        }

        // Attempt to save the data.
        $return = $model->save($data);

        // Check for errors.
        if ($return === false) {
            // Save the data in the session.
            $app->setUserState('com_jed.edit.velabandonedreport.data', $data);

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_jed.edit.velabandonedreport.id');
            $this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_jed&view=velabandonedreportform&layout=edit&id=' . $id, false));
        }

        // Check in the profile.
        if ($return) {
            $model->checkin($return);
        }

        // Clear the profile id from the session.
        $app->setUserState('com_jed.edit.velabandonedreport.id', null);

        // Redirect to the list of Tickets screen.
        $this->setMessage(Text::_('COM_JED_VEL_GENERAL_SAVED_SUCCESSFULLY'));
        $url = 'index.php?option=com_jed&view=jedtickets';
        $this->setRedirect(Route::_($url, false));

        // Flush the data from the session.
        $app->setUserState('com_jed.edit.velabandonedreport.data', null);
    }

    /**
     * Method to remove data
     *
     * There should be no removing of submitted forms, so this function is commented out
     *
     * @return void
     *
     * @since 4.0.0
     * @throws Exception
     *
     */
    /*  public function remove()
        {
            $app   = Factory::getApplication();
            $model = $this->getModel('Velabandonedreportform', 'Site');
            $pk    = $app->input->getInt('id');

            // Attempt to save the data
            try
            {
                $return = $model->delete($pk);

                // Check in the profile
                $model->checkin($return);

                // Clear the profile id from the session.
                $app->setUserState('com_jed.edit.velabandonedreport.id', null);

                $menu = $app->getMenu();
                $item = $menu->getActive();
                $url  = (empty($item->link) ? 'index.php?option=com_jed&view=velabandonedreports' : $item->link);

                // Redirect to the list screen
                $this->setMessage(Text::_('COM_JED_ITEM_DELETED_SUCCESSFULLY'));
                $this->setRedirect(Route::_($url, false));

                // Flush the data from the session.
                $app->setUserState('com_jed.edit.velabandonedreport.data', null);
            }
            catch (Exception $e)
            {
                $errorType = ($e->getCode() == '404') ? 'error' : 'warning';
                $this->setMessage($e->getMessage(), $errorType);
                $this->setRedirect('index.php?option=com_jed&view=velabandonedreports');
            }
        }*/
}
