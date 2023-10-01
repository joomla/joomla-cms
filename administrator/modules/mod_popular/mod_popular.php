<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_popular
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Module\Popular\Administrator\Helper\PopularHelper;

$model = $app->bootComponent('com_content')->getMVCFactory()->createModel('Articles', 'Administrator', ['ignore_request' => true]);
$list  = PopularHelper::getList($params, $model);

// Get module data.
if ($params->get('automatic_title', 0)) {
    $module->title = PopularHelper::getTitle($params);
}

// If recording of hits is disabled.
if (!ComponentHelper::getParams('com_content')->get('record_hits', 1)) {
    echo LayoutHelper::render('joomla.content.emptystate_module', [
        'title' => 'JGLOBAL_RECORD_HITS_DISABLED',
        'icon'  => 'icon-minus-circle',
        ]);

    return;
}

// If there are some articles to display.
if (\count($list)) {
    require ModuleHelper::getLayoutPath('mod_popular', $params->get('layout', 'default'));

    return;
}

// If there are no articles to display, show empty state.
$app->getLanguage()->load('com_content');

echo LayoutHelper::render('joomla.content.emptystate_module', [
        'textPrefix' => 'COM_CONTENT',
        'icon'       => 'icon-copy',
    ]);
