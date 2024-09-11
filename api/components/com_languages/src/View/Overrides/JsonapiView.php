<?php

/**
 * @package     Joomla.API
 * @subpackage  com_languages
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Api\View\Overrides;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The overrides view
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
    protected $fieldsToRenderItem = ['value'];

    /**
     * The fields to render items in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderList = ['value'];

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
        /** @var \Joomla\Component\Languages\Administrator\Model\OverrideModel $model */
        $model = $this->getModel();
        $id    = $model->getState($model->getName() . '.id');
        $item  = $this->prepareItem($model->getItem($id));

        return parent::displayItem($item);
    }
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
        /** @var \Joomla\Component\Languages\Administrator\Model\OverridesModel $model */
        $model = $this->getModel();
        $items = [];

        foreach ($model->getOverrides() as $key => $override) {
            $item = (object) [
                'key'      => $key,
                'override' => $override,
            ];

            $items[] = $this->prepareItem($item);
        }

        return parent::displayList($items);
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
        $item->id    = $item->key;
        $item->value = $item->override;
        unset($item->key);
        unset($item->override);

        return parent::prepareItem($item);
    }
}
