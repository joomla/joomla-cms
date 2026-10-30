<?php

/**
 * @package     Joomla.API
 * @subpackage  com_guidedtours
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Api\View\Steps;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Router\Exception\RouteNotFoundException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The steps view
 *
 * @since  __DEPLOY_VERSION__
 */
class JsonapiView extends BaseApiView
{
    /**
     * The fields to render item in the documents
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    protected $fieldsToRenderItem = [
        'typeAlias',
        'id',
        'title',
        'published',
        'description',
        'ordering',
        'position',
        'target',
        'type',
        'interactive_type',
        'url',
        'created',
        'created_by',
        'modified',
        'modified_by',
        'checked_out',
        'checked_out_time',
        'language',
        'editor',
        'note',
    ];

    /**
     * The fields to render items in the documents
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    protected $fieldsToRenderList = [
        'id',
        'title',
        'published',
        'description',
    ];

    /**
     * Execute and display a template script.
     *
     * @param   object  $item  Item
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function displayItem($item = null)
    {
        if ($item === null) {
            /** @var \Joomla\CMS\MVC\Model\AdminModel $model */
            $model = $this->getModel();
            $item  = $this->prepareItem($model->getItem());
        }

        if ($item->id === null) {
            throw new RouteNotFoundException('Item does not exist');
        }

        if ($item->tour_id != $this->getModel()->getState('filter.tour_id')) {
            throw new RouteNotFoundException('Item does not exist');
        }

        return parent::displayItem($item);
    }
}
