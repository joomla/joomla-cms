<?php

/**
 * @package     Joomla.API
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Api\Controller;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\MVC\Controller\ApiController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
        if (!$this->app->getIdentity()->authorise('core.admin', $this->option)) {
            throw new NotAllowed('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN', 403);
        }

        if ($id === null) {
            $id = $this->input->get('id', 0, 'int');
        }

        $this->input->set('model', $this->contentType);

        return parent::displayItem($id);
    }

    /**
     * Basic display of a list view
     *
     * @return  static  A \JControllerLegacy object to support chaining.
     *
     * @since   6.1.2
     */
    public function displayList()
    {
        if (!$this->app->getIdentity()->authorise('core.admin', $this->option)) {
            throw new NotAllowed('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN', 403);
        }

        return parent::displayList();
    }
}
