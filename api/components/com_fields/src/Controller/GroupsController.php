<?php

/**
 * @package     Joomla.API
 * @subpackage  com_fields
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Api\Controller;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\MVC\Controller\ApiController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The groups controller
 *
 * @since  4.0.0
 */
class GroupsController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $contentType = 'groups';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'groups';

    /**
     * Basic display of an item view
     *
     * @param   integer  $id  The primary key to display. Leave empty if you want to retrieve data from the request
     *
     * @return  static  A \JControllerLegacy object to support chaining.
     *
     * @since   4.0.0
     */
    public function displayItem($id = null)
    {
        $this->modelState->set('filter.context', $this->getContextFromInput());

        return parent::displayItem($id);
    }

    /**
     * Basic display of a list view
     *
     * @return  static  A \JControllerLegacy object to support chaining.
     *
     * @since   4.0.0
     */
    public function displayList()
    {
        $this->modelState->set('filter.context', $this->getContextFromInput());

        return parent::displayList();
    }

    /**
     * Get extension from input
     *
     * @return string
     *
     * @since 4.0.0
     */
    private function getContextFromInput()
    {
        return $this->input->exists('context') ?
            $this->input->get('context') : $this->input->post->get('context');
    }

    /**
     * Method to check if you can add a new record.
     *
     * We don't allow adding from API
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   5.4.7
     */
    protected function allowAdd($data = [])
    {
        [$option] = explode('.', $this->getContextFromInput());

        return $this->app->getIdentity()->authorise('core.create', $option);
    }

    /**
     * Method to check if you can edit an existing record.
     *
     * We don't allow editing from API (yet?)
     *
     * @param array $data An array of input data.
     * @param string $key The name of the key for the primary key; default is id.
     *
     * @return  boolean
     *
     * @since   5.4.7
     */
    protected function allowEdit($data = [], $key = 'parent_id')
    {
        $recordId = isset($data[$key]) ? (int) $data[$key] : 0;
        $user     = $this->app->getIdentity();
        [$option] = explode('.', $this->getContextFromInput());

        // Zero record (id:0), return component edit permission by calling parent controller method
        if (!$recordId) {
            return $user->authorise('core.edit', $option);
        }

        // Check edit on the record asset (explicit or inherited)
        if ($user->authorise('core.edit', $option . '.fieldgroup.' . $recordId)) {
            return true;
        }

        // Check edit own on the record asset (explicit or inherited)
        if ($user->authorise('core.edit.own', $option . '.fieldgroup.' . $recordId)) {
            // Existing record already has an owner, get it
            $record = $this->getModel()->getItem($recordId);

            if (empty($record)) {
                return false;
            }

            // Grant if current user is owner of the record
            return $user->id == $record->created_user_id;
        }

        return false;
    }

    /**
     * Removes an item.
     *
     * @param   integer  $id  The primary key to delete item.
     *
     * @return  void
     *
     * @since   5.4.7
     */
    public function delete($id = null)
    {
        [$option] = explode('.', $this->getContextFromInput());

        if (!$this->app->getIdentity()->authorise('core.delete', $option)) {
            throw new NotAllowed('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED', 403);
        }

        parent::delete($id);
    }
}
