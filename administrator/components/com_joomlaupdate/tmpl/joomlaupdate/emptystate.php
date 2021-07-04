<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_JOOMLAUPDATE',
	'formURL'    => 'index.php?option=com_joomlaupdate&view=joomlaupdate',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Updating_from_an_existing_version',
	'icon'       => 'icon-loop joomlaupdate',
];

	$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.admin', 'com_joomlaupdate'))
{
	$displayData['createURL'] = 'index.php?option=com_joomlaupdate&layout=upload';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
