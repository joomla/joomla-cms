<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_toolbar
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Toolbar\Toolbar;

$toolbar = Toolbar::getInstance('toolbar')->render();

require ModuleHelper::getLayoutPath('mod_toolbar', $params->get('layout', 'default'));
