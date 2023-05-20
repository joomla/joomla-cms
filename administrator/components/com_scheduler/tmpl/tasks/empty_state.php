<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_SCHEDULER',
    'formURL' => 'index.php?option=com_scheduler&task=task.add',
    'helpURL' => 'https://docs.joomla.org/Special:MyLanguage/J4.x:Task_Scheduler',
    'icon' => 'icon-clock clock',
];

if (Factory::getApplication()->getIdentity()->authorise('core.create', 'com_scheduler')) {
    $displayData['createURL'] = 'index.php?option=com_scheduler&view=select&layout=default';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
