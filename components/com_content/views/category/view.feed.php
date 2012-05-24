<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Content component
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.5
 */
class ContentViewCategory extends JView
{
	function display()
	{
		$app = JFactory::getApplication();

		$doc		= JFactory::getDocument();
		$params 	= $app->getParams();
		$feedEmail	= (@$app->getCfg('feed_email')) ? $app->getCfg('feed_email') : 'author';
		$siteEmail	= $app->getCfg('mailfrom');
		
		// Get some data from the model
		JRequest::setVar('limit', $app->getCfg('feed_limit'));
		$category	= $this->get('Category');
		$rows		= $this->get('Items');

		$doc->link = JRoute::_(ContentHelperRoute::getCategoryRoute($category->id));

		foreach ($rows as $row)
		{
			// Strip html from feed item title
			$title = $this->escape($row->title);
			$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

			// Compute the article slug
			$row->slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;

			// Url link to article
			$link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid));

			// Get row fulltext
			$db 				=& JFactory::getDBO();
			$query 				= "SELECT `fulltext` FROM #__content WHERE id =".$row->id.";";
			$db->setQuery($query);
			$row->fulltext 		= $db->loadResult();

			$description		= ($params->get('feed_summary', 0) ? $row->introtext.$row->fulltext : $row->introtext);
			// TODO(?): if created_by_alias is empty, it won't show anything
			$author				= $row->created_by_alias ? $row->created_by_alias : $row->author;
			@$date				= ($row->created ? date('r', strtotime($row->created)) : '');

			// Load individual item creator class
			$item 				= new JFeedItem();
			$item->title		= $title;
			$item->link			= $link;
			$item->description	= $description;

			// Add readmore to description if introtext is shown and showreadmore is true
			if (!$params->get('feed_summary', 0) && $params->get('feed_show_readmore', 0)) {
				// Only add readmore link if there is more to read 
				if ($row->fulltext != '') {
					$item->description .= '<br /><a class="feed-readmore" target="_blank" href ="'.rtrim(JURI::base(), "/").str_replace(' ', '%20', $item->link).'">'.JText::_('COM_CONTENT_FEED_READMORE').'</a>';
				}
			}

			$item->date			= $date;
			$item->category		= $row->category_title;
			$item->author		= $author;
			// TODO: add option in Joomla core to let the authorEmail empty (Feed e-mail:none). Users will love this for spam prevention.
			$item->authorEmail	= (($feedEmail == 'site') ? $siteEmail : $row->author_email);

			// Loads item info into rss array
			$doc->addItem($item);
		}
	}
}
