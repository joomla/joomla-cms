<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_CHECKIN',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help4.x:Maintenance:_Global_Check-in',
	'icon'       => 'icon-check-square',
	'title'      => Text::_('COM_CHECKIN_GLOBAL_CHECK_IN'),
];

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
