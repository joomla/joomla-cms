<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_media
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
        $user   = $this->app->getIdentity();
        $asset  = $this->input->get('asset');
        $author = $this->input->get('author');

        // Access check
        if (
            !$user->authorise('core.manage', 'com_media')
            && (!$asset || (!$user->authorise('core.edit', $asset)
            && !$user->authorise('core.create', $asset)
            && count($user->getAuthorisedCategories($asset, 'core.create')) == 0)
            && !($user->id == $author && $user->authorise('core.edit.own', $asset)))
        ) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
