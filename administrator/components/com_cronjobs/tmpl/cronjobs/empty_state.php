<?php
/**
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_CRONJOBS',
	'formURL' => 'index.php?option=com_cronjobs&task=cronjob.add',
	'helpURL' => 'https://github.com/joomla-projects/soc21_website-cronjob',
	'icon' => 'icon-clock clock',
];

if (Factory::getApplication()->getIdentity()->authorise('core.create', 'com_cronjobs'))
{
	$displayData['createURL'] = 'index.php?option=com_cronjobs&view=select&layout=default';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
