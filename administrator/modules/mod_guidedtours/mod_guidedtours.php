<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_guidedtours
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Module\GuidedTours\Administrator\Helper\GuidedToursHelper;

if (!PluginHelper::isEnabled('system', 'tour')) {
    return;
}

$tours = GuidedToursHelper::getList($params);

if (empty($tours)) {
    return;
}

require ModuleHelper::getLayoutPath('mod_guidedtours', $params->get('layout', 'default'));
