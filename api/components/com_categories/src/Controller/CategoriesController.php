<?php

/**
 * @package     Joomla.API
 * @subpackage  com_categories
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Api\Controller;

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\Table\Category;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The categories controller
 *
 * @since  4.0.0
 */
class CategoriesController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $contentType = 'categories';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'categories';

    /**
     * Method to allow extended classes to manipulate the data to be saved for an extension.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    protected function preprocessSaveData(array $data): array
    {
        $extension         = $this->getExtensionFromInput();
        $data['extension'] = $extension;

        // @todo: This is a hack to drop the extension into the global input object - to satisfy how state is built
        //       we should be able to improve this in the future
        $this->input->set('extension', $extension);

        return $data;
    }

    /**
     * Method to save a record.
     *
     * @param   integer  $recordKey  The primary key of the item (if exists)
     *
     * @return  integer  The record ID on success, false on failure
     *
     * @since   4.0.6
     */
    protected function save($recordKey = null)
    {
        $recordId = parent::save($recordKey);

        if (!$recordId) {
            return $recordId;
        }

        $data = $this->input->get('data', json_decode($this->input->json->getRaw(), true), 'array');

        if (empty($data['location'])) {
            return $recordId;
        }

        /** @var Category $category */
        $category = $this->getModel('Category')->getTable('Category');
        $category->load((int) $recordId);

        $reference = $category->parent_id;

        if (!empty($data['location_reference'])) {
            $reference = (int) $data['location_reference'];
        }

        $category->setLocation($reference, $data['location']);
        $category->store();

        return $recordId;
    }

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
        $this->modelState->set('filter.extension', $this->getExtensionFromInput());

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
        $apiFilterInfo = $this->input->get('filter', [], 'array');
        $filter        = InputFilter::getInstance();

        if (\array_key_exists('search', $apiFilterInfo)) {
            $this->modelState->set('filter.search', $filter->clean($apiFilterInfo['search'], 'STRING'));
        }

        if (\array_key_exists('state', $apiFilterInfo)) {
            $this->modelState->set('filter.published', $filter->clean($apiFilterInfo['state'], 'INT'));
        }

        $this->modelState->set('filter.extension', $this->getExtensionFromInput());

        return parent::displayList();
    }

    /**
     * Get extension from input
     *
     * @return string
     *
     * @since 4.0.0
     */
    private function getExtensionFromInput()
    {
        return $this->input->exists('extension') ?
            $this->input->get('extension') : $this->input->post->get('extension');
    }

    /**
     * Method to check if you can add a new record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   5.4.8
     * @since   6.1.3
     */
    protected function allowAdd($data = [])
    {
        $user      = $this->app->getIdentity();
        $extension = $this->getExtensionFromInput();

        // Require generic management permissions for the component
        if (!$user->authorise('core.manage', $extension)) {
            return false;
        }

        return $user->authorise('core.create', $extension) || \count($user->getAuthorisedCategories($extension, 'core.create'));
    }


    /**
     * Method to check if you can edit a record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   5.4.8
     * @since   6.1.3
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $recordId  = isset($data[$key]) ? (int) $data[$key] : 0;
        $user      = $this->app->getIdentity();
        $extension = $this->getExtensionFromInput();

        // Require generic management permissions for the component
        if (!$user->authorise('core.manage', $extension)) {
            return false;
        }

        // Need to do a lookup from the model to get the owner
        $record = $this->getModel('Category')->getItem($recordId);

        if (empty($record) || $record->extension !== $extension) {
            return false;
        }

        // Check "edit" permission on record asset (explicit or inherited)
        if ($user->authorise('core.edit', $extension . '.category.' . $recordId)) {
            return true;
        }

        // Check "edit own" permission on record asset (explicit or inherited)
        if ($user->authorise('core.edit.own', $extension . '.category.' . $recordId)) {
            $ownerId = $record->created_user_id;

            // If the owner matches 'me' then do the test.
            if ($ownerId == $user->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Method to check if it's allowed to delete a record
     *
     * @return  boolean
     *
     * @since   5.4.8
     * @since   6.1.3
     */
    protected function allowDelete(): bool
    {
        $extension = $this->getExtensionFromInput();
        $user      = $this->app->getIdentity();

        // Require generic management permissions for the component
        if (!$user->authorise('core.manage', $extension)) {
            return false;
        }

        return $user->authorise('core.delete', $extension);
    }
}
