<?php

/**
 * @package     Joomla.API
 * @subpackage  com_categories
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Api\View\Categories;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * The categories view
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
        'title',
        'alias',
        'note',
        'published',
        'access',
        'checked_out',
        'checked_out_time',
        'created_user_id',
        'parent_id',
        'level',
        'extension',
        'lft',
        'rgt',
        'language',
        'language_title',
        'language_image',
        'editor',
        'access_level',
        'author_name',
        'count_trashed',
        'count_unpublished',
        'count_published',
        'count_archived',
        'params',
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
        'alias',
        'note',
        'published',
        'access',
        'checked_out',
        'checked_out_time',
        'created_user_id',
        'parent_id',
        'level',
        'lft',
        'rgt',
        'language',
        'language_title',
        'language_image',
        'editor',
        'access_level',
        'author_name',
        'count_trashed',
        'count_unpublished',
        'count_published',
        'count_archived',
        'params',
    ];

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
        foreach (FieldsHelper::getFields('com_content.categories') as $field) {
            $this->fieldsToRenderList[] = $field->name;
        }

        return parent::displayList();
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
        foreach (FieldsHelper::getFields('com_content.categories') as $field) {
            $this->fieldsToRenderItem[] = $field->name;
        }

        if ($item === null) {
            /** @var \Joomla\CMS\MVC\Model\AdminModel $model */
            $model = $this->getModel();
            $item  = $this->prepareItem($model->getItem());
        }

        if ($item->id === null) {
            throw new RouteNotFoundException('Item does not exist');
        }

        if ($item->extension != $this->getModel()->getState('filter.extension')) {
            throw new RouteNotFoundException('Item does not exist');
        }

        return parent::displayItem($item);
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
        foreach (FieldsHelper::getFields('com_content.categories', $item, true) as $field) {
            $item->{$field->name} = $field->apivalue ?? $field->rawvalue;
        }

        return parent::prepareItem($item);
    }
}
