<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.schemaorg
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Schemaorg\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Schemaorg\SchemaorgServiceInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactory;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Schemaorg System Plugin
 *
 * @since  4.0.0
 */
final class Schemaorg extends CMSPlugin implements SubscriberInterface
{
    // use SchemaorgPluginTrait;
    use DatabaseAwareTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
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

        $dispatcher = $app->getDispatcher();

        $event   = AbstractEvent::create(
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
     * @since   __DEPLOY_VERSION__
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

        //Load the form fields
        $form->loadFile(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms/schemaorg.xml');

        // The user should configurate the plugin first
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
     * @since   __DEPLOY_VERSION__
     *
     */
    public function onContentAfterSave(EventInterface $event)
    {
        $context  = $event->getArgument('0');
        $table    = $event->getArgument('1');
        $isNew    = $event->getArgument('2');
        $data     = $event->getArgument('3');
        $registry = new Registry($data);

        if (!$this->isSupported($context)) {
            return true;
        }

        $dispatcher = Factory::getApplication()->getDispatcher();

        $event   = AbstractEvent::create(
            'onSchemaAfterSave',
            [
                'subject'   => $this,
                'extension' => $context,
                'table'     => $table,
                'isNew'     => $isNew,
                'data'      => $registry,
            ]
        );

        PluginHelper::importPlugin('schemaorg');
        $eventResult = $dispatcher->dispatch('onSchemaAfterSave', $event);

        return true;
    }


    /**
     * This event is triggered before the framework creates the Head section of the Document
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onBeforeCompileHead()
    {
        $app      = $this->getApplication();
        $baseType = $this->params->get('baseType');

        $itemId  = (int) $app->getInput()->getInt('id');
        $option  = $app->getInput()->get('option');
        $view    = $app->getInput()->get('view');
        $context = $option . '.' . $view;

        // We need the plugin configurated at least once to add structured data
        if (!$app->isClient('site') || !in_array($baseType, ['organization', 'person']) || !$this->isSupported($context)) {
            return;
        }

        $domain = Uri::root();
        $url    = htmlspecialchars(Uri::getInstance()->toString());

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

        $webPageSchema['@type']       = 'WebSite';
        $webPageSchema['@id']         = $webPageId;
        $webPageSchema['url']         = $url;
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

                $localSchema->set('@id', $domain . '#/schema/' . ucfirst($result->schemaType) . '/' . (int) $result->itemId);
                $localSchema->set('@isPartOf', ['@id' => $webPageId]);

                $baseSchema['@graph'][] = $localSchema->toArray();
            }
        }

        $schema->loadArray($baseSchema);

        $event = AbstractEvent::create(
            'onSchemaBeforeCompileHead',
            [
                'subject' => $this,
                'schema'  => $schema,
            ]
        );

        PluginHelper::importPlugin('schemaorg');
        $eventResult = $app->getDispatcher()->dispatch('onSchemaBeforeCompileHead', $event);

        $schemaString = $schema->toString();

        if ($schemaString !== '{}') {
            $wa = $this->getApplication()->getDocument()->getWebAssetManager();
            $wa->addInlineScript($schemaString,  ['position' => 'after'], ['type' => 'application/ld+json']);
        }
    }

    /**
     * Check if the current plugin should execute schemaorg related activities
     *
     * @param   string  $context
     *
     * @return boolean
     *
     * @since   _DEPLOY_VERSION__
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
