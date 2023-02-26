<?php
/**
 * @package     Joomla.API
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Api\View\Adapters;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\Component\Media\Administrator\Provider\ProviderManagerHelperTrait;

/**
 * Media web service view
 *
 * @since  4.1.0
 */
class JsonapiView extends BaseApiView
{
    use ProviderManagerHelperTrait;

    /**
     * The fields to render item in the documents
     *
     * @var    array
     * @since  4.1.0
     */
    protected $fieldsToRenderItem = [
        'provider_id',
        'name',
        'path',
    ];

    /**
     * The fields to render items in the documents
     *
     * @var    array
     * @since  4.1.0
     */
    protected $fieldsToRenderList = [
        'provider_id',
        'name',
        'path',
    ];
}
