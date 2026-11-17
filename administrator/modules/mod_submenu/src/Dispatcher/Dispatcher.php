<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_submenu
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Submenu\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;
use Joomla\Module\Submenu\Administrator\Menu\Menu;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_submenu
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
        $data = parent::getLayoutData();

        $menutype         = $data['params']->get('menutype', '*');
        $data['root']     = false;

        if ($menutype === '*') {
            $name           = $data['params']->get('preset', 'system');
            $data['root']   = MenusHelper::loadPreset($name);
        } else {
            $data['root'] = MenusHelper::getMenuItems($menutype, true);
        }

        if ($data['root'] && $data['root']->hasChildren()) {
            $data['app']->getLanguage()->load(
                'mod_menu',
                JPATH_ADMINISTRATOR,
                $data['app']->getLanguage()->getTag(),
                true
            );

            Menu::preprocess($data['root']);
        }

        return $data;
    }
}
