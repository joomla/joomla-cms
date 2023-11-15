<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_CACHE',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Cache',
    'icon'       => 'icon-bolt clear',
];

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
