<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Base feed View class for a category
 *
 * @package     Joomla.Libraries
 * @subpackage  view
 * @since       3.1.3
 */
class JViewCategoryfeed extends JViewLegacy
{
	public function display($tpl = null)
	{
		$app      = JFactory::getApplication();
		$document = JFactory::getDocument();

		$extension = $app->input->getString('option');
		$document->link = JRoute::_(JHelperRoute::getCategoryRoute($app->input->getInt('id'), $language = 0, $extension));

		$app->input->set('limit', $app->getCfg('feed_limit'));
		$siteEmail = $app->getCfg('mailfrom');
		$fromName  = $app->getCfg('fromname');
		$feedEmail = $app->getCfg('feed_email', 'author');
		$document->editor = $fromName;
		if ($feedEmail != "none")
		{
			$document->editorEmail = $siteEmail;
		}

		// Get some data from the model
		$items    = $this->get('Items');
		$category = $this->get('Category');

		foreach ($items as $item)
		{
			static::reconcileNames($item);

			// Strip html from feed item title
			$title = $this->escape($item->title);
			$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

			// URL link to article
			$link = JRoute::_(JHelperRoute::getRoute($item->id, $item->catid));

			// Strip html from feed item description text.
			$description = $item->description;
			$author			= $item->created_by_alias ? $item->created_by_alias : $item->author;
			$date = ($item->date ? date('r', strtotime($item->date)) : '');

			// Load individual item creator class.
			$feeditem = new JFeedItem;
			$feeditem->title       = $title;
			$feeditem->link        = $link;
			$feeditem->description = $description;
			$feeditem->date        = $date;
			$feeditem->category    = $category->title;
			$feeditem->author      = $author;

			// We don't have the author email so we have to use site in both cases.
			if ($feedEmail == 'site')
			{
				$feeditem->authorEmail = $siteEmail;
			}
			elseif ($feedEmail === 'author')
			{
				$feeditem->authorEmail = $item->author_email;
			}

			// Loads item information into RSS array
			$document->addItem($feeditem);
		}
	}
	/*
	 * Method to reconcile non standard names from components to usage in this class.
	 * Typically overriden in the component feed view class.
	 *
	 * @param  object  $item  The item for a feed, an element of the $items array.
	 *
	 * @since  3.1.2
	 */
	protected function reconcileNames($item)
	{
		if (!property_exists($item, 'title') && property_exists($item, 'name'))
		{
			$item->title = $item->name;
		}

	}
}
