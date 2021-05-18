<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_TAGS',
	'formURL'    => 'index.php?option=com_tags&task=tag.add',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/J3.x:How_To_Use_Content_Tags_in_Joomla!',
	'icon'       => 'icon-tags tags',
];

if (Factory::getApplication()->getIdentity()->authorise('core.create', 'com_tags'))
{
	$displayData['createURL'] = 'index.php?option=com_tags&task=tag.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
