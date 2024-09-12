<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\Component\Messages\Administrator\View\Messages\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_MESSAGES',
    'formURL'    => 'index.php?option=com_messages&view=messages',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help5.x:Private_Messages',
    'icon'       => 'icon-envelope inbox',
];

if (
    $this->getCurrentUser()->authorise('core.create', 'com_messages')
    && $this->getCurrentUser()->authorise('core.manage', 'com_users')
) {
    $displayData['createURL'] = 'index.php?option=com_messages&task=message.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
