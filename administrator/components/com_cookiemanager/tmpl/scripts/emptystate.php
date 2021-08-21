<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cookiemanager
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_COOKIEMANAGER_SCRIPTS',
	'formURL'    => 'index.php?option=com_cookiemanager&view=scripts',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage',
	'icon'       => 'icon-code',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_cookiemanager') || count($user->getAuthorisedCategories('com_cookiemanager', 'core.create')) > 0)
{
	$displayData['createURL'] = 'index.php?option=com_cookiemanager&task=script.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
