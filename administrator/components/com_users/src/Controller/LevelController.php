<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Controller;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * User view level controller class.
 *
 * @since  1.6
 */
class LevelController extends FormController
{
    /**
     * @var     string  The prefix to use with controller messages.
     * @since   1.6
     */
    protected $text_prefix = 'COM_USERS_LEVEL';

    /**
     * Method to check if you can save a new or existing record.
     *
     * Overrides Joomla\CMS\MVC\Controller\FormController::allowSave to check the core.admin permission.
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
        return ($this->app->getIdentity()->authorise('core.admin', $this->option) && parent::allowSave($data, $key));
    }

    /**
     * Overrides JControllerForm::allowEdit
     *
     * Checks that non-Super Admins are not editing Super Admins.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean
     *
     * @since   3.8.8
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        // Check for if Super Admin can edit
        $viewLevel = $this->getModel('Level', 'Administrator')->getItem((int) $data['id']);

        // If this group is super admin and this user is not super admin, canEdit is false
        if (!$this->app->getIdentity()->authorise('core.admin') && $viewLevel->rules && Access::checkGroup($viewLevel->rules[0], 'core.admin')) {
            $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');

            $this->setRedirect(
                Route::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_list
                    . $this->getRedirectToListAppend(),
                    false
                )
            );

            return false;
        }

        return parent::allowEdit($data, $key);
    }

    /**
     * Removes an item.
     *
     * Overrides Joomla\CMS\MVC\Controller\FormController::delete to check the core.admin permission.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function delete()
    {
        // Check for request forgeries.
        $this->checkToken();

        $ids = (array) $this->input->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $ids = array_filter($ids);

        if (!$this->app->getIdentity()->authorise('core.admin', $this->option)) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        if (empty($ids)) {
            $this->setMessage(Text::_('COM_USERS_NO_LEVELS_SELECTED'), 'warning');
        } else {
            // Get the model.
            $model = $this->getModel();

            // Remove the items.
            if ($model->delete($ids)) {
                $this->setMessage(Text::plural('COM_USERS_N_LEVELS_DELETED', \count($ids)));
            }
        }

        $this->setRedirect('index.php?option=com_users&view=levels');
    }
}
