<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;

/** @var \Joomla\Component\Installer\Administrator\View\Update\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_INSTALLER',
    'formURL'    => 'index.php?option=com_installer&view=update',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help5.x:Extensions:_Update',
    'icon'       => 'icon-puzzle-piece install',
];

$user = $this->getCurrentUser();

if ($user->authorise('core.create', 'com_content') || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_installer&task=update.find&' . Session::getFormToken() . '=1';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
