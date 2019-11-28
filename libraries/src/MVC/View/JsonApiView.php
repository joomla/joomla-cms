<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

\defined('_JEXEC') or die;

use Joomla\CMS\Document\JsonapiDocument;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Resource;
use Tobscure\JsonApi\AbstractSerializer;

/**
 * Base class for a Joomla Json List View
 *
 * Class holding methods for displaying presentation data.
 *
 * @since  4.0.0
 */
abstract class JsonApiView extends JsonView
{
	/**
	 * The active document object (Redeclared for typehinting)
	 *
	 * @var    JsonapiDocument
	 * @since  3.0
	 */
	public $document;

	/**
	 * The content type
	 *
	 * @var  string
	 */
	protected $type;

	/**
	 * Item relationship
	 *
	 * @var  array
	 *
	 * @since  4.0
	 */
	protected $relationship = [];

	/**
	 * Serializer data
	 *
	 * @var    AbstractSerializer
	 * @since  4.0
	 */
	protected $serializer;

	/**
	 * The fields to render item in the documents
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderItem = [];

	/**
	 * The fields to render items in the documents
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [];

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
		if (\array_key_exists('contentType', $config))
		{
			$this->type = $config['contentType'];
		}

		if ($this->serializer === null)
		{
			$this->serializer = new JoomlaSerializer($this->type);
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
		/** @var \Joomla\CMS\MVC\Model\ListModel $model */
		$model = $this->getModel();

		if ($items === null)
		{
			$items = [];

			foreach ($model->getItems() as $item)
			{
				$items[] = $this->prepareItem($item);
			}
		}

		$pagination = $model->getPagination();

		// Check for errors.
		if (\count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		if ($this->type === null)
		{
			throw new \RuntimeException('Content type missing');
		}

		// Set up links for pagination
		$currentUrl = Uri::getInstance();
		$currentPageDefaultInformation = array('offset' => $pagination->limitstart, 'limit' => $pagination->limit);
		$currentPageQuery = $currentUrl->getVar('page', $currentPageDefaultInformation);
		$totalPagesAvailable = ($pagination->pagesTotal * $pagination->limit);

		$firstPage = clone $currentUrl;
		$firstPageQuery = $currentPageQuery;
		$firstPageQuery['offset'] = 0;
		$firstPage->setVar('page', $firstPageQuery);

		$nextPage = clone $currentUrl;
		$nextPageQuery = $currentPageQuery;
		$nextOffset = $currentPageQuery['offset'] + $pagination->limit;
		$nextPageQuery['offset'] = ($nextOffset > ($totalPagesAvailable * $pagination->limit)) ? $totalPagesAvailable - $pagination->limit : $nextOffset;
		$nextPage->setVar('page', $nextPageQuery);

		$previousPage = clone $currentUrl;
		$previousPageQuery = $currentPageQuery;
		$previousOffset = $currentPageQuery['offset'] - $pagination->limit;
		$previousPageQuery['offset'] = $previousOffset >= 0 ? $previousOffset : 0;
		$previousPage->setVar('page', $previousPageQuery);

		$lastPage = clone $currentUrl;
		$lastPageQuery = $currentPageQuery;
		$lastPageQuery['offset'] = $totalPagesAvailable - $pagination->limit;
		$lastPage->setVar('page', $lastPageQuery);

		$collection = (new Collection($items, $this->serializer))
			->fields([$this->type => $this->fieldsToRenderList]);

		// Set the data into the document and render it
		$this->document->addMeta('total-pages', $pagination->pagesTotal)
			->setData($collection)
			->addLink('self', (string) $currentUrl)
			->addLink('first', (string) $firstPage)
			->addLink('next', (string) $nextPage)
			->addLink('previous', (string) $previousPage)
			->addLink('last', (string) $lastPage);

		return $this->document->render();
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
		if ($item === null)
		{
			/** @var \Joomla\CMS\MVC\Model\AdminModel $model */
			$model = $this->getModel();
			$item  = $this->prepareItem($model->getItem());
		}

		if ($item->id === null)
		{
			throw new RouteNotFoundException('Item does not exist');
		}

		// Check for errors.
		if (\count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		if ($this->type === null)
		{
			throw new \RuntimeException('Content type missing');
		}

		$element = (new Resource($item, $this->serializer))
			->fields([$this->type => $this->fieldsToRenderItem]);

		if (!empty($this->relationship))
		{
			$element->with($this->relationship);
		}

		$this->document->setData($element);
		$this->document->addLink('self', Uri::current());

		return $this->document->render();
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
		return $item;
	}
}
