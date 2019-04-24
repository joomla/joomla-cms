<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

defined('_JEXEC') or die;

use Joomla\CMS\Document\JsonapiDocument;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
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
	 * The fields to render in the documents
	 *
	 * @var  string
	 */
	protected $fieldsToRender = [];

	/**
	 * Constructor.
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 *                          contentType: the name (optional) of the content type to use for the serialization
	 *
	 * @since   4.0.0
	 */
	public function __construct($config = array())
	{
		if (array_key_exists('contentType', $config))
		{
			$this->type = $config['contentType'];
		}

		parent::__construct($config);
	}

	/**
	 * Execute and display a template script.
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function displayList()
	{
		/** @var \Joomla\CMS\MVC\Model\ListModel $model */
		$model = $this->getModel();

		$items      = $model->getItems();
		$pagination = $model->getPagination();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
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

		$collection = (new Collection($items, new JoomlaSerializer($this->type)))
			->fields([$this->type => $this->fieldsToRender]);

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
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function displayItem()
	{
		/** @var \Joomla\CMS\MVC\Model\AdminModel $model */
		$model = $this->getModel();
		$item = $model->getItem();

		if ($item->id === null)
		{
			throw new RouteNotFoundException('Item does not exist');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		if ($this->type === null)
		{
			throw new \RuntimeException('Content type missing');
		}

		$serializer = new JoomlaSerializer($this->type);
		$element = (new Resource($item, $serializer))
			->fields([$this->type => $this->fieldsToRender]);

		$this->document->setData($element);
		$this->document->addLink('self', Uri::current());

		return $this->document->render();
	}
}
