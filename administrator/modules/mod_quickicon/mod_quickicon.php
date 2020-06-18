<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   © 2005 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ModQuickIconHelper', __DIR__ . '/helper.php');

$buttons = ModQuickIconHelper::getButtons($params);

require JModuleHelper::getLayoutPath('mod_quickicon', $params->get('layout', 'default'));
