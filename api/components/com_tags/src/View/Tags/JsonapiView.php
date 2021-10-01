<?php
/**
 * @package     Joomla.API
 * @subpackage  com_tags
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Api\View\Tags;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * The tags view
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
		'parent_id',
		'level',
		'lft',
		'rgt',
		'alias',
		'typeAlias',
		'path',
		'title',
		'note',
		'description',
		'published',
		'checked_out',
		'checked_out_time',
		'access',
		'params',
		'metadesc',
		'metakey',
		'metadata',
		'created_user_id',
		'created_time',
		'created_by_alias',
		'modified_user_id',
		'modified_time',
		'images',
		'urls',
		'hits',
		'language',
		'version',
		'publish_up',
		'publish_down',
	];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [
		'id',
		'title',
		'alias',
		'note',
		'published',
		'access',
		'description',
		'checked_out',
		'checked_out_time',
		'created_user_id',
		'path',
		'parent_id',
		'level',
		'lft',
		'rgt',
		'language',
		'language_title',
		'language_image',
		'editor',
		'author_name',
		'access_title',
	];
}
