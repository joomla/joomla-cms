<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help4.x:Information:_Warnings',
    'icon'       => 'icon-puzzle-piece install',
    'title'      => Text::_('COM_INSTALLER_MSG_WARNINGS_NONE'),
    'content'    => '',
];

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
