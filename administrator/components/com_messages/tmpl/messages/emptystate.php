<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_MESSAGES',
	'formURL'    => 'index.php?option=com_messages&view=messages',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help40:Private_Messages',
	'icon'       => 'icon-envelope inbox',
];

if (Factory::getApplication()->getIdentity()->authorise('core.create', 'com_messages'))
{
	$displayData['createURL'] = 'index.php?option=com_messages&task=message.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
