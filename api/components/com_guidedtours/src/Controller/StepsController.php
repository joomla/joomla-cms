<?php

/**
 * @package     Joomla.API
 * @subpackage  com_guidedtours
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Api\Controller;

use Joomla\CMS\MVC\Controller\ApiController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The steps controller
 *
 * @since  __DEPLOY_VERSION__
 */
class StepsController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $contentType = 'steps';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $default_view = 'steps';

    /**
     * Basic display of an item view
     *
     * @param   integer  $id  The primary key to display. Leave empty if you want to retrieve data from the request
     *
     * @return  static  A \JControllerLegacy object to support chaining.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function displayItem($id = null)
    {
        $this->modelState->set('filter.tour_id', $this->input->get('tourid'));

        return parent::displayItem($id);
    }

    /**
     * Basic display of a list view
     *
     * @return  static  A \JControllerLegacy object to support chaining.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function displayList()
    {
        $this->modelState->set('filter.tour_id', $this->input->get('tourid'));

        return parent::displayList();
    }
}
