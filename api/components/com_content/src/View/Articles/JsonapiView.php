<?php
/**
 * @package     Joomla.API
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Api\View\Articles;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Content\Api\Serializer\ContentSerializer;
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
		'tags',
		'language',
		'state',
		'category',
		'created',
		'author',
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
		'tags',
		'language',
		'state',
		'category',
		'created',
		'author',
	];

	/**
	 * The relationships the item has
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $relationship = [
		'category',
		'author',
	];

	/**
	 * Constructor.
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 *                          contentType: the name (optional) of the content type to use for the serialization
	 *
	 * @since   4.0.0
	 */
	public function __construct($config = [])
	{
		if (array_key_exists('contentType', $config))
		{
			$this->serializer = new ContentSerializer($config['contentType']);
		}

		parent::__construct($config);
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   array|null  $items  Array of items
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function displayList(array $items = null)
	{
		foreach (FieldsHelper::getFields('com_content.article') as $field)
		{
			$this->fieldsToRenderList[] = $field->name;
		}

		return parent::displayList();
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   object  $item  Item
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function displayItem($item = null)
	{
		foreach (FieldsHelper::getFields('com_content.article') as $field)
		{
			$this->fieldsToRenderItem[] = $field->name;
		}

		if (Multilanguage::isEnabled())
		{
			$this->fieldsToRenderItem[] = 'languageAssociations';
			$this->relationship[]       = 'languageAssociations';
		}

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
		Factory::getApplication()->triggerEvent('onContentPrepare', ['com_content.article', &$item, &$item->params]);

		foreach (FieldsHelper::getFields('com_content.article', $item, true) as $field)
		{
			$item->{$field->name} = isset($field->apivalue) ? $field->apivalue : $field->rawvalue;
		}

		if (Multilanguage::isEnabled() && !empty($item->associations))
		{
			$associations = [];

			foreach ($item->associations as $language => $association)
			{
				$itemId = explode(':', $association)[0];

				$associations[] = (object) [
					'id'       => $itemId,
					'language' => $language,
				];
			}

			$item->associations = $associations;
		}

		if (!empty($item->tags->tags))
		{
			$tagsIds   = explode(',', $item->tags->tags);
			$tagsNames = $item->tagsHelper->getTagNames($tagsIds);

			$item->tags = array_combine($tagsIds, $tagsNames);
		}
		else
		{
			$item->tags = [];
		}

		return parent::prepareItem($item);
	}
}
