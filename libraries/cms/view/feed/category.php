<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Base feed View class for a category
 *
 * @package     Joomla.Libraries
 * @subpackage  View
 * @since       3.4
 */
class JViewFeedCategory extends JViewCms
{
	/**
	 * The name of the view to link individual items to, also used for the content type
	 * Should be implemented by the feed view.
	 *
	 * @var    string  The name of the view to link individual items to
	 * @since  3.4
	 */
	protected $viewName = '';

	/**
	 * Method to render the view.
	 *
	 * @return  mixed  A string if successful, otherwise an Exception.
	 *
	 * @since  3.4
	 */
	public function render()
	{
		$app      = JFactory::getApplication();
		$document = JFactory::getDocument();

		$extension   = $app->input->getString('option');
		$contentType = $extension . '.' . $this->viewName;

		$ucmType      = new JUcmType;
		$ucmRow       = $ucmType->getTypeByAlias($contentType);
		$ucmMapCommon = json_decode($ucmRow->field_mappings)->common;
		$createdField = null;
		$titleField   = null;

		if (is_object($ucmMapCommon))
		{
			$createdField = $ucmMapCommon->core_created_time;
			$titleField   = $ucmMapCommon->core_title;
		}
		elseif (is_array($ucmMapCommon))
		{
			$createdField = $ucmMapCommon[0]->core_created_time;
			$titleField   = $ucmMapCommon[0]->core_title;
		}

		$document->link = JRoute::_(JHelperRoute::getCategoryRoute($app->input->getInt('id'), $language = 0, $extension));

		$app->input->set('limit', $app->get('feed_limit'));
		$siteEmail        = $app->get('mailfrom');
		$fromName         = $app->get('fromname');
		$feedEmail        = $app->get('feed_email', 'author');
		$document->editor = $fromName;

		if ($feedEmail != 'none')
		{
			$document->editorEmail = $siteEmail;
		}

		$data     = $this->getData();
		$items    = $data['items'];
		$category = $data['category'];

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
			$router = new JHelperRoute;
			$link   = JRoute::_($router->getRoute($item->id, $contentType, null, null, $item->catid));

			// Strip HTML from feed item description text.
			$description = $item->description;
			$author      = $item->created_by_alias ? $item->created_by_alias : $item->author;

			if ($createdField)
			{
				$date = isset($item->$createdField) ? date('r', strtotime($item->$createdField)) : '';
			}
			else
			{
				$date = '';
			}

			// Load individual item creator class.
			$feeditem              = new JFeedItem;
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

		// We have added items here directly into the JDocument, so we return an empty string.
		return '';
	}

	/**
	 * Method to reconcile non standard names from components to usage in this class.
	 * Typically overriden in the component feed view class.
	 *
	 * @param   object  $item  The item for a feed, an element of the $items array.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function reconcileNames($item)
	{
		if (!property_exists($item, 'title') && property_exists($item, 'name'))
		{
			$item->title = $item->name;
		}
	}

	/**
	 * Retrieves the data from the default model.
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function getData()
	{
		// Get some data from the model
		$model            = $this->getModel();
		$data             = array();
		$data['items']    = $model->getItems();
		$data['category'] = $model->getCategory();

		return $data;
	}
}
