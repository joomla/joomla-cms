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
use Joomla\CMS\Plugin\PluginHelper;
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
    use SchemaorgPluginTrait;

    /**
     * @var    \Joomla\Database\DatabaseDriver
     *
     */
    protected $db;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Loads the CMS Application for direct access
     *
     * @var   CMSApplicationInterface
     * @since 4.0.0
     */
    protected $app;

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

        if ($this->app->isClient('site') || !$this->isSupported($context)) {
            return true;
        }

        $dispatcher = $this->app->getDispatcher();

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
        $form    = $event->getArgument('0');
        $context = $form->getName();

        if ($this->app->isClient('site') || !$this->isSupported($context)) {
            return true;
        }

        //Load the form fields
        $form->loadFile(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms/schemaorg.xml');

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
        $dispatcher = Factory::getApplication()->getDispatcher();

        $event   = AbstractEvent::create(
            'onSchemaBeforeCompileHead',
            [
                    'subject' => $this,
                ]
        );

        PluginHelper::importPlugin('schemaorg');
        $eventResult = $dispatcher->dispatch('onSchemaBeforeCompileHead', $event);
    }
}
