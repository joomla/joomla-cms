<?php

/**
 * @package     Joomla.API
 * @subpackage  com_contact
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Api\View\Contacts;

use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\Component\Contact\Api\Serializer\ContactSerializer;
use Joomla\Component\Content\Api\Helper\ContentHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The contacts view
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
        'alias',
        'name',
        'category',
        'created',
        'created_by',
        'created_by_alias',
        'modified',
        'modified_by',
        'image',
        'tags',
        'featured',
        'publish_up',
        'publish_down',
        'version',
        'hits',
        'metakey',
        'metadesc',
        'metadata',
        'con_position',
        'address',
        'suburb',
        'state',
        'country',
        'postcode',
        'telephone',
        'fax',
        'misc',
        'email_to',
        'default_con',
        'user_id',
        'access',
        'mobile',
        'webpage',
        'sortname1',
        'sortname2',
        'sortname3',
    ];

    /**
     * The fields to render items in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderList = [
        'id',
        'alias',
        'name',
        'category',
        'created',
        'created_by',
        'created_by_alias',
        'modified',
        'modified_by',
        'image',
        'tags',
        'user_id',
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
        'user_id',
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
            $this->serializer = new ContactSerializer($config['contentType']);
        }

        parent::__construct($config);
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
        foreach (FieldsHelper::getFields('com_contact.contact') as $field) {
            $this->fieldsToRenderList[] = $field->name;
        }

        return parent::displayList();
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
        foreach (FieldsHelper::getFields('com_contact.contact') as $field) {
            $this->fieldsToRenderItem[] = $field->name;
        }

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
        foreach (FieldsHelper::getFields('com_contact.contact', $item, true) as $field) {
            $item->{$field->name} = $field->apivalue ?? $field->rawvalue;
        }

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

        if (isset($item->image)) {
            $item->image = ContentHelper::resolve($item->image);
        }

        return parent::prepareItem($item);
    }
}
