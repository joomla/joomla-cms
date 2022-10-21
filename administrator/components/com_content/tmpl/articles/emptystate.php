<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_CONTENT',
    'formURL'    => 'index.php?option=com_content&view=articles',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Adding_a_new_article',
    'icon'       => 'icon-copy article',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_content') || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_content&task=article.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
