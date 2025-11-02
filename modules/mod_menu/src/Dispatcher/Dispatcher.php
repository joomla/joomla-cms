<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Menu\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_menu
 *
 * @since  5.4.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Runs the dispatcher.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function dispatch()
    {
        $displayData = $this->getLayoutData();

        if (!$displayData['list']) {
            return;
        }

        parent::dispatch();
    }

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.4.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $menuHelper = $this->getHelperFactory()->getHelper('MenuHelper');

        $data['list']       = $menuHelper->getItems($data['params'], $data['app']);
        $data['base']       = $menuHelper->getBaseItem($data['params'], $data['app']);
        $data['active']     = $menuHelper->getActiveItem($data['app']);
        $data['default']    = $menuHelper->getDefaultItem($data['app']);
        $data['active_id']  = $data['active']->id;
        $data['default_id'] = $data['default']->id;
        $data['path']       = $data['base']->tree;
        $data['showAll']    = $data['params']->get('showAllChildren', 1);
        $data['class_sfx']  = htmlspecialchars($data['params']->get('class_sfx', ''), ENT_COMPAT, 'UTF-8');

        return $data;
    }
}
