<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_media
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Site\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * ComponentDispatcher class for com_media
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Load the language
     *
     * @since   4.0.0
     *
     * @return  void
     */
    protected function loadLanguage()
    {
        // Load the administrator languages needed for the media manager
        $this->app->getLanguage()->load('', JPATH_ADMINISTRATOR);
        $this->app->getLanguage()->load($this->option, JPATH_ADMINISTRATOR);

        parent::loadLanguage();
    }

    /**
     * Method to check component access permission
     *
     * @since   4.0.0
     *
     * @return  void
     */
    protected function checkAccess()
    {
        $user = $this->app->getIdentity();

        // Access check
        if (
            !$user->authorise('core.manage', 'com_media')
            && !$user->authorise('core.create', 'com_media')
        ) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    /**
     * Get a controller from the component
     *
     * @param   string  $name    Controller name
     * @param   string  $client  Optional client (like Administrator, Site etc.)
     * @param   array   $config  Optional controller config
     *
     * @return  BaseController
     *
     * @since   4.0.0
     */
    public function getController(string $name, string $client = '', array $config = array()): BaseController
    {
        $config['base_path'] = JPATH_ADMINISTRATOR . '/components/com_media';

        // Force to load the admin controller
        return parent::getController($name, 'Administrator', $config);
    }
}
