<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.schemaorg
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Schemaorg\Extension;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
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
use Joomla\CMS\User\UserFactory;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\EventInterface;
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
final class Schemaorg extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use SchemaorgPrepareImageTrait;
    use SchemaorgPrepareDateTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  5.0.0
     */
    protected $autoloadLanguage = true;

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
        ];
    }

    /**
     * Runs on content preparation
     *
     * @param   EventInterface  $event  The event
     *
     * @since   5.0.0
     *
     */
    public function onContentPrepareData(EventInterface $event)
    {
        $context = $event->getArgument('0');
        $data    = $event->getArgument('1');

        $app = $this->getApplication();

        if ($app->isClient('site') || !$this->isSupported($context)) {
            return true;
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
                return false;
            }

            $schemaType                 = $results['schemaType'];
            $data->schema['schemaType'] = $schemaType;

            $schema = new Registry($results['schema']);

            $data->schema[$schemaType] = $schema->toArray();
        }

        $dispatcher = Factory::getApplication()->getDispatcher();

        $event = AbstractEvent::create(
            'onSchemaPrepareData',
            [
                'subject' => $data,
                'context' => $context,
            ]
        );

        PluginHelper::importPlugin('schemaorg');
        $eventResult = $dispatcher->dispatch('onSchemaPrepareData', $event);

        return true;
    }

    /**
     * The form event.
     *
     * @param   EventInterface  $event  The event
     *
     * @since   5.0.0
     */
    public function onContentPrepareForm(EventInterface $event)
    {
        /**
         * @var Form
         */
        $form    = $event->getArgument('0');
        $context = $form->getName();

        $app = $this->getApplication();

        if (!$app->isClient('administrator') || !$this->isSupported($context)) {
            return true;
        }

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

            return true;
        }

        $dispatcher = Factory::getApplication()->getDispatcher();

        $event   = AbstractEvent::create(
            'onSchemaPrepareForm',
            [
                'subject' => $form,
            ]
        );

        PluginHelper::importPlugin('schemaorg');
        $eventResult = $dispatcher->dispatch('onSchemaPrepareForm', $event);

        return true;
    }

    /**
     * Saves form field data in the database
     *
     * @param   EventInterface $event
     *
     * @return  boolean
     *
     * @since   5.0.0
     *
     */
    public function onContentAfterSave(EventInterface $event)
    {
        $context  = $event->getArgument('0');
        $table    = $event->getArgument('1');
        $isNew    = $event->getArgument('2');
        $data     = $event->getArgument('3');

        $app = $this->getApplication();
        $db  = $this->getDatabase();

        if (!$app->isClient('administrator') || !$this->isSupported($context)) {
            return true;
        }

        $itemId = (int) $table->id;

        if (empty($data['schema']) || empty($data['schema']['schemaType']) || $data['schema']['schemaType'] === 'None') {
            $query = $db->getQuery(true);

            $query->delete($db->quoteName('#__schemaorg'))
                ->where($db->quoteName('itemId') . '= :itemId')
                ->bind(':itemId', $itemId, ParameterType::INTEGER)
                ->where($db->quoteName('context') . '= :context')
                ->bind(':context', $context, ParameterType::STRING);

            $db->setQuery($query)->execute();

            return true;
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

        PluginHelper::importPlugin('schemaorg');

        $dispatcher = $app->getDispatcher();

        $event   = AbstractEvent::create(
            'onSchemaPrepareSave',
            [
                'subject' => $entry,
                'context' => $context,
                'table'   => $table,
                'isNew'   => $isNew,
                'schema'  => $data['schema'],
            ]
        );

        $eventResult = $dispatcher->dispatch('onSchemaPrepareSave', $event);

        if (!isset($entry->schemaType)) {
            return true;
        }

        if (!empty($entry->id)) {
            $db->updateObject('#__schemaorg', $entry, 'id');
        } else {
            $db->insertObject('#__schemaorg', $entry, 'id');
        }

        return true;
    }

    /**
     * This event is triggered before the framework creates the Head section of the Document
     *
     * @since   5.0.0
     */
    public function onBeforeCompileHead()
    {
        $app      = $this->getApplication();
        $baseType = $this->params->get('baseType');

        $itemId  = (int) $app->getInput()->getInt('id');
        $option  = $app->getInput()->get('option');
        $view    = $app->getInput()->get('view');
        $context = $option . '.' . $view;

        // We need the plugin configured at least once to add structured data
        if (!$app->isClient('site') || !in_array($baseType, ['organization', 'person']) || !$this->isSupported($context)) {
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

        $siteSchema['@type'] = ucfirst($this->params->get('baseType'));
        $siteSchema['@id']   = $baseId;

        $name = $this->params->get('name');

        if ($isPerson && $this->params->get('userId') > 0) {
            $user = Factory::getContainer()->get(UserFactory::class)->loadUserById($this->params->get('userId'));

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

        $event = AbstractEvent::create(
            'onSchemaBeforeCompileHead',
            [
                'subject' => $schema,
                'context' => $context . '.' . $itemId,
            ]
        );

        PluginHelper::importPlugin('schemaorg');
        $eventResult = $app->getDispatcher()->dispatch('onSchemaBeforeCompileHead', $event);

        $data = $schema->get('@graph');

        foreach ($data as $key => $entry) {
            $data[$key] = $this->cleanupSchema($entry);
        }

        $schema->set('@graph', $data);

        $prettyPrint  = JDEBUG ? JSON_PRETTY_PRINT : false;
        $schemaString = $schema->toString('JSON', ['bitmask' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | $prettyPrint]);

        if ($schemaString !== '{}') {
            $wa = $this->getApplication()->getDocument()->getWebAssetManager();
            $wa->addInlineScript($schemaString, ['position' => 'after'], ['type' => 'application/ld+json']);
        }
    }

    /**
     * Clean the schema and remove empty fields
     *
     * @param   array  $schema
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    private function cleanupSchema($schema)
    {
        $result = [];

        foreach ($schema as $key => $value) {
            if (is_array($value)) {
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
                    if (empty($value) || count($value) <= 1) {
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
        $parts = explode('.', $context);

        // We need at least the extension + view for loading the table fields
        if (count($parts) < 2) {
            return false;
        }

        $component = $this->getApplication()->bootComponent($parts[0]);

        return $component instanceof SchemaorgServiceInterface;
    }
}
