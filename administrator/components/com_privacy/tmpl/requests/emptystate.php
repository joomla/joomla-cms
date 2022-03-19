<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_PRIVACY_REQUESTS',
	'formURL'    => 'index.php?option=com_privacy&view=requests',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help40:Privacy:_Information_Requests',
	'icon'       => 'icon-lock',
];

if (Factory::getApplication()->get('mailonline', 1))
{
	$displayData['createURL'] = 'index.php?option=com_privacy&task=request.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
