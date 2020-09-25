<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

// Include the module helper classes.
JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
JLoader::register('ModMenuHelper', __DIR__ . '/helper.php');
JLoader::register('JAdminCssMenu', __DIR__ . '/menu.php');

/** @var  Registry  $params */
$lang    = JFactory::getLanguage();
$user    = JFactory::getUser();
$input   = JFactory::getApplication()->input;
$enabled = !$input->getBool('hidemainmenu');

$menu = new JAdminCssMenu($user);
$menu->load($params, $enabled);

// Render the module layout
require JModuleHelper::getLayoutPath('mod_menu', $params->get('layout', 'default'));
