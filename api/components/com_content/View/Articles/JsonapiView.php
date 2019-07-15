<?php
/**
 * @package     Joomla.API
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Api\View\Articles;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * The article view
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
		'typeAlias',
		'asset_id',
		'title',
		'text',
		'state',
		'catid',
		'created'
	];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [
		'id',
		'typeAlias',
		'asset_id',
		'title',
		'text',
		'state',
		'catid',
		'created'
	];

	/**
	 * Execute and display a template script.
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function displayList()
	{
		$fields = [];

		foreach (FieldsHelper::getFields('com_content.article') as $field)
		{
			$fields[] = $field->name;
		}

		$this->fieldsToRenderList = array_merge($this->fieldsToRenderList, $fields);

		return parent::displayList();
	}

	/**
	 * Execute and display a template script.
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function displayItem()
	{
		$fields = [];

		foreach (FieldsHelper::getFields('com_content.article') as $field)
		{
			$fields[] = $field->name;
		}

		$this->fieldsToRenderItem = array_merge($this->fieldsToRenderItem, $fields);

		return parent::displayItem();
	}

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
		$item->text = $item->introtext . ' ' . $item->fulltext;

		// Process the content plugins.
		PluginHelper::importPlugin('content');
		Factory::getApplication()->triggerEvent('onContentPrepare', array('com_content.article', &$item, &$item->params));

		foreach (FieldsHelper::getFields('com_content.article', $item, true) as $field)
		{
			$item->{$field->name} = isset($field->apivalue) ? $field->apivalue : $field->rawvalue;
		}

		return parent::prepareItem($item);
	}
}
