<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Administrator\Controller;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Versioning\VersionableControllerTrait;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Newsfeed controller class.
 *
 * @since  1.6
 */
class NewsfeedController extends FormController
{
    use VersionableControllerTrait;

    /**
     * Method override to check if you can add a new record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowAdd($data = [])
    {
        $categoryId = ArrayHelper::getValue($data, 'catid', $this->input->getInt('filter_category_id'), 'int');
        $allow      = null;

        if ($categoryId) {
            // If the category has been passed in the URL check it.
            $allow = $this->app->getIdentity()->authorise('core.create', $this->option . '.category.' . $categoryId);
        }

        if ($allow === null) {
            // In the absence of better information, revert to the component permissions.
            return parent::allowAdd($data);
        }

        return $allow;
    }

    /**
     * Method to check if you can edit a record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $recordId = (int) isset($data[$key]) ? $data[$key] : 0;

        // Since there is no asset tracking, fallback to the component permissions.
        if (!$recordId) {
            return parent::allowEdit($data, $key);
        }

        // Get the item.
        $item = $this->getModel()->getItem($recordId);

        // Since there is no item, return false.
        if (empty($item)) {
            return false;
        }

        $user = $this->app->getIdentity();

        // Check if can edit own core.edit.own.
        $canEditOwn = $user->authorise('core.edit.own', $this->option . '.category.' . (int) $item->catid) && $item->created_by == $user->id;

        // Check the category core.edit permissions.
        return $canEditOwn || $user->authorise('core.edit', $this->option . '.category.' . (int) $item->catid);
    }

    /**
     * Method to run batch operations.
     *
     * @param   object  $model  The model.
     *
     * @return  boolean   True if successful, false otherwise and internal error is set.
     *
     * @since   2.5
     */
    public function batch($model = null)
    {
        $this->checkToken();

        // Set the model
        $model = $this->getModel('Newsfeed', '', []);

        // Preset the redirect
        $this->setRedirect(Route::_('index.php?option=com_newsfeeds&view=newsfeeds' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }

    /**
     * Method to cancel an edit.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  boolean  True if access level checks pass, false otherwise.
     *
     * @since   5.1.0
     */
    public function cancel($key = null)
    {
        $result = parent::cancel($key);

        // When editing in modal then redirect to modalreturn layout
        if ($result && $this->input->get('layout') === 'modal') {
            $id     = $this->input->get('id');
            $return = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id)
                . '&layout=modalreturn&from-task=cancel';

            $this->setRedirect(Route::_($return, false));
        }

        return $result;
    }

    /**
     * Function that allows child controller access to model data
     * after the data has been saved.
     *
     * @param   BaseDatabaseModel  $model      The data model object.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   5.1.0
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = [])
    {
        // When editing in modal then redirect to modalreturn layout
        if ($this->input->get('layout') === 'modal' && $this->task === 'save') {
            $id     = $model->getState('newsfeed.id', '');
            $return = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id)
                . '&layout=modalreturn&from-task=save';

            $this->setRedirect(Route::_($return, false));
        }
    }
}
