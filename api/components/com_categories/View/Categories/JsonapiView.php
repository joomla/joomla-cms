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
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * The categories view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render item in the documents
	 *
	 * @var  string
	 * @since  4.0.0
	 */
	protected $fieldsToRenderItem = [
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
		'count_archived',
		'fields'
	];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  string
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [
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
		'count_archived',
		'fields'
	];

	/**
	 * Prepare item before render.
	 *
	 * @param   object  $item  The model item
	 *
	 * @return  object
	 *
	 * @since   4.0.0
	 */
	protected function prepareItem($item)
	{
		$fields = [];

		foreach (FieldsHelper::getFields('com_content.categories', $item, true) as $field)
		{
			$fields[] = [
				'id'       => $field->id,
				'title'    => $field->title,
				'name'     => $field->name,
				'value'    => trim($field->value),
				'rawvalue' => $field->rawvalue,
			];
		}

		$item->fields = $fields;

		return parent::prepareItem($item);
	}
}
