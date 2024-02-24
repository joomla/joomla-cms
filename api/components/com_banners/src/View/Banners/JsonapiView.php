<?php

/**
 * @package     Joomla.API
 * @subpackage  com_banners
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Api\View\Banners;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The banners view
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
        'cid',
        'type',
        'name',
        'alias',
        'imptotal',
        'impmade',
        'clicks',
        'clickurl',
        'state',
        'catid',
        'description',
        'custombannercode',
        'sticky',
        'ordering',
        'metakey',
        'params',
        'own_prefix',
        'metakey_prefix',
        'purchase_type',
        'track_clicks',
        'track_impressions',
        'checked_out',
        'checked_out_time',
        'publish_up',
        'publish_down',
        'reset',
        'created',
        'language',
        'created_by',
        'created_by_alias',
        'modified',
        'modified_by',
        'version',
        'contenthistoryHelper',
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
        'alias',
        'checked_out',
        'checked_out_time',
        'catid',
        'clicks',
        'metakey',
        'sticky',
        'impmade',
        'imptotal',
        'state',
        'ordering',
        'purchase_type',
        'language',
        'publish_up',
        'publish_down',
        'language_image',
        'editor',
        'category_title',
        'client_name',
        'client_purchase_type',
    ];
}
