<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Tags component all tags view
 *
 * @since  3.1
 */
class TagsViewTags extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$app            = JFactory::getApplication();
		$document       = JFactory::getDocument();
		$document->link = JRoute::_('index.php?option=com_tags&view=tags');

		$app->input->set('limit', $app->get('feed_limit'));
		$siteEmail        = $app->get('mailfrom');
		$fromName         = $app->get('fromname');
		$feedEmail        = $app->get('feed_email', 'author');
		$document->editor = $fromName;

		if ($feedEmail != "none")
		{
			$document->editorEmail = $siteEmail;
		}

		// Get some data from the model
		$items = $this->get('Items');

		foreach ($items as $item)
		{
			// Strip HTML from feed item title
			$title = $this->escape($item->title);
			$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');
			$date = ($item->created_time ? date('r', strtotime($item->created_time)) : '');

			// Load individual item creator class
			$feeditem = new JFeedItem;
			$feeditem->title       = $title;
			$feeditem->link        = JRoute::_(TagsHelperRoute::getTagRoute($item->id));
			$feeditem->description = $item->description;
			$feeditem->date        = $date;
			$feeditem->category    = 'All Tags';
			$feeditem->author      = $item->author;

			if ($feedEmail == 'site')
			{
				$feeditem->authorEmail = $siteEmail;
			}
			elseif ($feedEmail === 'author')
			{
				$feeditem->authorEmail = $item->author_email;
			}

			// Loads item info into RSS array
			$document->addItem($feeditem);
		}
	}
}
