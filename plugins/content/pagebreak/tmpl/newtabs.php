<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagebreak
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$t[] = $text[0];
$t[] = JHtml::_('bootstrap.startTabSet', 'pageBreak', array('active' => 'article-1'));

foreach ($text as $key => $subtext)
{
	if ($key >= 1)
	{
		$match = $matches[$key - 1];
		$match = (array) JUtility::parseAttributes($match[0]);

		if (isset($match['alt']))
		{
			$title = stripslashes($match['alt']);
		}
		elseif (isset($match['title']))
		{
			$title = stripslashes($match['title']);
		}
		else
		{
			$title = JText::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $key + 1);
		}

		$t[] = JHtml::_('bootstrap.addTab', 'pageBreak', 'article-' . $key, $title);
		$t[] = (string) $subtext;
		$t[] = JHtml::_('bootstrap.endTab');
	}
}

$t[] = JHtml::_('bootstrap.endTabSet');

$row->text = implode(' ', $t);
