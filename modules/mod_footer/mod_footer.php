<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_footer
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;

$date       = Factory::getDate();
$cur_year   = HTMLHelper::_('date', $date, 'Y');
$csite_name = $app->get('sitename');

if (is_int(StringHelper::strpos(Text::_('MOD_FOOTER_LINE1'), '%date%'))) {
    $line1 = str_replace('%date%', $cur_year, Text::_('MOD_FOOTER_LINE1'));
} else {
    $line1 = Text::_('MOD_FOOTER_LINE1');
}

if (is_int(StringHelper::strpos($line1, '%sitename%'))) {
    $lineone = str_replace('%sitename%', $csite_name, $line1);
} else {
    $lineone = $line1;
}

require ModuleHelper::getLayoutPath('mod_footer', $params->get('layout', 'default'));
