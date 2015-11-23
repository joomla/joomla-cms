<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumViewCategory extends JViewCategoryfeed
{

	protected $viewName = 'topic';

	protected function reconcileNames ($item)
	{
		// Get description, author and date
		$app = JFactory::getApplication();
		$params = $app->getParams();
		$item->description = $params->get('feed_summary', 0) ? $item->introtext . $item->fulltext : $item->introtext;
		
		// Add readmore link to description if introtext is shown, show_readmore
		// is true and fulltext exists
		if (! $item->params->get('feed_summary', 0) && $item->params->get('feed_show_readmore', 0) && $item->fulltext)
		{
			$item->description .= '<p class="feed-readmore"><a target="_blank" href ="' . $item->link . '">' . JText::_('COM_CJFORUM_FEED_READMORE') .
					 '</a></p>';
		}
		
		$item->author = $item->created_by_alias ? $item->created_by_alias : $item->author;
	}
}
