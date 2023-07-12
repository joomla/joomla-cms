<?php

/**
 * @package     Joomla.API
 * @subpackage  com_fields
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Api\Controller;

use Joomla\CMS\MVC\Controller\ApiController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The fields controller
 *
 * @since  4.0.0
 */
class FieldsController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $contentType = 'fields';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'fields';

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
}
