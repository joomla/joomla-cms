<?php

/**
 * @package     Joomla.API
 * @subpackage  com_banners
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Api\View\Clients;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * The clients view
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
        'typeAlias',
        'id',
        'checked_out_time',
        'name',
        'contact',
        'email',
        'checked_out',
        'checked_out_time',
        'extrainfo',
        'state',
        'metakey',
        'own_prefix',
        'metakey_prefix',
        'purchase_type',
        'track_clicks',
        'track_impressions',
    ];

    /**
     * The fields to render items in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderList = [
        'id',
        'name',
        'contact',
        'checked_out',
        'checked_out_time',
        'state',
        'metakey',
        'purchase_type',
        'nbanners',
        'editor',
        'count_published',
        'count_unpublished',
        'count_trashed',
        'count_archived',
    ];
}
