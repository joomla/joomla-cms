<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_contact
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Contact component
 *
 * @package		Joomla.Site
 * @subpackage	com_contact
 * @since 1.5
 */
class ContactViewCategory extends JView
{
	function display()
	{
		// Get some data from the models
		$category	= $this->get('Category');
		$rows		= $this->get('Items');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$app = JFactory::getApplication();

		$doc	= JFactory::getDocument();
		$params = $app->getParams();

		$doc->link = JRoute::_(ContactHelperRoute::getCategoryRoute($category->id));

		foreach ($rows as $row)
		{
			// strip html from feed item title
			$title = $this->escape($row->name);
			$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

			// Compute the contact slug
			$row->slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;

			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			$link = JRoute::_(ContactHelperRoute::getContactRoute($row->slug, $row->catid), false);

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
			$item->date			= $date;
			$item->category		= $row->category;

			// loads item info into rss array
			$doc->addItem($item);
		}
	}
}
