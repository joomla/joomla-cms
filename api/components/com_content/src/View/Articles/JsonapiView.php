<?php

/**
 * @package     Joomla.API
 * @subpackage  com_content
 *
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Api\View\Articles;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Content\Api\Helper\ContentHelper;
use Joomla\Component\Content\Api\Serializer\ContentSerializer;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

class JsonapiView extends BaseApiView
{
    /**
     * Fields rendered for single item
     */
    protected $fieldsToRenderItem = [
        'id',
        'typeAlias',
        'asset_id',
        'title',
        'text',
        'tags',
        'language',
        'state',
        'category',
        'images',
        'metakey',
        'metadesc',
        'metadata',
        'access',
        'featured',
        'alias',
        'note',
        'publish_up',
        'publish_down',
        'urls',
        'created',
        'created_by',
        'created_by_alias',
        'modified',
        'modified_by',
        'hits',
        'version',
        'featured_up',
        'featured_down',
        'custom_fields', // NEW SAFE CONTAINER
    ];

    /**
     * Fields rendered for list
     */
    protected $fieldsToRenderList = [
        'id',
        'typeAlias',
        'asset_id',
        'title',
        'text',
        'tags',
        'language',
        'state',
        'category',
        'images',
        'metakey',
        'metadesc',
        'metadata',
        'access',
        'featured',
        'alias',
        'note',
        'publish_up',
        'publish_down',
        'urls',
        'created',
        'created_by',
        'created_by_alias',
        'modified',
        'hits',
        'version',
        'featured_up',
        'featured_down',
        'custom_fields', // NEW SAFE CONTAINER
    ];

    protected $relationship = [
        'category',
        'created_by',
        'tags',
    ];

    public function __construct($config = [])
    {
        if (\array_key_exists('contentType', $config)) {
            $this->serializer = new ContentSerializer($config['contentType']);
        }

        parent::__construct($config);
    }

    /**
     * NO dynamic field injection anymore
     */
    public function displayList(?array $items = null)
    {
        return parent::displayList();
    }

    /**
     * NO dynamic field injection anymore
     */
    public function displayItem($item = null)
    {
        $this->relationship[] = 'modified_by';

        if (Multilanguage::isEnabled()) {
            $this->fieldsToRenderItem[] = 'languageAssociations';
            $this->relationship[]       = 'languageAssociations';
        }

        return parent::displayItem();
    }

    /**
     * Prepare item before render
     */
    protected function prepareItem($item)
    {
        if (!$item) {
            return $item;
        }

        $item->text = $item->introtext . ' ' . $item->fulltext;

        // Trigger content plugins
        PluginHelper::importPlugin('content');
        Factory::getApplication()->triggerEvent(
            'onContentPrepare',
            ['com_content.article', &$item, &$item->params]
        );

        /**
         * FIX: Store custom fields safely
         */
        $customFields = [];

        foreach (FieldsHelper::getFields('com_content.article', $item, true) as $field) {
            $customFields[$field->name] = $field->apivalue ?? $field->rawvalue;
        }

        $item->custom_fields = $customFields;

        /**
         * Multilanguage associations
         */
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

        /**
         * Tags
         */
        if (!empty($item->tags->tags)) {
            $tagsIds    = explode(',', $item->tags->tags);
            $item->tags = $item->tagsHelper->getTags($tagsIds);
        } else {
            $item->tags = [];
            $tags       = new TagsHelper();
            $tagsIds    = $tags->getTagIds($item->id, 'com_content.article');

            if (!empty($tagsIds)) {
                $tagsIds    = explode(',', $tagsIds);
                $item->tags = $tags->getTags($tagsIds);
            }
        }

        /**
         * Process core images safely
         */
        if (isset($item->images) && \is_string($item->images)) {
            $registry     = new Registry($item->images);
            $item->images = $registry->toArray();

            if (!empty($item->images['image_intro'])) {
                $item->images['image_intro'] =
                    ContentHelper::resolve($item->images['image_intro']);
            }

            if (!empty($item->images['image_fulltext'])) {
                $item->images['image_fulltext'] =
                    ContentHelper::resolve($item->images['image_fulltext']);
            }
        }

        return parent::prepareItem($item);
    }
}
