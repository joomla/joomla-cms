<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Schemaorg.book
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Schemaorg\Book\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Schemaorg\SchemaorgPluginTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareDateTrait;
use Joomla\Event\Event;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Schemaorg Plugin
 *
 * @since  5.0.0
 */
final class Book extends CMSPlugin implements SubscriberInterface
{
    use SchemaorgPluginTrait;
    use SchemaorgPrepareDateTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  5.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * The name of the schema form
     *
     * @var   string
     * @since 5.0.0
     */
    protected $pluginName = 'Book';

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        $subscribed = SchemaorgPluginTrait::getSubscribedEvents();

        $subscribed['onSchemaBeforeCompileHead'] = ['onSchemaBeforeCompileHead', Priority::BELOW_NORMAL];

        return $subscribed;
    }

    /**
     * Cleanup all Book types
     *
     * @param   Event  $event  The given event
     *
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onSchemaBeforeCompileHead(Event $event)
    {
        $schema = $event->getArgument('subject');

        $graph = $schema->get('@graph');

        foreach ($graph as &$entry) {
            if (!isset($entry['@type']) || $entry['@type'] !== 'Book') {
                continue;
            }

            if (!empty($entry['datePublished'])) {
                $entry['datePublished'] = $this->prepareDate($entry['datePublished']);
            }
        }

        $schema->set('@graph', $graph);
    }
}
