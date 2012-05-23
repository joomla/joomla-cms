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

		$doc	= JFactory::getDocument();
		$params = $app->getParams();
		$feedEmail	= (@$app->getCfg('feed_email')) ? $app->getCfg('feed_email') : 'author';
		$siteEmail	= $app->getCfg('mailfrom');
		// Get some data from the model
		JRequest::setVar('limit', $app->getCfg('feed_limit'));
		$category	= $this->get('Category');
		$rows		= $this->get('Items');

		$doc->link = JRoute::_(ContentHelperRoute::getCategoryRoute($category->id));

		foreach ($rows as $row)
		{
			// strip html from feed item title
			$title = $this->escape($row->title);
			$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

			// Compute the article slug
			$row->slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;

			// url link to article
			$link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid));

			// strip html from feed item description text
			// TODO: Only pull fulltext if necessary (actually, just get the necessary fields).
			$description	= ($params->get('feed_summary', 0) ? $row->introtext/*.$row->fulltext*/ : $row->introtext);
			$author			= $row->created_by_alias ? $row->created_by_alias : $row->author;
			@$date			= ($row->created ? date('r', strtotime($row->created)) : '');

			// load individual item creator class
			$item = new JFeedItem();
			$item->title		= $title;
			$item->link			= $link;
			$item->description	= $description;

			/* 
			 * Add readmore to introtext
			 * 
			 * @TODO	- Language tag for readmore
						- Option in Joomla core to allow users to include readmore in feed-items
			  			- $row->fulltext fix
			 */
			
			// check if introtext is shown, else don't show readmore link (fulltext won't need readmore)
			if (!$params->get('feed_summary', 0)
				//&& ($params->get('feed_showreadmore', 0) // @TODO parameter not yet available
				) {
				
				// get row fulltext
				// temporarily solved this way because $row->fulltext isn't working, because fulltext is protected in MySQL. 
				// The `` are missing somewhere in a model I think, perhaps in /public_html/components/com_content/models/article.php
				// add it with function $db->quoteName('fulltext')
				$query = "SELECT `fulltext` FROM #__content WHERE id =".$row->id.";";
				$db =& JFactory::getDBO();
				$db->setQuery($query);
				$fulltext = $db->loadResult();
				
				// add readmore link
				if ($fulltext != '') {
				//if ($row->fulltext != '') { // delete comment and the line above, when $row->fulltext works
					$item->description .= '<br /><a class="rss-readmore" target="_blank" href ="'.rtrim(JURI::base(), "/").str_replace(' ', '%20', $item->link).'">Read more »</a>';
				}
			}
			//$item->description = $row->fulltext; //only for $row->fulltext testing

			$item->date		= $date;
			$item->category		= $row->category_title;
			$item->author		= $author;
			if ($feedEmail == 'site') {
				$item->authorEmail = $siteEmail;
			}
			else {
				$item->authorEmail = $row->author_email;
			}

			// loads item info into rss array
			$doc->addItem($item);
		}
	}
}
