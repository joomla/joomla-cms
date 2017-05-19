<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Tags\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Controller\Controller;
use Joomla\CMS\Helper\TagsHelper;

/**
 * The Tags List Controller
 *
 * @since  3.1
 */
class Tags extends Controller
{
	/**
	 * Method to search tags with A\JAX
	 *
	 * @return  void
	 */
	public function searchAjax()
	{
		// Receive request data
		$filters = array(
			'like'      => trim($this->input->get('like', null)),
			'title'     => trim($this->input->get('title', null)),
			'flanguage' => $this->input->get('flanguage', null),
			'published' => $this->input->get('published', 1, 'int'),
			'parent_id' => $this->input->get('parent_id', null)
		);

		if ($results = TagsHelper::searchTags($filters))
		{
			// Output a JSON object
			echo json_encode($results);
		}

		$this->app->close();
	}
}
