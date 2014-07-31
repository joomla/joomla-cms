<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_contact
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Contact component
 *
 * @package		Joomla.Site
 * @subpackage	com_contact
 * @since 1.5
 */
class ContactViewCategory extends JViewLegacy
{
	function display($tpl = null)
	{
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$app = JFactory::getApplication();

		$doc	= JFactory::getDocument();
		$params = $app->getParams();
		$feedEmail	= $app->getCfg('feed_email', 'author');
		$siteEmail	= $app->getCfg('mailfrom');
		$fromName = $app->getCfg('fromname');

		JRequest::setVar('limit', $app->getCfg('feed_limit'));
		// Get some data from the models
		$category	= $this->get('Category');
		$rows		= $this->get('Items');

		$doc->link = JRoute::_(ContactHelperRoute::getCategoryRoute($category->id));

		foreach ($rows as $row)
		{
			// strip html from feed item title
			$title = $this->escape($row->name);
			$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

			// Compute the contact slug
			$row->slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;

			// url link to article
			$link = JRoute::_(ContactHelperRoute::getContactRoute($row->slug, $row->catid));

			$description	= $row->address;
			$author			= $row->created_by_alias ? $row->created_by_alias : $row->author;
			@$date			= ($row->created ? date('r', strtotime($row->created)) : '');

			// load individual item creator class
			$item = new JFeedItem();
			$item->title		= $title;
			$item->link			= $link;
			$item->description	= $description;
			$item->date			= $date;
			$item->category		= $category->title;
			$item->author		= $author;

			// We don't have the author email so we have to use site in both cases.
			if ($feedEmail == 'site')
			{
				$item->authorEmail = $siteEmail;
			}
			elseif($feedEmail == 'author')
			{
				$item->authorEmail = $row->author_email;
			}

			// loads item info into rss array
			$doc->addItem($item);
		}
	}
}
