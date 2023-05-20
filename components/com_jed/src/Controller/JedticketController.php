<?php

/**
 * @package       JED
 *
 * @subpackage    TICKETS
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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Jedticket class.
 *
 * @since  4.0.0
 */
class JedticketController extends BaseController
{
    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     * @return void
     *
     * @since    4.0.0
     *
     * @throws Exception
     */
    public function edit()
    {
        $app = Factory::getApplication();

        // Get the previous edit id (if any) and the current edit id.
        $previousId = (int) $app->getUserState('com_jed.edit.jedticket.id');
        $editId     = $app->input->getInt('id', 0);

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_jed.edit.jedticket.id', $editId);

        // Get the model.
        $model = $this->getModel('Jedticket', 'Site');

        // Check out the item
        if ($editId) {
            $model->checkout($editId);
        }

        // Check in the previous user.
        if ($previousId && $previousId !== $editId) {
            $model->checkin($previousId);
        }

        // Redirect to the edit screen.
        $this->setRedirect(Route::_('index.php?option=com_jed&view=jedticketform&layout=edit', false));
    }

    /**
     * Method to save data
     *
     * @return    void
     *
     * @since    4.0.0
     * @throws Exception
     */
    public function publish()
    {
        // Initialise variables.
        $app = Factory::getApplication();

        // Checking if the user can remove object
        $user = JedHelper::getUser();

        if ($user->authorise('core.edit', 'com_jed') || $user->authorise('core.edit.state', 'com_jed')) {
            $model = $this->getModel('Jedticket', 'Site');

            // Get the user data.
            $id    = $app->input->getInt('id');
            $state = $app->input->getInt('state');

            // Attempt to save the data.
            $return = $model->publish($id, $state);

            // Check for errors.
            if ($return === false) {
                $this->setMessage(Text::sprintf('Save failed: %s', $model->getError()), 'warning');
            }

            // Clear the profile id from the session.
            $app->setUserState('com_jed.edit.jedticket.id', null);

            // Flush the data from the session.
            $app->setUserState('com_jed.edit.jedticket.data', null);

            // Redirect to the list screen.
            $this->setMessage(Text::_('COM_JED_ITEM_SAVED_SUCCESSFULLY'));
            $menu = Factory::getApplication()->getMenu();
            $item = $menu->getActive();

            if (!$item) {
                // If there isn't any menu item active, redirect to list view
                $this->setRedirect(Route::_('index.php?option=com_jed&view=jedtickets', false));
            } else {
                $this->setRedirect(Route::_('index.php?Itemid=' . $item->id, false));
            }
        } else {
            throw new Exception(500);
        }
    }

    /**
     * Remove data
     *
     * No Tickets should be removed via the front-end so this code is commented out. Keeping it in case it's decided to be needed.
     *
     *
     * @return void
     * @since    4.0.0
     *
     * @throws Exception
     */
    /*  public function remove()
    {
        // Initialise variables.
        $app = Factory::getApplication();

        // Checking if the user can remove object
        $user = JedHelper::getUser();

        if ($user->authorise('core.delete', 'com_jed'))
        {
            $model = $this->getModel('Jedticket', 'Site');

            // Get the user data.
            $id = $app->input->getInt('id', 0);

            // Attempt to save the data.
            $return = $model->delete($id);

            // Check for errors.
            if ($return === false)
            {
                $this->setMessage(Text::sprintf('Delete failed', $model->getError()), 'warning');
            }
            else
            {
                // Check in the profile.
                if ($return)
                {
                    $model->checkin($return);
                }

                $app->setUserState('com_jed.edit.jedticket.id', null);
                $app->setUserState('com_jed.edit.jedticket.data', null);

                $app->enqueueMessage(Text::_('COM_JED_ITEM_DELETED_SUCCESSFULLY'), 'success');
                $app->redirect(Route::_('index.php?option=com_jed&view=jedtickets', false));
            }

            // Redirect to the list screen.
            $menu = Factory::getApplication()->getMenu();
            $item = $menu->getActive();
            $this->setRedirect(Route::_($item->link, false));
        }
        else
        {
            throw new Exception(500);
        }
    }
        } */
}
