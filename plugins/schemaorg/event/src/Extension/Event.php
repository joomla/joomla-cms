<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Schemaorg.event
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Schemaorg\Event\Extension;

use Joomla\CMS\Event\Plugin\System\Schemaorg\BeforeCompileHeadEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Schemaorg\SchemaorgPluginTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareDateTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareImageTrait;
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
final class Event extends CMSPlugin implements SubscriberInterface
{
    use SchemaorgPluginTrait;
    use SchemaorgPrepareDateTrait;
    use SchemaorgPrepareImageTrait;

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
    protected $pluginName = 'Event';

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
            'onSchemaPrepareForm'       => 'onSchemaPrepareForm',
            'onSchemaBeforeCompileHead' => ['onSchemaBeforeCompileHead', Priority::BELOW_NORMAL],
        ];
    }

    /**
     * Cleanup all Event types
     *
     * @param   BeforeCompileHeadEvent  $event  The given event
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function onSchemaBeforeCompileHead(BeforeCompileHeadEvent $event): void
    {
        $schema = $event->getSchema();

        $graph = $schema->get('@graph');

        foreach ($graph as &$entry) {
            if (!isset($entry['@type']) || $entry['@type'] !== 'Event') {
                continue;
            }

            if (!empty($entry['startDate'])) {
                $entry['startDate'] = $this->prepareDate($entry['startDate']);
            }

            if (!empty($entry['image'])) {
                $entry['image'] = $this->prepareImage($entry['image']);
            }
        }

        $schema->set('@graph', $graph);
    }
}
