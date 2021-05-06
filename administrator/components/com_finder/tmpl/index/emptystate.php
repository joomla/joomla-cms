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
	'formURL'    => 'index.php?option=com_finder&view=index',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Smart_Search_quickstart_guide',
	'icon'       => 'icon-search-plus finder',
	'content'    => Text::_('COM_FINDER_INDEX_NO_DATA') . '<br>' . Text::_('COM_FINDER_INDEX_TIP'),
	'title'      => Text::_('COM_FINDER_HEADING_INDEXER'),
	'createURL'  => "javascript:document.getElementsByClassName('button-archive')[0].click();",
];

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
