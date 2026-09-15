<?php

/**
 * @package     Joomla.API
 * @subpackage  com_guidedtours
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Api\View\Tours;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The tours view
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
        'uid',
        'description',
        'published',
        'ordering',
        'publish_up',
        'publish_down',
        'created',
        'created_by',
        'modified',
        'modified_by',
        'access',
        'access_level',
        'extensions',
        'url',
        'checked_out',
        'checked_out_time',
        'language',
        'autostart',
        'steps_count',
        'editor',
        'language_title',
        'language_image',
        'note',
    ];

    /**
     * The fields to render items in the documents
     *
     * @var  array
     * @since  __DEPLOY_VERSION__
     */
    protected $fieldsToRenderList = [
        'id',
        'title',
        'uid',
        'description',
        'published',
        'steps_count',
    ];
}
