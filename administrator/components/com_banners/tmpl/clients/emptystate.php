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

/** @var \Joomla\Component\Banners\Administrator\View\Clients\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_BANNERS_CLIENT',
    'formURL'    => 'index.php?option=com_banners&view=clients',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help5.x:Banners:_Clients',
    'icon'       => 'icon-bookmark banners',
];

if (count($this->getCurrentUser()->getAuthorisedCategories('com_banners', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_banners&task=client.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
