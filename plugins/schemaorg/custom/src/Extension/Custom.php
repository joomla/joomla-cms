<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Schemaorg.Custom
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Schemaorg\Custom\Extension;

use Joomla\CMS\Event\Plugin\System\Schemaorg\BeforeCompileHeadEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareSaveEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Schemaorg\SchemaorgPluginTrait;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Schemaorg Plugin
 *
 * @since   5.1.0
 */
final class Custom extends CMSPlugin implements SubscriberInterface
{
    use SchemaorgPluginTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  5.1.0
     */
    protected $autoloadLanguage = true;

    /**
     * The name of the schema form
     *
     * @var   string
     * @since 5.1.0
     */
    protected $pluginName = 'Custom';

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.1.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onSchemaPrepareForm'       => 'onSchemaPrepareForm',
            'onSchemaBeforeCompileHead' => ['onSchemaBeforeCompileHead', Priority::BELOW_NORMAL],
            'onSchemaPrepareSave'       => 'onSchemaPrepareSave',
        ];
    }

    public function onSchemaPrepareSave(PrepareSaveEvent $event): void
    {
        $subject = $event->getData();

        if (empty($subject->schemaType) || $subject->schemaType !== 'Custom' || !isset($subject->schema)) {
            return;
        }

        try {
            $schema = new Registry($subject->schema);

            $json = (new Registry($schema->get('json')))->toArray();
        } catch (\RuntimeException) {
            $this->getApplication()->enqueueMessage(Text::_('PLG_SCHEMAORG_CUSTOM_JSON_ERROR'), 'error');
            return;
        }

        if (!isset($json['@context']) || !preg_match('#^https://schema.org/?$#', $json['@context']) || !isset($json['@type'])) {
            $this->getApplication()->enqueueMessage(Text::_('PLG_SCHEMAORG_CUSTOM_JSON_ERROR'), 'error');
            return;
        }

        $schema->set('json', json_encode($json, JSON_PRETTY_PRINT));

        $subject->schema = $schema->toString();

        $event->setArgument('subject', $subject);
    }

    /**
     * Cleanup all Custom types
     *
     * @param   BeforeCompileHeadEvent  $event  The given event
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function onSchemaBeforeCompileHead(BeforeCompileHeadEvent $event): void
    {
        $schema = $event->getSchema();

        $graph = $schema->get('@graph');

        foreach ($graph as $i => $entry) {
            if (!isset($entry['@type']) || $entry['@type'] !== 'Custom') {
                continue;
            }

            $json = (new Registry($entry['json']))->toArray();

            if (isset($json['@context'])) {
                unset($json['@context']);
            }

            $graph[$i] = $json;
        }

        $schema->set('@graph', $graph);
    }
}
