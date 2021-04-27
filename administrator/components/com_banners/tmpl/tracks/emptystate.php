<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

$displayData = array(
		'textPrefix' => 'COM_BANNERS_TRACKS',
		'formURL'    => 'index.php?option=com_banners&view=tracks',
		'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help40:Banners:_Tracks',
		'icon'       => 'icon-bookmark banners',
);

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
