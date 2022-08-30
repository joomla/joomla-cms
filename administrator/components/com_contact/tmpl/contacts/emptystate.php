<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_CONTACT',
    'formURL'    => 'index.php?option=com_contact',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help4.x:Contacts',
    'icon'       => 'icon-address-book contact',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_contact') || count($user->getAuthorisedCategories('com_contact', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_contact&task=contact.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
