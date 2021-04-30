<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_BANNERS_CLIENT',
	'formURL'    => 'index.php?option=com_banners&view=clients',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help40:Banners:_Clients',
	'icon'       => 'icon-bookmark banners',
];

if (count(Factory::getApplication()->getIdentity()->getAuthorisedCategories('com_banners', 'core.create')) > 0)
{
	$displayData['createURL'] = 'index.php?option=com_banners&task=client.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
