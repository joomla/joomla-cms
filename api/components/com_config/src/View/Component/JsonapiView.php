<?php

/**
 * @package     Joomla.API
 * @subpackage  com_config
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Api\View\Component;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
use Tobscure\JsonApi\Collection;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The component view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
    /**
     * Execute and display a template script.
     *
     * @param   ?array  $items  Array of items
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function displayList(?array $items = null)
    {
        try {
            $component = ComponentHelper::getComponent($this->get('component_name'));

            if ($component === null || !$component->enabled) {
                // @todo: exception component unavailable
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_INVALID_COMPONENT_NAME'), 400);
            }

            $data = $component->getParams()->toObject();
        } catch (\Exception $e) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SERVER'), 500, $e);
        }

        $items = [];

        foreach ($data as $key => $value) {
            $item    = (object) [$key => $value];
            $items[] = $this->prepareItem($item);
        }

        // Set up links for pagination
        $currentUrl                    = Uri::getInstance();
        $currentPageDefaultInformation = ['offset' => 0, 'limit' => 20];
        $currentPageQuery              = $currentUrl->getVar('page', $currentPageDefaultInformation);

        $offset              = $currentPageQuery['offset'];
        $limit               = $currentPageQuery['limit'];
        $totalItemsCount     = \count($items);
        $totalPagesAvailable = ceil($totalItemsCount / $limit);

        $items = array_splice($items, $offset, $limit);

        $this->getDocument()->addMeta('total-pages', $totalPagesAvailable)
            ->addLink('self', (string) $currentUrl);

        // Check for first and previous pages
        if ($offset > 0) {
            $firstPage                = clone $currentUrl;
            $firstPageQuery           = $currentPageQuery;
            $firstPageQuery['offset'] = 0;
            $firstPage->setVar('page', $firstPageQuery);

            $previousPage                = clone $currentUrl;
            $previousPageQuery           = $currentPageQuery;
            $previousOffset              = $currentPageQuery['offset'] - $limit;
            $previousPageQuery['offset'] = max($previousOffset, 0);
            $previousPage->setVar('page', $previousPageQuery);

            $this->getDocument()->addLink('first', $this->queryEncode((string) $firstPage))
                ->addLink('previous', $this->queryEncode((string) $previousPage));
        }

        // Check for next and last pages
        if ($offset + $limit < $totalItemsCount) {
            $nextPage                = clone $currentUrl;
            $nextPageQuery           = $currentPageQuery;
            $nextOffset              = $currentPageQuery['offset'] + $limit;
            $nextPageQuery['offset'] = ($nextOffset > ($totalPagesAvailable * $limit)) ? $totalPagesAvailable - $limit : $nextOffset;
            $nextPage->setVar('page', $nextPageQuery);

            $lastPage                = clone $currentUrl;
            $lastPageQuery           = $currentPageQuery;
            $lastPageQuery['offset'] = ($totalPagesAvailable - 1) * $limit;
            $lastPage->setVar('page', $lastPageQuery);

            $this->getDocument()->addLink('next', $this->queryEncode((string) $nextPage))
                ->addLink('last', $this->queryEncode((string) $lastPage));
        }

        $collection = (new Collection($items, new JoomlaSerializer($this->type)));

        // Set the data into the document and render it
        $this->getDocument()->setData($collection);

        return $this->getDocument()->render();
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
        $item->id = ExtensionHelper::getExtensionRecord($this->component_name, 'component')->extension_id;

        return $item;
    }
}
