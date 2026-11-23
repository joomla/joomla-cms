<?php

/**
 * @package     Joomla.Api
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Api\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ApiDispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Component dispatcher class for com_privacy
 *
 * @since  6.2.1
 */
class Dispatcher extends ApiDispatcher
{
    /**
     * Method to check component access permission
     *
     * @return  void
     *
     * @throws  NotAllowed
     */
    protected function checkAccess(): void
    {
        // Privacy requests contain personal data, only users with the admin permission may use them in the API
        if ($this->app->getIdentity()->authorise('core.admin', $this->option)) {
            return;
        }

        throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
    }
}
