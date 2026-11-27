<?php

/**
 * @package     Joomla.Api
 * @subpackage  com_fields
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Api\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Application\ApiApplication;
use Joomla\CMS\Dispatcher\ApiDispatcher;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_fields
 *
 * @since  6.2.1
 */
class Dispatcher extends ApiDispatcher
{
    /**
     * Method to check component access permission
     *
     * @since   6.2.1
     *
     * @return  void
     */
    protected function checkAccess(): void
    {
        $route = $this->app instanceof ApiApplication ? $this->app->getActiveRoute() : new Registry();
        $parts = FieldsHelper::extract($route->get('vars.context'));

        // extract() returns null for a missing or invalid context
        if (empty($parts[0])) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }

        if ($this->app->getIdentity()->authorise('core.api', $parts[0])) {
            return;
        }

        throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
    }
}
