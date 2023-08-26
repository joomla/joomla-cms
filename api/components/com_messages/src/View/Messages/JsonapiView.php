<?php

/**
 * @package     Joomla.API
 * @subpackage  com_messages
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Api\View\Messages;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The messages view
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
        'user_id_from',
        'user_id_to',
        'date_time',
        'priority',
        'subject',
        'message',
        'state',
        'user_from',
    ];

    /**
     * The fields to render items in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderList = [
        'id',
        'user_id_from',
        'user_id_to',
        'date_time',
        'priority',
        'subject',
        'message',
        'state',
        'user_from',
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
        $item->id = $item->message_id;
        unset($item->message_id);

        return parent::prepareItem($item);
    }
}
