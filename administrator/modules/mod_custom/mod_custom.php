<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_custom
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;

if ($params->def('prepare_content', 1)) {
    PluginHelper::importPlugin('content');
    $module->content = HTMLHelper::_('content.prepare', $module->content, '', 'mod_custom.content');
}

// Replace 'images/' to '../images/' when using an image from /images in backend.
$module->content = preg_replace('*src\=\"(?!administrator\/)images/*', 'src="../images/', $module->content);

require ModuleHelper::getLayoutPath('mod_custom', $params->get('layout', 'default'));
