<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Content component
 *
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.5
 */
class ContentViewCategory extends JViewLegacy
{
	function display($tpl = null)
	{
		$app       = JFactory::getApplication();
		$doc       = JFactory::getDocument();
		$params    = $app->getParams();
		$feedEmail = $app->getCfg('feed_email', 'author');
		$siteEmail = $app->getCfg('mailfrom');

		// Get some data from the model
		$app->input->set('limit', $app->getCfg('feed_limit'));
		$category = $this->get('Category');
		$rows     = $this->get('Items');

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
			$db = JFactory::getDBO();
			$query = 'SELECT' .$db->quoteName('fulltext'). 'FROM #__content WHERE id ='.$row->id;
			$db->setQuery($query);
			$row->fulltext = $db->loadResult();

			// Get description, author and date
			$description = ($params->get('feed_summary', 0) ? $row->introtext.$row->fulltext : $row->introtext);
			$author = $row->created_by_alias ? $row->created_by_alias : $row->author;
			@$date = ($row->created ? date('r', strtotime($row->created)) : '');

			// Load individual item creator class
			$item           = new JFeedItem;
			$item->title    = $title;
			$item->link     = $link;
			$item->date     = $date;
			$item->category = $row->category_title;
			$item->author   = $author;

			if ($feedEmail == 'site')
			{
				$item->authorEmail = $siteEmail;
			}
			elseif ($feedEmail === 'author')
			{
				$item->authorEmail = $row->author_email;
			}

			// Add readmore link to description if introtext is shown, show_readmore is true and fulltext exists
			if (!$params->get('feed_summary', 0) && $params->get('feed_show_readmore', 0) && $row->fulltext)
			{
				$description .= '<p class="feed-readmore"><a target="_blank" href ="'.rtrim(JURI::base(), "/").str_replace(' ', '%20', $item->link).'">'.JText::_('COM_CONTENT_FEED_READMORE').'</a></p>';
			}

			// Load item description and add div
			$item->description	= '<div class="feed-description">'.$description.'</div>';

			// Loads item info into rss array
			$doc->addItem($item);
		}
	}
}
