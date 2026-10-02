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
 * The tours controller
 *
 * @since  __DEPLOY_VERSION__
 */
class ToursController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $contentType = 'tours';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $default_view = 'tours';

    /**
     * Method to allow extended classes to manipulate the data to be saved for an extension.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function preprocessSaveData(array $data): array
    {
        /**
         * The model requires ID to be an integer and present. So if a post request hardcode to 0 to mimic
         * a web request
         */
        if ($this->input->getMethod() === 'POST') {
            $data['id'] = 0;
        }

        return $data;
    }
}
