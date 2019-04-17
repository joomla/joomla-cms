<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

defined('JPATH_PLATFORM') or die;

/**
 * Base feed View class for a category
 *
 * @since  3.2
 */
class CategoryFeedView extends HtmlView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		$app      = \JFactory::getApplication();
		$document = \JFactory::getDocument();

		$extension      = $app->input->getString('option');
		$contentType = $extension . '.' . $this->viewName;

		$ucmType = new \JUcmType;
		$ucmRow = $ucmType->getTypeByAlias($contentType);
		$ucmMapCommon = json_decode($ucmRow->field_mappings)->common;
		$createdField = null;
		$titleField = null;

		if (is_object($ucmMapCommon))
		{
			$createdField = $ucmMapCommon->core_created_time;
			$titleField = $ucmMapCommon->core_title;
		}
		elseif (is_array($ucmMapCommon))
		{
			$createdField = $ucmMapCommon[0]->core_created_time;
			$titleField = $ucmMapCommon[0]->core_title;
		}

		$document->link = \JRoute::_(\JHelperRoute::getCategoryRoute($app->input->getInt('id'), $language = 0, $extension));

		$app->input->set('limit', $app->get('feed_limit'));
		$siteEmail        = $app->get('mailfrom');
		$fromName         = $app->get('fromname');
		$feedEmail        = $app->get('feed_email', 'none');
		$document->editor = $fromName;

		if ($feedEmail !== 'none')
		{
			$document->editorEmail = $siteEmail;
		}

		// Get some data from the model
		$items    = $this->get('Items');
		$category = $this->get('Category');

		// Don't display feed if category id missing or non existent
		if ($category == false || $category->alias === 'root')
		{
			return \JError::raiseError(404, \JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		foreach ($items as $item)
		{
			$this->reconcileNames($item);

			// Strip html from feed item title
			if ($titleField)
			{
				$title = $this->escape($item->$titleField);
				$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');
			}
			else
			{
				$title = '';
			}

			// URL link to article
			$router = new \JHelperRoute;
			$link   = \JRoute::_($router->getRoute($item->id, $contentType, null, null, $item->catid));

			// Strip HTML from feed item description text.
			$description = $item->description;
			$author      = $item->created_by_alias ?: $item->author;

			if ($createdField)
			{
				$date = isset($item->$createdField) ? date('r', strtotime($item->$createdField)) : '';
			}
			else
			{
				$date = '';
			}

			// Load individual item creator class.
			$feeditem              = new \JFeedItem;
			$feeditem->title       = $title;
			$feeditem->link        = $link;
			$feeditem->description = $description;
			$feeditem->date        = $date;
			$feeditem->category    = $category->title;
			$feeditem->author      = $author;

			// We don't have the author email so we have to use site in both cases.
			if ($feedEmail === 'site')
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

	/**
	 * Method to reconcile non standard names from components to usage in this class.
	 * Typically overriden in the component feed view class.
	 *
	 * @param   object  $item  The item for a feed, an element of the $items array.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function reconcileNames($item)
	{
		if (!property_exists($item, 'title') && property_exists($item, 'name'))
		{
			$item->title = $item->name;
		}
	}
}
