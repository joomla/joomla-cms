<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Menu\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Module\Menu\Administrator\Menu\CssMenu;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_menu
 *
 * @since  5.4.0
 */
class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.4.0
     */
    protected function getLayoutData()
    {
        $db   = Factory::getContainer()->get(DatabaseInterface::class);
        $data = parent::getLayoutData();

        $data['enabled'] = !$data['app']->getInput()->getBool('hidemainmenu');

        $data['menu']        = new CssMenu($data['app'], $db);
        $data['root']        = $data['menu']->load($data['params'], $data['enabled']);
        $data['root']->level = 0;

        return $data;
    }
}
