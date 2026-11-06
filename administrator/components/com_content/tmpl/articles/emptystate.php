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

/** @var \Joomla\Component\Content\Administrator\View\Articles\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_CONTENT',
    'formURL'    => 'index.php?option=com_content&view=articles',
    'helpURL'    => 'https://guide.joomla.org/user-manual/articles',
    'icon'       => 'icon-copy article',

    'controlFields' => $this->filterForm->renderControlFields(),
];

$user = $this->getCurrentUser();

if ($user->authorise('core.create', 'com_content') || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_content&task=article.add';
}

$factory = Factory::getApplication()->bootComponent('com_guidedtours')->getMVCFactory();

// Get an instance of the guided tour model
$tourModel = $factory->createModel('Tour', 'Administrator', ['ignore_request' => true]);

$tourUid = 'joomla-articles';

if ($tourModel->isAvailable($tourUid) !== false) {
    $displayData['tourUID'] = $tourUid;
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
