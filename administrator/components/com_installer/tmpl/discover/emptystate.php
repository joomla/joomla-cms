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
	'textPrefix' => 'COM_INSTALLER',
	'formURL'    => 'index.php?option=com_installer&task=discover.refresh',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help4.x:Extensions:_Discover',
	'icon'       => 'icon-puzzle-piece install',
	'content'    => Text::_('COM_INSTALLER_MSG_DISCOVER_DESCRIPTION'),
	'title'      => Text::_('COM_INSTALLER_EMPTYSTATE_DISCOVER_TITLE'),
];

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
