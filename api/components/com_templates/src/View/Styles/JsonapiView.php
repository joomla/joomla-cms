<?php

/**
 * @package     Joomla.API
 * @subpackage  com_templates
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Api\View\Styles;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Router\Exception\RouteNotFoundException;

/**
 * The styles view
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
        'template',
        'client_id',
        'home',
        'title',
        'params',
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
        'template',
        'title',
        'home',
        'client_id',
        'language_title',
        'image',
        'language_sef',
        'assigned',
        'e_id',
    ];

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
        if ($item->client_id != $this->getModel()->getState('client_id')) {
            throw new RouteNotFoundException('Item does not exist');
        }

        return parent::prepareItem($item);
    }
}
