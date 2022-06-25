<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * ComponentDispatcher class for com_fields
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Method to check component access permission
     *
     * @since   4.0.0
     *
     * @return  void
     */
    protected function checkAccess()
    {
        $context   = $this->app->getUserStateFromRequest(
            'com_fields.groups.context',
            'context',
            $this->app->getUserStateFromRequest('com_fields.fields.context', 'context', 'com_content.article', 'CMD'),
            'CMD'
        );

        $parts = FieldsHelper::extract($context);

        if (!$parts || !$this->app->getIdentity()->authorise('core.manage', $parts[0])) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
