<?php
/**
 * @package     Joomla.API
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Api\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\MVC\Model\ListModelInterface;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Component\Media\Administrator\Provider\ProviderManagerHelperTrait;

/**
 * Media web service model supporting lists of media adapters.
 *
 * @since  4.1.0
 */
class AdaptersModel extends BaseModel implements ListModelInterface
{
    use ProviderManagerHelperTrait;

    /**
     * A hacky way to enable the standard jsonapiView::displayList() to create a Pagination object,
     * since com_media's ApiModel does not support pagination as we know from regular ListModel derived models.
     *
     * @var    int
     * @since  4.1.0
     */
    private $total = 0;

    /**
     * Method to get a list of files and/or folders.
     *
     * @return  array  An array of data items.
     *
     * @since   4.1.0
     */
    public function getItems(): array
    {
        $adapters = [];
        foreach ($this->getProviderManager()->getProviders() as $provider)
        {
            foreach ($provider->getAdapters() as $adapter)
            {
                $obj              = new \stdClass();
                $obj->id          = $provider->getID() . '-' . $adapter->getAdapterName();
                $obj->provider_id = $provider->getID();
                $obj->name        = $adapter->getAdapterName();
                $obj->path        = $provider->getID() . '-' . $adapter->getAdapterName() . ':/';

                $adapters[] = $obj;
            }
        }

        // A hacky way to enable the standard jsonapiView::displayList() to create a Pagination object.
        $this->total = \count($adapters);

        return $adapters;
    }

    /**
     * Method to get a \JPagination object for the data set.
     *
     * @return  Pagination  A Pagination object for the data set.
     *
     * @since   4.1.0
     */
    public function getPagination(): Pagination
    {
        return new Pagination($this->getTotal(), $this->getStart(), 0);
    }

    /**
     * Method to get the starting number of items for the data set. Because com_media's ApiModel
     * does not support pagination as we know from regular ListModel derived models,
     * we always start at the top.
     *
     * @return  integer  The starting number of items available in the data set.
     *
     * @since   4.1.0
     */
    public function getStart(): int
    {
        return 0;
    }

    /**
     * Method to get the total number of items for the data set.
     *
     * @return  integer  The total number of items available in the data set.
     *
     * @since   4.1.0
     */
    public function getTotal(): int
    {
        return $this->total;
    }
}
