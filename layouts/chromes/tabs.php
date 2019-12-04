<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * xhtml (divs and font header tags)
 * With the new advanced parameter it does the same as the html5 chrome
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

$module  = $displayData['module'];

$content = trim($module->content);

if (!empty($content))
{
	echo HTMLHelper::_('tabs.panel', $module->title, 'module' . $module->id);
	echo $content;
}
