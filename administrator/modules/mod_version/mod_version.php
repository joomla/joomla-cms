<?php

use Joomla\Module\Version\Administrator\Helper\VersionHelper;
use Joomla\CMS\Helper\ModuleHelper;
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_version
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$version = VersionHelper::getVersion();

require ModuleHelper::getLayoutPath('mod_version', $params->get('layout', 'default'));
