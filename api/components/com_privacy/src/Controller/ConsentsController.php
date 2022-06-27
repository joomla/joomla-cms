<?php

/**
 * @package     Joomla.API
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Api\Controller;

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * The consents controller
 *
 * @since  4.0.0
 */
class ConsentsController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $contentType = 'consents';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'consents';

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
        if ($id === null) {
            $id = $this->input->get('id', 0, 'int');
        }

        $this->input->set('model', $this->contentType);

        return parent::displayItem($id);
    }
}
