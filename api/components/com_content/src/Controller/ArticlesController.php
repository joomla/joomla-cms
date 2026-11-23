<?php

/**
 * @package     Joomla.API
 * @subpackage  com_content
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Api\Controller;

use Joomla\CMS\Categories\CategoryChangeTrait;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The article controller
 *
 * @since  4.0.0
 */
class ArticlesController extends ApiController
{
    use CategoryChangeTrait;

    /**
     * The content type of the item.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $contentType = 'articles';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'articles';

    /**
     * Article list view amended to add filtering of data
     *
     * @return  static  A BaseController object to support chaining.
     *
     * @since   4.0.0
     */
    public function displayList()
    {
        $apiFilterInfo = $this->input->get('filter', [], 'array');
        $filter        = InputFilter::getInstance();

        if (\array_key_exists('author', $apiFilterInfo)) {
            $this->modelState->set('filter.author_id', $filter->clean($apiFilterInfo['author'], 'INT'));
        }

        if (\array_key_exists('category', $apiFilterInfo)) {
            $this->modelState->set('filter.category_id', $filter->clean($apiFilterInfo['category'], 'INT'));
        }

        if (\array_key_exists('search', $apiFilterInfo)) {
            $this->modelState->set('filter.search', $filter->clean($apiFilterInfo['search'], 'STRING'));
        }

        if (\array_key_exists('state', $apiFilterInfo)) {
            $this->modelState->set('filter.published', $filter->clean($apiFilterInfo['state'], 'INT'));
        }

        if (\array_key_exists('featured', $apiFilterInfo)) {
            $this->modelState->set('filter.featured', $filter->clean($apiFilterInfo['featured'], 'INT'));
        }

        if (\array_key_exists('tag', $apiFilterInfo)) {
            $this->modelState->set('filter.tag', $filter->clean($apiFilterInfo['tag'], 'INT'));
        }

        if (\array_key_exists('language', $apiFilterInfo)) {
            $this->modelState->set('filter.language', $filter->clean($apiFilterInfo['language'], 'STRING'));
        }

        if (\array_key_exists('modified_start', $apiFilterInfo)) {
            $this->modelState->set('filter.modified_start', $filter->clean($apiFilterInfo['modified_start'], 'STRING'));
        }

        if (\array_key_exists('modified_end', $apiFilterInfo)) {
            $this->modelState->set('filter.modified_end', $filter->clean($apiFilterInfo['modified_end'], 'STRING'));
        }

        if (\array_key_exists('checked_out', $apiFilterInfo)) {
            $this->modelState->set('filter.checked_out', $filter->clean($apiFilterInfo['checked_out'], 'INT'));
        }

        if (\array_key_exists('stage', $apiFilterInfo)) {
            $this->modelState->set('filter.stage', $filter->clean($apiFilterInfo['stage'], 'INT'));
        }

        $apiListInfo = $this->input->get('list', [], 'array');

        if (\array_key_exists('ordering', $apiListInfo)) {
            $this->modelState->set('list.ordering', $filter->clean($apiListInfo['ordering'], 'STRING'));
        }

        if (\array_key_exists('direction', $apiListInfo)) {
            $this->modelState->set('list.direction', $filter->clean($apiListInfo['direction'], 'STRING'));
        }

        return parent::displayList();
    }

    /**
     * Method to allow extended classes to manipulate the data to be saved for an extension.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    protected function preprocessSaveData(array $data): array
    {
        foreach (FieldsHelper::getFields('com_content.article') as $field) {
            if (isset($data[$field->name])) {
                !isset($data['com_fields']) && $data['com_fields'] = [];

                $data['com_fields'][$field->name] = $data[$field->name];
                unset($data[$field->name]);
            }
        }

        if (($this->input->getMethod() === 'PATCH') && !(\array_key_exists('tags', $data))) {
            $tags = new TagsHelper();
            $tags->getTagIds($data['id'], 'com_content.article');
            $data['tags'] = explode(',', $tags->tags);
        }

        return $data;
    }

    /**
     * Method to check if you can add a new record.
     *
     * Like in the administrator, the create permission is checked in the target category.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   6.2.1
     */
    protected function allowAdd($data = [])
    {
        if (!$this->allowCategoryAdd($data)) {
            return false;
        }

        return parent::allowAdd($data);
    }

    /**
     * Method to check if you can edit an existing record.
     *
     * Moving a record to another category requires the create permission in the target category.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key; default is id.
     *
     * @return  boolean
     *
     * @since   6.2.1
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        return parent::allowEdit($data, $key) && $this->allowCategoryChange(
            $this->getModel('article')->getTable(),
            isset($data[$key]) ? (int) $data[$key] : 0,
            $this->input->get('data', json_decode($this->input->json->getRaw(), true), 'array')
        );
    }
}
