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
 * The Tags List Controller
 *
 * @package     Joomla.Site
 * @subpackage  com_tags
 * @since       3.1
 */
class TagsControllerTags extends JControllerLegacy
{

	/**
	 * Method to search tags with AJAX
	 *
	 * @return  void
	 */
	public function searchAjax()
	{
		// Required objects
		$app = JFactory::getApplication();

		// Receive request data
		$filters = array(
			'like'      => trim($app->input->get('like', null)),
			'title'     => trim($app->input->get('title', null)),
			'flanguage' => $app->input->get('flanguage', null),
			'published' => $app->input->get('published', 1, 'integer'),
			'parent_id' => $app->input->get('parent_id', null)
		);

		if ($results = JHelperTags::searchTags($filters))
		{
			// Output a JSON object
			echo json_encode($results);
		}

		$app->close();
	}

}
