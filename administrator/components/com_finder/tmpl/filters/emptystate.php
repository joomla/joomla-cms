<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_FINDER',
	'formURL'    => 'index.php?option=com_finder&view=filters',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Smart_Search_quickstart_guide',
	'icon'       => 'icon-search-plus finder',
	'btnadd'     => Text::_('COM_FINDER_FILTERS_EMPTYSTATE_BUTTON_ADD'),
	'content'    => Text::_('COM_FINDER_FILTERS_EMPTYSTATE_CONTENT'),
	'title'      => Text::_('COM_FINDER_FILTERS_TOOLBAR_TITLE'),
];

if (Factory::getApplication()->getIdentity()->authorise('core.create', 'com_finder'))
{
	$displayData['createURL']  = "index.php?option=com_finder&task=filter.add";
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
