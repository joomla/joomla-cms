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

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\Exception\ResourceNotFound;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\MVC\Model\ListModelInterface;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Component\Media\Administrator\Exception\FileNotFoundException;
use Joomla\Component\Media\Administrator\Model\ApiModel;
use Joomla\Component\Media\Administrator\Provider\ProviderManagerHelperTrait;

/**
 * Media web service model supporting lists of media items.
 *
 * @since  4.1.0
 */
class MediaModel extends BaseModel implements ListModelInterface
{
    use ProviderManagerHelperTrait;

    /**
     * Instance of com_media's ApiModel
     *
     * @var ApiModel
     * @since  4.1.0
     */
    private $mediaApiModel;

    /**
     * A hacky way to enable the standard jsonapiView::displayList() to create a Pagination object,
     * since com_media's ApiModel does not support pagination as we know from regular ListModel derived models.
     *
     * @var int
     * @since  4.1.0
     */
    private $total = 0;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->mediaApiModel = new ApiModel();
    }

    /**
     * Method to get a list of files and/or folders.
     *
     * @return  array  An array of data items.
     *
     * @since   4.1.0
     */
    public function getItems(): array
    {
        // Map web service model state to com_media options.
        $options = [
            'url'       => $this->getState('url', false),
            'temp'      => $this->getState('temp', false),
            'search'    => $this->getState('search', ''),
            'recursive' => $this->getState('search_recursive', false),
            'content'   => $this->getState('content', false),
        ];

        ['adapter' => $adapterName, 'path' => $path] = $this->resolveAdapterAndPath($this->getState('path', ''));
        try
        {
            $files = $this->mediaApiModel->getFiles($adapterName, $path, $options);
        }
        catch (FileNotFoundException $e)
        {
            throw new ResourceNotFound(
                Text::sprintf('WEBSERVICE_COM_MEDIA_FILE_NOT_FOUND', $path),
                404
            );
        }

        /**
         * A hacky way to enable the standard jsonapiView::displayList() to create a Pagination object.
         * Because com_media's ApiModel does not support pagination as we know from regular ListModel
         * derived models, we always return all retrieved items.
         */
        $this->total = \count($files);

        return $files;
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
     * @return  int  The starting number of items available in the data set.
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
     * @return  int  The total number of items available in the data set.
     *
     * @since   4.1.0
     */
    public function getTotal(): int
    {
        return $this->total;
    }
}
