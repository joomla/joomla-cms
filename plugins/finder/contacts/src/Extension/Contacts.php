<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.contacts
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Finder\Contacts\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Finder as FinderEvent;
use Joomla\Component\Contact\Site\Helper\RouteHelper;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\QueryInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Finder adapter for Joomla Contacts.
 *
 * @since  2.5
 */
final class Contacts extends Adapter implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * The plugin identifier.
     *
     * @var    string
     * @since  2.5
     */
    protected $context = 'Contacts';

    /**
     * The extension name.
     *
     * @var    string
     * @since  2.5
     */
    protected $extension = 'com_contact';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  2.5
     */
    protected $layout = 'contact';

    /**
     * The type of content that the adapter indexes.
     *
     * @var    string
     * @since  2.5
     */
    protected $type_title = 'Contact';

    /**
     * The table name.
     *
     * @var    string
     * @since  2.5
     */
    protected $table = '#__contact_details';

    /**
     * The field the published state is stored in.
     *
     * @var    string
     * @since  2.5
     */
    protected $state_field = 'published';

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return array_merge(parent::getSubscribedEvents(), [
            'onFinderCategoryChangeState' => 'onFinderCategoryChangeState',
            'onFinderAfterDelete'         => 'onFinderAfterDelete',
            'onFinderAfterSave'           => 'onFinderAfterSave',
            'onFinderBeforeSave'          => 'onFinderBeforeSave',
            'onFinderChangeState'         => 'onFinderChangeState',
        ]);
    }

    /**
     * Method to update the item link information when the item category is
     * changed. This is fired when the item category is published or unpublished
     * from the list view.
     *
     * @param   FinderEvent\AfterCategoryChangeStateEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderCategoryChangeState(FinderEvent\AfterCategoryChangeStateEvent $event): void
    {
        // Make sure we're handling com_contact categories
        if ($event->getExtension() === 'com_contact') {
            $this->categoryStateChange($event->getPks(), $event->getValue());
        }
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * This event will fire when contacts are deleted and when an indexed item is deleted.
     *
     * @param   FinderEvent\AfterDeleteEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderAfterDelete(FinderEvent\AfterDeleteEvent $event): void
    {
        $context = $event->getContext();
        $table   = $event->getItem();

        if ($context === 'com_contact.contact') {
            $id = $table->id;
        } elseif ($context === 'com_finder.index') {
            $id = $table->link_id;
        } else {
            return;
        }

        // Remove the items.
        $this->remove($id);
    }

    /**
     * Method to determine if the access level of an item changed.
     *
     * @param   FinderEvent\AfterSaveEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderAfterSave(FinderEvent\AfterSaveEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $isNew   = $event->getIsNew();

        // We only want to handle contacts here
        if ($context === 'com_contact.contact') {
            // Check if the access levels are different
            if (!$isNew && $this->old_access != $row->access) {
                // Process the change.
                $this->itemAccessChange($row);
            }

            // Reindex the item
            $this->reindex($row->id);
        }

        // Check for access changes in the category
        if ($context === 'com_categories.category') {
            // Check if the access levels are different
            if (!$isNew && $this->old_cataccess != $row->access) {
                $this->categoryAccessChange($row);
            }
        }
    }

    /**
     * Method to reindex the link information for an item that has been saved.
     * This event is fired before the data is actually saved so we are going
     * to queue the item to be indexed later.
     *
     * @param   FinderEvent\BeforeSaveEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderBeforeSave(FinderEvent\BeforeSaveEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $isNew   = $event->getIsNew();

        // We only want to handle contacts here
        if ($context === 'com_contact.contact') {
            // Query the database for the old access level if the item isn't new
            if (!$isNew) {
                $this->checkItemAccess($row);
            }
        }

        // Check for access levels from the category
        if ($context === 'com_categories.category') {
            // Query the database for the old access level if the item isn't new
            if (!$isNew) {
                $this->checkCategoryAccess($row);
            }
        }
    }

    /**
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   FinderEvent\AfterChangeStateEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderChangeState(FinderEvent\AfterChangeStateEvent $event): void
    {
        $context = $event->getContext();
        $pks     = $event->getPks();
        $value   = $event->getValue();

        // We only want to handle contacts here
        if ($context === 'com_contact.contact') {
            $this->itemStateChange($pks, $value);
        }

        // Handle when the plugin is disabled
        if ($context === 'com_plugins.plugin' && $value === 0) {
            $this->pluginDisable($pks);
        }
    }

    /**
     * Method to index an item. The item must be a Result object.
     *
     * @param   Result  $item  The item to index as a Result object.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    protected function index(Result $item)
    {
        // Check if the extension is enabled
        if (ComponentHelper::isEnabled($this->extension) === false) {
            return;
        }

        $item->setLanguage();

        // Initialize the item parameters.
        $item->params = new Registry($item->params);

        // Create a URL as identifier to recognise items again.
        $item->url = $this->getUrl($item->id, $this->extension, $this->layout);

        // Build the necessary route and path information.
        $item->route = RouteHelper::getContactRoute($item->slug, $item->catslug, $item->language);

        // Get the menu title if it exists.
        $title = $this->getItemMenuTitle($item->url);

        // Adjust the title if necessary.
        if (!empty($title) && $this->params->get('use_menu_title', true)) {
            $item->title = $title;
        }

        // Add the image.
        if ($item->image) {
            $item->imageUrl = $item->image;
            $item->imageAlt = $item->title ?? '';
        }

        /*
         * Add the metadata processing instructions based on the contact
         * configuration parameters.
         */

        // Handle the contact position.
        if ($item->params->get('show_position', true)) {
            $item->addInstruction(Indexer::META_CONTEXT, 'position');
        }

        // Handle the contact street address.
        if ($item->params->get('show_street_address', true)) {
            $item->addInstruction(Indexer::META_CONTEXT, 'address');
        }

        // Handle the contact city.
        if ($item->params->get('show_suburb', true)) {
            $item->addInstruction(Indexer::META_CONTEXT, 'city');
        }

        // Handle the contact region.
        if ($item->params->get('show_state', true)) {
            $item->addInstruction(Indexer::META_CONTEXT, 'region');
        }

        // Handle the contact country.
        if ($item->params->get('show_country', true)) {
            $item->addInstruction(Indexer::META_CONTEXT, 'country');
        }

        // Handle the contact zip code.
        if ($item->params->get('show_postcode', true)) {
            $item->addInstruction(Indexer::META_CONTEXT, 'zip');
        }

        // Handle the contact telephone number.
        if ($item->params->get('show_telephone', true)) {
            $item->addInstruction(Indexer::META_CONTEXT, 'telephone');
        }

        // Handle the contact fax number.
        if ($item->params->get('show_fax', true)) {
            $item->addInstruction(Indexer::META_CONTEXT, 'fax');
        }

        // Handle the contact email address.
        if ($item->params->get('show_email', true)) {
            $item->addInstruction(Indexer::META_CONTEXT, 'email');
        }

        // Handle the contact mobile number.
        if ($item->params->get('show_mobile', true)) {
            $item->addInstruction(Indexer::META_CONTEXT, 'mobile');
        }

        // Handle the contact webpage.
        if ($item->params->get('show_webpage', true)) {
            $item->addInstruction(Indexer::META_CONTEXT, 'webpage');
        }

        // Handle the contact user name.
        $item->addInstruction(Indexer::META_CONTEXT, 'user');

        // Get taxonomies to display
        $taxonomies = $this->params->get('taxonomies', ['type', 'category', 'language', 'region', 'country']);

        // Add the type taxonomy data.
        if (\in_array('type', $taxonomies)) {
            $item->addTaxonomy('Type', 'Contact');
        }

        // Add the category taxonomy data.
        $categories = $this->getApplication()->bootComponent('com_contact')->getCategory(['published' => false, 'access' => false]);
        $category   = $categories->get($item->catid);

        if (!$category) {
            return;
        }

        // Add the category taxonomy data.
        if (\in_array('category', $taxonomies)) {
            $item->addNestedTaxonomy('Category', $category, $this->translateState($category->published), $category->access, $category->language);
        }

        // Add the language taxonomy data.
        if (\in_array('language', $taxonomies)) {
            $item->addTaxonomy('Language', $item->language);
        }

        // Add the region taxonomy data.
        if (\in_array('region', $taxonomies) && !empty($item->region) && $this->params->get('tax_add_region', true)) {
            $item->addTaxonomy('Region', $item->region);
        }

        // Add the country taxonomy data.
        if (\in_array('country', $taxonomies) && !empty($item->country) && $this->params->get('tax_add_country', true)) {
            $item->addTaxonomy('Country', $item->country);
        }

        // Get content extras.
        Helper::getContentExtras($item);
        Helper::addCustomFields($item, 'com_contact.contact');

        // Index the item.
        $this->indexer->index($item);
    }

    /**
     * Method to setup the indexer to be run.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    protected function setup()
    {
        return true;
    }

    /**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  An object implementing QueryInterface or null.
     *
     * @return  QueryInterface  A database object.
     *
     * @since   2.5
     */
    protected function getListQuery($query = null)
    {
        $db = $this->getDatabase();

        // Check if we can use the supplied SQL query.
        $query = $query instanceof QueryInterface ? $query : $db->getQuery(true)
            ->select('a.id, a.name AS title, a.alias, a.con_position AS position, a.address, a.created AS start_date')
            ->select('a.created_by_alias, a.modified, a.modified_by')
            ->select('a.metakey, a.metadesc, a.metadata, a.language')
            ->select('a.sortname1, a.sortname2, a.sortname3')
            ->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date')
            ->select('a.suburb AS city, a.state AS region, a.country, a.postcode AS zip')
            ->select('a.telephone, a.fax, a.misc AS summary, a.email_to AS email, a.mobile')
            ->select('a.image, a.webpage, a.access, a.published AS state, a.ordering, a.params, a.catid')
            ->select('c.title AS category, c.published AS cat_state, c.access AS cat_access');

        // Handle the alias CASE WHEN portion of the query
        $case_when_item_alias = ' CASE WHEN ';
        $case_when_item_alias .= $query->charLength('a.alias', '!=', '0');
        $case_when_item_alias .= ' THEN ';
        $a_id = $query->castAsChar('a.id');
        $case_when_item_alias .= $query->concatenate([$a_id, 'a.alias'], ':');
        $case_when_item_alias .= ' ELSE ';
        $case_when_item_alias .= $a_id . ' END as slug';
        $query->select($case_when_item_alias);

        $case_when_category_alias = ' CASE WHEN ';
        $case_when_category_alias .= $query->charLength('c.alias', '!=', '0');
        $case_when_category_alias .= ' THEN ';
        $c_id = $query->castAsChar('c.id');
        $case_when_category_alias .= $query->concatenate([$c_id, 'c.alias'], ':');
        $case_when_category_alias .= ' ELSE ';
        $case_when_category_alias .= $c_id . ' END as catslug';
        $query->select($case_when_category_alias)

            ->select('u.name')
            ->from('#__contact_details AS a')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid')
            ->join('LEFT', '#__users AS u ON u.id = a.user_id');

        return $query;
    }
}
