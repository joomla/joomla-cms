<?php

/**
 * @package     Joomla.API
 * @subpackage  com_contenthistory
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contenthistory\Api\View\History;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The history view
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
        'ucm_item_id',
        'ucm_type_id',
        'version_note',
        'save_date',
        'editor_user_id',
        'character_count',
        'sha1_hash',
        'version_data',
        'keep_forever',
        'editor',
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
        $item->id = $item->version_id;
        unset($item->version_id);

        $item->version_data = (array) json_decode($item->version_data, true);

        return parent::prepareItem($item);
    }
}
