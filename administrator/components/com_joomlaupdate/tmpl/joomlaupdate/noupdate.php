<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;

$uploadLink = 'index.php?option=com_joomlaupdate&view=upload';

$displayData = [
    'textPrefix' => 'COM_JOOMLAUPDATE' . $this->messagePrefix,
    'formURL'    => 'index.php?option=com_joomlaupdate&view=joomlaupdate',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/J4.x:We_cant_find_a_download_url',
    'icon'       => 'icon-loop joomlaupdate',
    'createURL'  => 'index.php?option=com_joomlaupdate&task=update.purge&' . Session::getFormToken() . '=1'
];

if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_joomlaupdate')) {
    $displayData['formAppend'] = '<div class="text-center"><a href="' . $uploadLink . '" class="btn btn-sm btn-outline-secondary">' . Text::_($displayData['textPrefix'] . '_EMPTYSTATE_APPEND') . '</a></div>';
}

$content = LayoutHelper::render('joomla.content.emptystate', $displayData);

// Inject Joomla! version
echo str_replace('%1$s', '&#x200E;' . $this->updateInfo['latest'], $content);
