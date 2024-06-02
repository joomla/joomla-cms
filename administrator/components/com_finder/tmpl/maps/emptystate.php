<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_FINDER',
    'formURL'    => 'index.php?option=com_finder&view=maps',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help5.x:Smart_Search:_Content_Maps',
    'icon'       => 'icon-search-plus finder',
    'title'      => Text::_('COM_FINDER_MAPS_TOOLBAR_TITLE')
];

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
