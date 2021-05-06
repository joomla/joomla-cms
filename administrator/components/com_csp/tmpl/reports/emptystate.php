<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csp
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_CSP',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help4.x:Content_Security_Policy_Reports',
	'icon'       => 'icon-shield-alt',
];

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
