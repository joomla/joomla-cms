<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;

/**
 * Discover Installation Controller
 *
 * @since  1.6
 */
class DiscoverController extends BaseController
{
    /**
     * Refreshes the cache of discovered extensions.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function refresh()
    {
        $this->checkToken('request');

        /** @var \Joomla\Component\Installer\Administrator\Model\DiscoverModel $model */
        $model = $this->getModel('discover');
        $model->discover();

        if (!$model->getTotal()) {
            $this->setMessage(Text::_('COM_INSTALLER_ERROR_NO_EXTENSIONS_DISCOVERED'), 'info');
        }

        $this->setRedirect(Route::_('index.php?option=com_installer&view=discover', false));
    }

    /**
     * Install a discovered extension.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function install()
    {
        $this->checkToken();

        /** @var \Joomla\Component\Installer\Administrator\Model\DiscoverModel $model */
        $model = $this->getModel('discover');
        $model->discover_install();
        $this->setRedirect(Route::_('index.php?option=com_installer&view=discover', false));
    }

    /**
     * Clean out the discovered extension cache.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function purge()
    {
        $this->checkToken('request');

        /** @var \Joomla\Component\Installer\Administrator\Model\DiscoverModel $model */
        $model = $this->getModel('discover');
        $model->purge();
        $this->setRedirect(Route::_('index.php?option=com_installer&view=discover', false), $model->_message);
    }

    /**
     * Provide the data for a badge in a menu item via JSON
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function getMenuBadgeData()
    {
        if (!$this->app->getIdentity()->authorise('core.manage', 'com_installer')) {
            throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
        }

        $model = $this->getModel('Discover');

        echo new JsonResponse($model->getTotal());
    }
}
