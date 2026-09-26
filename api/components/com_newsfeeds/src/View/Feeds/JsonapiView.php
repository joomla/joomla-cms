<?php

/**
 * @package     Joomla.API
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Api\View\Feeds;

use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\Component\Newsfeeds\Api\Serializer\NewsfeedSerializer;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The feeds view
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
        'category',
        'name',
        'alias',
        'link',
        'published',
        'numarticles',
        'cache_time',
        'checked_out',
        'checked_out_time',
        'ordering',
        'rtl',
        'access',
        'language',
        'params',
        'created',
        'created_by',
        'created_by_alias',
        'modified',
        'modified_by',
        'metakey',
        'metadesc',
        'metadata',
        'publish_up',
        'publish_down',
        'description',
        'version',
        'hits',
        'images',
        'tags',
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
        'category',
        'numarticles',
        'cache_time',
        'created_by',
        'published',
        'access',
        'ordering',
        'language',
        'publish_up',
        'publish_down',
        'language_title',
        'language_image',
        'editor',
        'access_level',
        'category_title',
        'modified_by',
    ];

    /**
     * The relationships the item has
     *
     * @var    array
     * @since  4.0.0
     */
    protected $relationship = [
        'category',
        'created_by',
        'modified_by',
        'tags',
    ];

    /**
     * Constructor.
     *
     * @param   array  $config  A named configuration array for object construction.
     *                          contentType: the name (optional) of the content type to use for the serialization
     *
     * @since   4.0.0
     */
    public function __construct($config = [])
    {
        if (\array_key_exists('contentType', $config)) {
            $this->serializer = new NewsfeedSerializer($config['contentType']);
        }

        parent::__construct($config);
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
        if (Multilanguage::isEnabled()) {
            $this->fieldsToRenderItem[] = 'languageAssociations';
            $this->relationship[]       = 'languageAssociations';
        }

        return parent::displayItem();
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
        if (Multilanguage::isEnabled() && !empty($item->associations)) {
            $associations = [];

            foreach ($item->associations as $language => $association) {
                $itemId = explode(':', $association)[0];

                $associations[] = (object) [
                    'id'       => $itemId,
                    'language' => $language,
                ];
            }

            $item->associations = $associations;
        }

        if (!empty($item->tags->tags)) {
            $tagsIds   = explode(',', $item->tags->tags);
            $tagsNames = $item->tagsHelper->getTagNames($tagsIds);

            $item->tags = array_combine($tagsIds, $tagsNames);
        } else {
            $item->tags = [];
        }

        return parent::prepareItem($item);
    }
}
