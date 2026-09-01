<?php

/**
 * @package     Joomla.API
 * @subpackage  com_fields
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Api\Controller;

use Doctrine\Inflector\InflectorFactory;
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
     * @since   6.1.2
     */
    protected function allowAdd($data = [])
    {
        $user     = $this->app->getIdentity();
        [$option] = explode('.', $this->getContextFromInput());

        if (!$user->authorise('core.manage', $option)) {
            return false;
        }

        return $user->authorise('core.create', $option);
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
     * @since   6.1.2
     */
    protected function allowEdit($data = [], $key = 'parent_id')
    {
        $recordId = isset($data[$key]) ? (int) $data[$key] : 0;
        $user     = $this->app->getIdentity();
        [$option] = explode('.', $this->getContextFromInput());

        // Require generic management permissions for the component
        if (!$user->authorise('core.manage', $option)) {
            return false;
        }

        // Zero record (id:0), return component edit permission by calling parent controller method
        if (!$recordId) {
            return $user->authorise('core.edit', $option);
        }

        // Get existing record
        $inflector = InflectorFactory::create()->build();
        $record    = $this->getModel($inflector->singularize($this->contentType))->getItem($recordId);

        if (empty($record)) {
            return false;
        }

        [$recordOption] = explode('.', $record->context);

        // Validate request context and field context match
        if ($recordOption !== $option) {
            return false;
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
     * Method to check if it's allowed to delete a record
     *
     * @return  boolean
     *
     * @since   6.1.3
     */
    protected function allowDelete(): bool
    {
        [$option] = explode('.', $this->getContextFromInput());
        $user     = $this->app->getIdentity();

        // Require generic management permissions for the component
        if (!$user->authorise('core.manage', $option)) {
            return false;
        }

        return $user->authorise('core.delete', $option);
    }
}
