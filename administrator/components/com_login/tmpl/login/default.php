<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_login
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

/**
 * Get the login modules
 * If you want to use a completely different login module change the value of name
 * in your layout override.
 */
$loginmodule = \Joomla\Component\Login\Administrator\Model\LoginModel::getLoginModule('mod_login');
echo ModuleHelper::renderModule($loginmodule, array('id' => 'section-box'));


/**
 * Get any other modules in the login position.
 * If you want to use a different position for the modules, change the name here in your override.
 */
$modules = ModuleHelper::getModules('login');

foreach ($modules as $module) {
// Render the login modules

    if ($module->module != 'mod_login') {
        echo ModuleHelper::renderModule($module, array('id' => 'section-box'));
    }
}
