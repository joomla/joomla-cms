<?php
/**
 * @package     Joomla.API
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Api\View\Feeds;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * The feeds view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render item in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderItem = [
		'id',
		'catid',
		'name',
		'alias',
		'link',
		'published',
		'numarticles',
		'cache_time',
		'checked_out',
		'checked_out_time',
		'ordering',
		'rtl',
		'access',
		'language',
		'params',
		'created',
		'created_by',
		'created_by_alias',
		'modified',
		'modified_by',
		'metakey',
		'metadesc',
		'metadata',
		'publish_up',
		'publish_down',
		'description',
		'version',
		'hits',
		'images',
		'tags',
	];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [
		'id',
		'name',
		'alias',
		'checked_out',
		'checked_out_time',
		'catid',
		'numarticles',
		'cache_time',
		'created_by',
		'published',
		'access',
		'ordering',
		'language',
		'publish_up',
		'publish_down',
		'language_title',
		'language_image',
		'editor',
		'access_level',
		'category_title',
	];
}
