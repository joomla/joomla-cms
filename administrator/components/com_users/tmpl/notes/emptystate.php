<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\Component\Users\Administrator\View\Notes\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_USERS_NOTES',
    'formURL'    => 'index.php?option=com_users&view=notes',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help5.x:User_Notes',
    'icon'       => 'icon-users user',
];

if ($this->getCurrentUser()->authorise('core.create', 'com_users')) {
    $displayData['createURL'] = 'index.php?option=com_users&task=note.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
