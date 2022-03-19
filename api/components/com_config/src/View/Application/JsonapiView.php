<?php
/**
 * @package     Joomla.API
 * @subpackage  com_config
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Api\View\Application;

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Config\Administrator\Model\ApplicationModel;
use Tobscure\JsonApi\Collection;

/**
 * The application view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
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
		/** @var ApplicationModel $model */
		$model = $this->getModel();
		$items = [];

		foreach ($model->getData() as $key => $value)
		{
			$item    = (object) [$key => $value];
			$items[] = $this->prepareItem($item);
		}

		// Set up links for pagination
		$currentUrl = Uri::getInstance();
		$currentPageDefaultInformation = ['offset' => 0, 'limit' => 20];
		$currentPageQuery = $currentUrl->getVar('page', $currentPageDefaultInformation);

		$offset              = $currentPageQuery['offset'];
		$limit               = $currentPageQuery['limit'];
		$totalItemsCount     = \count($items);
		$totalPagesAvailable = ceil($totalItemsCount / $limit);

		$items = array_splice($items, $offset, $limit);

		$this->document->addMeta('total-pages', $totalPagesAvailable)
			->addLink('self', (string) $currentUrl);

		// Check for first and previous pages
		if ($offset > 0)
		{
			$firstPage = clone $currentUrl;
			$firstPageQuery = $currentPageQuery;
			$firstPageQuery['offset'] = 0;
			$firstPage->setVar('page', $firstPageQuery);

			$previousPage = clone $currentUrl;
			$previousPageQuery = $currentPageQuery;
			$previousOffset = $currentPageQuery['offset'] - $limit;
			$previousPageQuery['offset'] = $previousOffset >= 0 ? $previousOffset : 0;
			$previousPage->setVar('page', $previousPageQuery);

			$this->document->addLink('first', $this->queryEncode((string) $firstPage))
				->addLink('previous', $this->queryEncode((string) $previousPage));
		}

		// Check for next and last pages
		if ($offset + $limit < $totalItemsCount)
		{
			$nextPage = clone $currentUrl;
			$nextPageQuery = $currentPageQuery;
			$nextOffset = $currentPageQuery['offset'] + $limit;
			$nextPageQuery['offset'] = ($nextOffset > ($totalPagesAvailable * $limit)) ? $totalPagesAvailable - $limit : $nextOffset;
			$nextPage->setVar('page', $nextPageQuery);

			$lastPage = clone $currentUrl;
			$lastPageQuery = $currentPageQuery;
			$lastPageQuery['offset'] = ($totalPagesAvailable - 1) * $limit;
			$lastPage->setVar('page', $lastPageQuery);

			$this->document->addLink('next', $this->queryEncode((string) $nextPage))
				->addLink('last', $this->queryEncode((string) $lastPage));
		}

		$collection = (new Collection($items, new JoomlaSerializer($this->type)));

		// Set the data into the document and render it
		$this->document->setData($collection);

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
		$item->id = ExtensionHelper::getExtensionRecord('joomla', 'file')->extension_id;

		return $item;
	}
}
