<?php

/**
 * @package     Joomla.API
 * @subpackage  com_languages
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Api\View\Strings;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Tobscure\JsonApi\Collection;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The strings view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
    /**
     * The fields to render items in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderList = [
        'id',
        'string',
        'file',
    ];

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
        /** @var \Joomla\Component\Languages\Administrator\Model\StringsModel $model */
        $model  = $this->getModel();
        $result = $model->search();

        if ($result instanceof \Exception) {
            throw $result;
        }

        $items = [];

        foreach ($result['results'] as $item) {
            $items[] = $this->prepareItem($item);
        }

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if ($this->type === null) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_CONTENT_TYPE_MISSING'), 400);
        }

        $collection = (new Collection($items, new JoomlaSerializer($this->type)))
            ->fields([$this->type => $this->fieldsToRenderList]);

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
        $item->id = $item->constant;
        unset($item->constant);

        return parent::prepareItem($item);
    }
}
