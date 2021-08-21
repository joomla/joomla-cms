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
	'textPrefix' => 'COM_COOKIEMANAGER_CONSENTS',
	'formURL'    => 'index.php?option=com_cookiemanager&view=consents',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage',
	'icon'       => 'icon-lock',
];

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
