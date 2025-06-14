<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\Component\Banners\Administrator\View\Banners\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_BANNERS',
    'formURL'    => 'index.php?option=com_banners&view=banners',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help5.x:Banners',
    'icon'       => 'icon-bookmark banners',
];

$user = $this->getCurrentUser();

if ($user->authorise('core.create', 'com_banners') || count($user->getAuthorisedCategories('com_banners', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_banners&task=banner.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
