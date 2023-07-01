<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.schemaorg
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Schemaorg\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Schemaorg\SchemaorgPluginTrait;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactory;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use stdClass;

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
     * @since   4.0.0
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
     * @since   4.0.0
     *
     */
    public function onContentPrepareData(EventInterface $event)
    {
        $context = $event->getArgument('0');
        $data    = $event->getArgument('1');

        $app = $this->getApplication();

        if ($app->isClient('site') || (false && !$this->isSupported($context))) {
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
     * @since   4.0.0
     */
    public function onContentPrepareForm(EventInterface $event)
    {
        /**
         * @var Form
         */
        $form    = $event->getArgument('0');
        $context = $form->getName();

        $app = $this->getApplication();

        if (!$app->isClient('administrator') || (false && !$this->isSupported($context))) {
            return true;
        }

        //Load the form fields
        $form->loadFile(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms/schemaorg.xml');

        if (!$this->params->get('baseType')) {

            $form->removeField('schemaType', 'schema');

            $plugin = PluginHelper::getPlugin('system', 'schemaorg');

            $user = $this->getApplication()->getIdentity();

            $infoText = Text::_('PLG_SYSTEM_SCHEMAORG_FIELD_SCHEMA_DESCRIPTION_NOT_CONFIGURATED');

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
     */
    public function onContentAfterSave(EventInterface $event)
    {
        $context  = $event->getArgument('0');
        $table    = $event->getArgument('1');
        $isNew    = $event->getArgument('2');
        $data     = $event->getArgument('3');
        $registry = new Registry($data);

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
     * @since   4.0.0
     */
    public function onBeforeCompileHead()
    {
        $app = $this->getApplication();
        $baseType = $this->params->get('baseType');

        // We need the plugin configurated at least once to add structured data
        if (!$app->isClient('site') || !in_array($baseType, ['organization', 'person'])) {

            return;
        }

        $itemId  = (int) $app->getInput()->getInt('id');
        $option  = $app->getInput()->get('option');
        $view    = $app->getInput()->get('view');
        $context = $option . '.' . $view;

        $domain = Uri::root();
        $url    = Uri::getInstance()->toString();

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

        $baseSchema['@graph'][] = $siteSchema;

        // Add WebSite
        $webSiteId = $domain . '#/schema/WebSite/base';

        $webSiteSchema = [];

        $webSiteSchema['@type']      = 'WebSite';
        $webSiteSchema['@id']        = $webSiteId;
        $webSiteSchema['url']        = $domain;
        $webSiteSchema['name']       = $app->get('sitename');
        $webSiteSchema['publisher']  = ['@id' => $baseId];

        $baseSchema['@graph'][] = $webSiteSchema;

        // Add WebPage
        $webPageId = $domain . '#/schema/WebPage/base';

        $webPageSchema = [];

        $webPageSchema['@type']       = 'WebSite';
        $webPageSchema['@id']         = $webPageId;
        $webPageSchema['url']         = $url;
        $webPageSchema['name']        = $app->getDocument()->getTitle();
        $webPageSchema['description'] = $app->getDocument()->getDescription();
        $webPageSchema['isPartOf'] = ['@id' => $webSiteId];

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
                    'schema'  => $schema
                ]
        );

        print_r($schema->toString());

        PluginHelper::importPlugin('schemaorg');
        $eventResult = $app->getDispatcher()->dispatch('onSchemaBeforeCompileHead', $event);

        $schemaString = $schema->toString();

        if ($schemaString !== '{}') {

            $wa = $this->getApplication()->getDocument()->getWebAssetManager();
            $wa->addInlineScript($schemaString,  ['position' => 'after'], ['type' => 'application/ld+json']);
        }
    }
}
