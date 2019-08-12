<?php
/**
 * @package     Joomla.API
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Api\View\Items;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
use Tobscure\JsonApi\Collection;

/**
 * The items view
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
	protected $fieldsToRenderItem = [];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList  = [];

	/**
	 * Execute and display a list items types.
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function displayListTypes()
	{
		/** @var \Joomla\Component\Menus\Administrator\Model\MenutypesModel $model */
		$model = $this->getModel();
		$items = [];

		$model->setState('client_id', 0);

		foreach ($model->getTypeOptions() as $type => $data)
		{
			$groupItems = [];

			foreach ($data as $item)
			{
				$item->id          = implode('/', $item->request);
				$item->title       = Text::_($item->title);
				$item->description = Text::_($item->description);
				$item->group       = Text::_($type);

				$groupItems[] = $item;
			}

			$items = array_merge($items, $groupItems);
		}

		// Set up links for pagination
		$currentUrl = Uri::getInstance();
		$currentPageDefaultInformation = ['offset' => 0, 'limit' => 20];
		$currentPageQuery = $currentUrl->getVar('page', $currentPageDefaultInformation);

		$offset              = $currentPageQuery['offset'];
		$limit               = $currentPageQuery['limit'];
		$totalItemsCount     = count($items);
		$totalPagesAvailable = ceil($totalItemsCount / $limit);

		$items = array_splice($items, $offset, $limit);

		$firstPage = clone $currentUrl;
		$firstPageQuery = $currentPageQuery;
		$firstPageQuery['offset'] = 0;
		$firstPage->setVar('page', $firstPageQuery);

		$nextPage = clone $currentUrl;
		$nextPageQuery = $currentPageQuery;
		$nextOffset = $currentPageQuery['offset'] + $limit;
		$nextPageQuery['offset'] = ($nextOffset > ($totalPagesAvailable * $limit)) ? $totalPagesAvailable - $limit : $nextOffset;
		$nextPage->setVar('page', $nextPageQuery);

		$previousPage = clone $currentUrl;
		$previousPageQuery = $currentPageQuery;
		$previousOffset = $currentPageQuery['offset'] - $limit;
		$previousPageQuery['offset'] = $previousOffset >= 0 ? $previousOffset : 0;
		$previousPage->setVar('page', $previousPageQuery);

		$lastPage = clone $currentUrl;
		$lastPageQuery = $currentPageQuery;
		$lastPageQuery['offset'] = $totalPagesAvailable - $limit;
		$lastPage->setVar('page', $lastPageQuery);

		$collection = (new Collection($items, new JoomlaSerializer('menutypes')));

		// Set the data into the document and render it
		$this->document->addMeta('total-pages', $totalPagesAvailable)
			->setData($collection)
			->addLink('self', (string) $currentUrl)
			->addLink('first', (string) $firstPage)
			->addLink('next', (string) $nextPage)
			->addLink('previous', (string) $previousPage)
			->addLink('last', (string) $lastPage);

		return $this->document->render();
	}
}
