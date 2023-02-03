<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_guidedtours
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
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

// Load language files from third-party extensions providing tours

$language_extensions = ['com_guidedtours'];
foreach ($tours as $tour) {
    $extensions = json_decode($tour->extensions);
    if ($extensions) {
        foreach ($extensions as $extension) {
            if (!in_array($extension, ['*', 'com_content', 'com_contact', 'com_banners', 'com_categories',  'com_menus', 'com_newsfeeds', 'com_finder',  'com_tags',  'com_users' ])) {
                $language_extensions[] = $extension;
            }
        }
    }
}

$lang = Factory::getLanguage();
foreach (array_unique($language_extensions) as $language_extension) {
    $lang->load($language_extension . '.sys', JPATH_ADMINISTRATOR);
}

require ModuleHelper::getLayoutPath('mod_guidedtours', $params->get('layout', 'default'));
