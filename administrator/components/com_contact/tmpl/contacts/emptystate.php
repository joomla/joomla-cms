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

/** @var \Joomla\Component\Contact\Administrator\View\Contacts\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_CONTACT',
    'formURL'    => 'index.php?option=com_contact',
    'helpURL'    => 'https://guide.joomla.org/user-manual/contacts',
    'icon'       => 'icon-address-book contact',

    'controlFields' => $this->filterForm->renderControlFields(),
];

$user = $this->getCurrentUser();

if ($user->authorise('core.create', 'com_contact') || count($user->getAuthorisedCategories('com_contact', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_contact&task=contact.add';
}

$factory = Factory::getApplication()->bootComponent('com_guidedtours')->getMVCFactory();

// Get an instance of the guided tour model
$tourModel = $factory->createModel('Tour', 'Administrator', ['ignore_request' => true]);

$tourUid = 'joomla-contacts';

if ($tourModel->isAvailable($tourUid) !== false) {
    $displayData['tourUID'] = $tourUid;
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
