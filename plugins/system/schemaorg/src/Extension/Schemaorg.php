<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.schemaorg
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Schemaorg\Extension;

use Joomla\CMS\Event\Model;
use Joomla\CMS\Event\Plugin\System\Schemaorg\BeforeCompileHeadEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareDataEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareFormEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareSaveEvent;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Schemaorg\SchemaorgPrepareDateTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareImageTrait;
use Joomla\CMS\Schemaorg\SchemaorgServiceInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Schemaorg System Plugin
 *
 * @since  5.0.0
 */
final class Schemaorg extends CMSPlugin implements SubscriberInterface, DispatcherAwareInterface
{
    use DatabaseAwareTrait;
    use DispatcherAwareTrait;
    use SchemaorgPrepareDateTrait;
    use SchemaorgPrepareImageTrait;
    use UserFactoryAwareTrait;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeCompileHead'  => 'onBeforeCompileHead',
            'onContentPrepareData' => 'onContentPrepareData',
            'onContentPrepareForm' => 'onContentPrepareForm',
            'onContentAfterSave'   => 'onContentAfterSave',
            'onContentAfterDelete' => 'onContentAfterDelete',
        ];
    }

    /**
     * Runs on content preparation
     *
     * @param   Model\PrepareDataEvent  $event  The event
     *
     * @since   5.0.0
     *
     */
    public function onContentPrepareData(Model\PrepareDataEvent $event)
    {
        $context = $event->getContext();
        $data    = $event->getData();

        $app = $this->getApplication();

        if ($app->isClient('site') || !$this->isSupported($context)) {
            return;
        }

        $data = (object) $data;

        $itemId = $data->id ?? 0;

        // Check if the form already has some data
        if ($itemId > 0) {
            $db = $this->getDatabase();

            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__schemaorg'))
                ->where($db->quoteName('itemId') . '= :itemId')
                ->bind(':itemId', $itemId, ParameterType::INTEGER)
                ->where($db->quoteName('context') . '= :context')
                ->bind(':context', $context, ParameterType::STRING);

            $results = $db->setQuery($query)->loadAssoc();

            if (empty($results)) {
                return;
            }

            $schemaType                 = $results['schemaType'];
            $data->schema['schemaType'] = $schemaType;

            $schema = new Registry($results['schema']);

            $data->schema[$schemaType] = $schema->toArray();
        }

        $dispatcher = $this->getDispatcher();
        $event      = new PrepareDataEvent('onSchemaPrepareData', [
            'subject' => $data,
            'context' => $context,
        ]);

        PluginHelper::importPlugin('schemaorg', null, true, $dispatcher);
        $dispatcher->dispatch('onSchemaPrepareData', $event);
    }

    /**
     * The form event.
     *
     * @param   Model\PrepareFormEvent  $event  The event
     *
     * @since   5.0.0
     */
    public function onContentPrepareForm(Model\PrepareFormEvent $event)
    {
        $form    = $event->getForm();
        $context = $form->getName();
        $app     = $this->getApplication();

        if (!$app->isClient('administrator') || !$this->isSupported($context)) {
            return;
        }

        // Load plugin language files.
        $this->loadLanguage();

        // Load the form fields
        $form->loadFile(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms/schemaorg.xml');


        // The user should configure the plugin first
        if (!$this->params->get('baseType')) {
            $form->removeField('schemaType', 'schema');

            $plugin = PluginHelper::getPlugin('system', 'schemaorg');

            $user = $this->getApplication()->getIdentity();

            $infoText = Text::_('PLG_SYSTEM_SCHEMAORG_FIELD_SCHEMA_DESCRIPTION_NOT_CONFIGURATED');

            // If edit permission are available, offer a link
            if ($user->authorise('core.edit', 'com_plugins')) {
                $infoText = Text::sprintf('PLG_SYSTEM_SCHEMAORG_FIELD_SCHEMA_DESCRIPTION_NOT_CONFIGURATED_ADMIN', (int) $plugin->id);
            }

            $form->setFieldAttribute('schemainfo', 'description', $infoText, 'schema');

            $form->setFieldAttribute('extendJed', 'type', 'hidden', 'schema');
            $form->setFieldAttribute('extendJed', 'class', 'hidden', 'schema');

            return;
        }

        $dispatcher = $this->getDispatcher();
        $event      = new PrepareFormEvent('onSchemaPrepareForm', [
            'subject' => $form,
        ]);

        PluginHelper::importPlugin('schemaorg', null, true, $dispatcher);
        $dispatcher->dispatch('onSchemaPrepareForm', $event);
    }

    /**
     * Saves form field data in the database
     *
     * @param   Model\AfterSaveEvent $event
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function onContentAfterSave(Model\AfterSaveEvent $event)
    {
        $context = $event->getContext();
        $table   = $event->getItem();
        $isNew   = $event->getIsNew();
        $data    = $event->getData();
        $app     = $this->getApplication();
        $db      = $this->getDatabase();

        if (!$app->isClient('administrator') || !$this->isSupported($context)) {
            return;
        }

        $itemId = (int) $table->id;

        if (empty($data['schema']) || empty($data['schema']['schemaType']) || $data['schema']['schemaType'] === 'None') {
            $this->deleteSchemaOrg($itemId, $context);
            return;
        }

        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__schemaorg'))
            ->where($db->quoteName('itemId') . '= :itemId')
            ->bind(':itemId', $itemId, ParameterType::INTEGER)
            ->where($db->quoteName('context') . '= :context')
            ->bind(':context', $context, ParameterType::STRING);

        $entry = $db->setQuery($query)->loadObject();

        if (empty($entry->id)) {
            $entry = new \stdClass();
        }

        $entry->itemId     = (int) $table->getId();
        $entry->context    = $context;

        if (isset($data['schema']['schemaType'])) {
            $entry->schemaType = $data['schema']['schemaType'];

            if (isset($data['schema'][$entry->schemaType])) {
                $entry->schema = (new Registry($data['schema'][$entry->schemaType]))->toString();
            }
        }

        $dispatcher = $this->getDispatcher();
        $event      = new PrepareSaveEvent('onSchemaPrepareSave', [
            'subject' => $entry,
            'context' => $context,
            'item'    => $table,
            'isNew'   => $isNew,
            'schema'  => $data['schema'],
        ]);

        PluginHelper::importPlugin('schemaorg', null, true, $dispatcher);
        $dispatcher->dispatch('onSchemaPrepareSave', $event);

        if (!isset($entry->schemaType)) {
            return;
        }

        if (!empty($entry->id)) {
            $db->updateObject('#__schemaorg', $entry, 'id');
        } else {
            $db->insertObject('#__schemaorg', $entry, 'id');
        }
    }

    /**
     * This event is triggered before the framework creates the Head section of the Document
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function onBeforeCompileHead(): void
    {
        $app      = $this->getApplication();
        $baseType = $this->params->get('baseType', 'organization');

        $itemId  = (int) $app->getInput()->getInt('id');
        $option  = $app->getInput()->get('option');
        $view    = $app->getInput()->get('view');
        $context = $option . '.' . $view;

        // We need the plugin configured at least once to add structured data
        if (!$app->isClient('site') || !\in_array($baseType, ['organization', 'person']) || !$this->isSupported($context)) {
            return;
        }

        $domain = Uri::root();

        $isPerson = $baseType === 'person';

        $schema = new Registry();

        $baseSchema = [];

        $baseSchema['@context'] = 'https://schema.org';
        $baseSchema['@graph']   = [];

        // Add base tag Person/Organization
        $baseId = $domain . '#/schema/' . ucfirst($baseType) . '/base';

        $siteSchema = [];

        $siteSchema['@type'] = ucfirst($baseType);
        $siteSchema['@id']   = $baseId;

        $name = $this->params->get('name', $app->get('sitename'));

        if ($isPerson && $this->params->get('user') > 0) {
            $user = $this->getUserFactory()->loadUserById($this->params->get('user'));

            $name = $user ? $user->name : '';
        }

        if ($name) {
            $siteSchema['name'] = $name;
        }

        $siteSchema['url'] = $domain;

        // Image
        $image = $this->params->get('image') ? HTMLHelper::_('cleanimageUrl', $this->params->get('image')) : false;

        if ($image !== false) {
            $siteSchema['logo'] = [
                '@type'      => 'ImageObject',
                '@id'        => $domain . '#/schema/ImageObject/logo',
                'url'        => $image->url,
                'contentUrl' => $image->url,
                'width'      => $image->attributes['width'] ?? 0,
                'height'     => $image->attributes['height'] ?? 0,
            ];

            $siteSchema['image'] = ['@id' => $siteSchema['logo']['@id']];
        }

        // Social media accounts
        $socialMedia = (array) $this->params->get('socialmedia', []);

        if (!empty($socialMedia)) {
            $siteSchema['sameAs'] = [];
        }

        foreach ($socialMedia as $social) {
            $siteSchema['sameAs'][] = $social->url;
        }

        $baseSchema['@graph'][] = $siteSchema;

        // Add WebSite
        $webSiteId = $domain . '#/schema/WebSite/base';

        $webSiteSchema = [];

        $webSiteSchema['@type']      = 'WebSite';
        $webSiteSchema['@id']        = $webSiteId;
        $webSiteSchema['url']        = $domain;
        $webSiteSchema['name']       = $app->get('sitename');
        $webSiteSchema['publisher']  = ['@id' => $baseId];

        // We support Finder actions
        $finder = ModuleHelper::getModule('mod_finder');

        if (!empty($finder->id)) {
            $webSiteSchema['potentialAction'] = [
                '@type'       => 'SearchAction',
                'target'      => Route::_('index.php?option=com_finder&view=search&q={search_term_string}', true, Route::TLS_IGNORE, true),
                'query-input' => 'required name=search_term_string',
            ];
        }

        $baseSchema['@graph'][] = $webSiteSchema;

        // Add WebPage
        $webPageId = $domain . '#/schema/WebPage/base';

        $webPageSchema = [];

        $webPageSchema['@type']       = 'WebPage';
        $webPageSchema['@id']         = $webPageId;
        $webPageSchema['url']         = htmlspecialchars(Uri::getInstance()->toString());
        $webPageSchema['name']        = $app->getDocument()->getTitle();
        $webPageSchema['description'] = $app->getDocument()->getDescription();
        $webPageSchema['isPartOf']    = ['@id' => $webSiteId];
        $webPageSchema['about']       = ['@id' => $baseId];
        $webPageSchema['inLanguage']  = $app->getLanguage()->getTag();

        // We support Breadcrumb linking
        $breadcrumbs = ModuleHelper::getModule('mod_breadcrumbs');

        if (!empty($breadcrumbs->id)) {
            $webPageSchema['breadcrumb'] = ['@id' => $domain . '#/schema/BreadcrumbList/' . (int) $breadcrumbs->id];
        }

        $baseSchema['@graph'][] = $webPageSchema;

        if ($itemId > 0) {
            // Load the table data from the database
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__schemaorg'))
                ->where($db->quoteName('itemId') . ' = :itemId')
                ->bind(':itemId', $itemId, ParameterType::INTEGER)
                ->where($db->quoteName('context') . ' = :context')
                ->bind(':context', $context, ParameterType::STRING);

            $result = $db->setQuery($query)->loadObject();

            if ($result) {
                $localSchema = new Registry($result->schema);

                $localSchema->set('@id', $domain . '#/schema/' . str_replace('.', '/', $context) . '/' . (int) $result->itemId);
                $localSchema->set('isPartOf', ['@id' => $webPageId]);

                $itemSchema = $localSchema->toArray();

                $baseSchema['@graph'][] = $itemSchema;
            }
        }

        $schema->loadArray($baseSchema);

        $dispatcher = $this->getDispatcher();
        $event      = new BeforeCompileHeadEvent('onSchemaBeforeCompileHead', [
            'subject' => $schema,
            'context' => $context . '.' . $itemId,
        ]);

        PluginHelper::importPlugin('schemaorg', null, true, $dispatcher);
        $dispatcher->dispatch('onSchemaBeforeCompileHead', $event);

        $data = $schema->get('@graph');

        foreach ($data as $key => $entry) {
            $data[$key] = $this->cleanupSchema($entry);
        }

        $schema->set('@graph', $data);

        $prettyPrint  = JDEBUG ? JSON_PRETTY_PRINT : 0;
        $schemaString = $schema->toString('JSON', ['bitmask' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | $prettyPrint]);

        if ($schemaString !== '{}') {
            $wa = $this->getApplication()->getDocument()->getWebAssetManager();
            $wa->addInlineScript($schemaString, ['name' => 'inline.schemaorg'], ['type' => 'application/ld+json']);
        }
    }

    /**
     * Clean the schema and remove empty fields
     *
     * @param   array  $schema
     *
     * @return  array
     *
     * @since  5.0.0
     */
    private function cleanupSchema($schema)
    {
        $result = [];

        foreach ($schema as $key => $value) {
            if (\is_array($value)) {
                // Subtypes need special handling
                if (!empty($value['@type'])) {
                    if ($value['@type'] === 'ImageObject') {
                        if (!empty($value['url'])) {
                            $value['url'] = $this->prepareImage($value['url']);
                        }

                        if (empty($value['url'])) {
                            $value = [];
                        }
                    } elseif ($value['@type'] === 'Date') {
                        if (!empty($value['value'])) {
                            $value['value'] = $this->prepareDate($value['value']);
                        }

                        if (empty($value['value'])) {
                            $value = [];
                        }
                    }

                    // Go into the array
                    $value = $this->cleanupSchema($value);

                    // We don't save when the array contains only the @type
                    if (empty($value) || \count($value) <= 1) {
                        $value = null;
                    }
                } elseif ($key == 'genericField') {
                    foreach ($value as $field) {
                        $result[$field['genericTitle']] = $field['genericValue'];
                    }

                    continue;
                }
            }

            // No data, no play
            if (empty($value)) {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Check if the current plugin should execute schemaorg related activities
     *
     * @param   string  $context
     *
     * @return   boolean
     *
     * @since   5.0.0
     */
    protected function isSupported($context)
    {
        // We need at least the extension + view for loading the table fields
        if (!str_contains($context, '.')) {
            return false;
        }

        $parts     = explode('.', $context, 2);
        $component = $this->getApplication()->bootComponent($parts[0]);

        if ($component instanceof SchemaorgServiceInterface) {
            return \in_array($context, array_keys($component->getSchemaorgContexts()));
        }

        return false;
    }

    /**
     * The delete event.
     *
     * @param   Object    $event  The event
     *
     * @return  void
     *
     * @since   5.1.3
     */
    public function onContentAfterDelete(Model\AfterDeleteEvent $event)
    {
        if (!$this->isSupported($event->getContext())) {
            return;
        }

        $this->deleteSchemaOrg($event->getItem()->id, $event->getContext());
    }

    /**
     * Delete SchemaOrg record from Database.
     *
     * @param   Integer   $itemId
     * @param   String    $context
     *
     * @return  void
     *
     * @since   5.1.3
     */
    public function deleteSchemaOrg($itemId, $context)
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->delete($db->quoteName('#__schemaorg'))
            ->where($db->quoteName('itemId') . '= :itemId')
            ->where($db->quoteName('context') . '= :context')
            ->bind(':itemId', $itemId, ParameterType::INTEGER)
            ->bind(':context', $context, ParameterType::STRING);

        $db->setQuery($query)->execute();
    }
}
