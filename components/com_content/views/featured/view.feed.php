<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Frontpage View class
 *
 * @since  1.5
 */
class ContentViewFeatured extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		// Parameters
		$app       = JFactory::getApplication();
		$doc       = JFactory::getDocument();
		$params    = $app->getParams();
		$feedEmail = $app->get('feed_email', 'none');
		$siteEmail = $app->get('mailfrom');
		$doc->link = JRoute::_('index.php?option=com_content&view=featured');

		// Get some data from the model
		$app->input->set('limit', $app->get('feed_limit'));
		$categories = JCategories::getInstance('Content');
		$rows       = $this->get('Items');

		foreach ($rows as $row)
		{
			// Strip html from feed item title
			$title = $this->escape($row->title);
			$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

			// Compute the article slug
			$row->slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;

			// URL link to article
			$link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid, $row->language));

			// Get row fulltext
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('fulltext'))
				->from($db->quoteName('#__content'))
				->where($db->quoteName('id') . ' = ' . $row->id);
			$db->setQuery($query);
			$row->fulltext = $db->loadResult();

			$description = '';
			$obj = json_decode($row->images);
			$introImage = isset($obj->{'image_intro'}) ? $obj->{'image_intro'} : '';

			if (isset($introImage) && ($introImage != ''))
			{
				$image = preg_match('/http/', $introImage)? $introImage : JURI::root() . $introImage;
				$description = '<p><img src="' . $image . '"></p>';
			}

			$description .= ($params->get('feed_summary', 0) ? $row->introtext . $row->fulltext : $row->introtext);
			$author      = $row->created_by_alias ?: $row->author;

			// Load individual item creator class
			$item           = new JFeedItem;
			$item->title    = $title;
			$item->link     = $link;
			$item->date     = $row->publish_up;
			$item->category = array();

			// All featured articles are categorized as "Featured"
			$item->category[] = JText::_('JFEATURED');

			for ($item_category = $categories->get($row->catid); $item_category !== null; $item_category = $item_category->getParent())
			{
				// Only add non-root categories
				if ($item_category->id > 1)
				{
					$item->category[] = $item_category->title;
				}
			}

			$item->author = $author;

			if ($feedEmail === 'site')
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
				$description .= '<p class="feed-readmore"><a target="_blank" href ="' . $item->link . '">' . JText::_('COM_CONTENT_FEED_READMORE') . '</a></p>';
			}

			// Load item description and add div
			$item->description = '<div class="feed-description">' . $description . '</div>';

			// Loads item info into rss array
			$doc->addItem($item);
		}
	}
}
