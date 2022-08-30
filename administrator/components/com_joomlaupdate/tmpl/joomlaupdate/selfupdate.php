<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_JOOMLAUPDATE_SELF',
    'formURL'    => 'index.php?option=com_joomlaupdate&view=joomlaupdate',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Updating_from_an_existing_version',
    'icon'       => 'icon-loop joomlaupdate',
    'createURL'  => 'index.php?option=com_installer&view=update'
];

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
