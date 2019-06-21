<?php
/**
 * @package     Joomla.API
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Api\View\Categories;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * The categories view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render in the documents
	 *
	 * @var  string
	 * @since  4.0.0
	 */
	protected $fieldsToRender = [
		'id',
		'title',
		'alias',
		'note',
		'published',
		'access',
		'checked_out',
		'checked_out_time',
		'created_user_id',
		'parent_id',
		'level',
		'lft',
		'rgt',
		'language',
		'language_title',
		'language_image',
		'editor',
		'access_level',
		'author_name',
		'count_trashed',
		'count_unpublished',
		'count_published',
		'count_archived'
	];
}
