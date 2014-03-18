<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagenavigation
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

echo '<ul class="pager pagenav">' . PHP_EOL;

if ($row->prev)
{
	echo '	<li class="previous">' . PHP_EOL;
	echo '	<a href="' . $row->prev . '" rel="prev">' . JText::_('JGLOBAL_LT') . $pnSpace . JText::_('JPREV') . '</a>' . PHP_EOL;
	echo '	</li>' . PHP_EOL;
}

if ($row->next)
{
	echo '	<li class="next">' . PHP_EOL;
	echo '		<a href="' . $row->next . '" rel="next">' . JText::_('JNEXT') . $pnSpace . JText::_('JGLOBAL_GT') . '</a>' . PHP_EOL;
	echo '	</li>' . PHP_EOL;
}

echo '</ul>' . PHP_EOL;
