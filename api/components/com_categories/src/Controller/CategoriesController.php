<?php

/**
 * @package     Joomla.API
 * @subpackage  com_categories
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Api\Controller;

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

        // TODO: This is a hack to drop the extension into the global input object - to satisfy how state is built
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
}
