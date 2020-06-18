<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_version
 *
 * @copyright   © 2012 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ModVersionHelper', __DIR__ . '/helper.php');

$version = ModVersionHelper::getVersion($params);

require JModuleHelper::getLayoutPath('mod_version', $params->get('layout', 'default'));
