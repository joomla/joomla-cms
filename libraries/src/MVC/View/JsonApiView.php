<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

\defined('_JEXEC') or die;

use Joomla\CMS\Document\JsonapiDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\Event\OnGetApiFields;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
use Tobscure\JsonApi\AbstractSerializer;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Resource;

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
	 * @since  4.0.0
	 */
	protected $relationship = [];

	/**
	 * Serializer data
	 *
	 * @var    AbstractSerializer
	 * @since  4.0.0
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

		// Get page query
		$currentUrl = Uri::getInstance();
		$currentPageDefaultInformation = ['offset' => 0, 'limit' => 20];
		$currentPageQuery = $currentUrl->getVar('page', $currentPageDefaultInformation);

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
		$totalItemsCount = ($pagination->pagesTotal * $pagination->limit);

		$this->document->addMeta('total-pages', $pagination->pagesTotal)
			->addLink('self', (string) $currentUrl);

		// Check for first and previous pages
		if ($pagination->limitstart > 0)
		{
			$firstPage = clone $currentUrl;
			$firstPageQuery = $currentPageQuery;
			$firstPageQuery['offset'] = 0;
			$firstPage->setVar('page', $firstPageQuery);

			$previousPage = clone $currentUrl;
			$previousPageQuery = $currentPageQuery;
			$previousOffset = $currentPageQuery['offset'] - $pagination->limit;
			$previousPageQuery['offset'] = $previousOffset >= 0 ? $previousOffset : 0;
			$previousPage->setVar('page', $previousPageQuery);

			$this->document->addLink('first', $this->queryEncode((string) $firstPage))
				->addLink('previous', $this->queryEncode((string) $previousPage));
		}

		// Check for next and last pages
		if ($pagination->limitstart + $pagination->limit < $totalItemsCount)
		{
			$nextPage = clone $currentUrl;
			$nextPageQuery = $currentPageQuery;
			$nextOffset = $currentPageQuery['offset'] + $pagination->limit;
			$nextPageQuery['offset'] = ($nextOffset > ($pagination->pagesTotal * $pagination->limit)) ? $pagination->pagesTotal - $pagination->limit : $nextOffset;
			$nextPage->setVar('page', $nextPageQuery);

			$lastPage = clone $currentUrl;
			$lastPageQuery = $currentPageQuery;
			$lastPageQuery['offset'] = ($pagination->pagesTotal - 1) * $pagination->limit;
			$lastPage->setVar('page', $lastPageQuery);

			$this->document->addLink('next', $this->queryEncode((string) $nextPage))
				->addLink('last', $this->queryEncode((string) $lastPage));
		}

		$eventData = ['type' => OnGetApiFields::LIST, 'fields' => $this->fieldsToRenderList, 'context' => $this->type];
		$event     = new OnGetApiFields('onApiGetFields', $eventData);

		/** @var OnGetApiFields $eventResult */
		$eventResult = Factory::getApplication()->getDispatcher()->dispatch('onApiGetFields', $event);

		$collection = (new Collection($items, $this->serializer))
			->fields([$this->type => $eventResult->getAllPropertiesToRender()]);

		if (!empty($this->relationship))
		{
			$collection->with($this->relationship);
		}

		// Set the data into the document and render it
		$this->document->setData($collection);

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

		$eventData = [
			'type' => OnGetApiFields::ITEM,
			'fields' => $this->fieldsToRenderItem,
			'relations' => $this->relationship,
			'context' => $this->type,
		];
		$event     = new OnGetApiFields('onApiGetFields', $eventData);

		/** @var OnGetApiFields $eventResult */
		$eventResult = Factory::getApplication()->getDispatcher()->dispatch('onApiGetFields', $event);

		$element = (new Resource($item, $this->serializer))
			->fields([$this->type => $eventResult->getAllPropertiesToRender()]);

		if (!empty($this->relationship))
		{
			$element->with($eventResult->getAllRelationsToRender());
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

	/**
	 * Encode square brackets in the URI query, according to JSON API specification.
	 *
	 * @param   string  $query  The URI query
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	protected function queryEncode($query)
	{
		return str_replace(array('[', ']'), array('%5B', '%5D'), $query);
	}
}
