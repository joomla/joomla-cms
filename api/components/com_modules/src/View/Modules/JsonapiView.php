<?php

/**
 * @package     Joomla.API
 * @subpackage  com_modules
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Api\View\Modules;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\Component\Modules\Administrator\Model\SelectModel;

/**
 * The modules view
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
        'note',
        'content',
        'ordering',
        'position',
        'checked_out',
        'checked_out_time',
        'publish_up',
        'publish_down',
        'published',
        'module',
        'access',
        'showtitle',
        'params',
        'client_id',
        'language',
        'assigned',
        'assignment',
        'xml',
    ];

    /**
     * The fields to render items in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderList = [
        'id',
        'title',
        'note',
        'position',
        'module',
        'language',
        'checked_out',
        'checked_out_time',
        'published',
        'enabled',
        'access',
        'ordering',
        'publish_up',
        'publish_down',
        'language_title',
        'language_image',
        'editor',
        'access_level',
        'pages',
        'name',
    ];

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
        /** @var \Joomla\CMS\MVC\Model\AdminModel $model */
        $model = $this->getModel();

        if ($item === null) {
            $item  = $this->prepareItem($model->getItem());
        }

        if ($item->id === null) {
            throw new RouteNotFoundException('Item does not exist');
        }

        if ((int) $model->getState('client_id') !== $item->client_id) {
            throw new RouteNotFoundException('Item does not exist');
        }

        return parent::displayItem($item);
    }

    /**
     * Execute and display a list modules types.
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function displayListTypes()
    {
        /** @var SelectModel $model */
        $model = $this->getModel();
        $items = [];

        foreach ($model->getItems() as $item) {
            $item->id = $item->extension_id;
            unset($item->extension_id);

            $items[] = $item;
        }

        $this->fieldsToRenderList = ['id', 'name', 'module', 'xml', 'desc'];

        return parent::displayList($items);
    }
}
