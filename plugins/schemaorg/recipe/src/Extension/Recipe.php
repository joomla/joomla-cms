<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Schemaorg.recipe
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Schemaorg\Recipe\Extension;

use Joomla\CMS\Event\Plugin\System\Schemaorg\BeforeCompileHeadEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Schemaorg\SchemaorgPluginTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareDateTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareDurationTrait;
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
final class Recipe extends CMSPlugin implements SubscriberInterface
{
    use SchemaorgPluginTrait;
    use SchemaorgPrepareDateTrait;
    use SchemaorgPrepareDurationTrait;

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
    protected $pluginName = 'Recipe';

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
     * Cleanup all Recipe types
     *
     * @param   BeforeCompileHeadEvent  $event  The given event
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function onSchemaBeforeCompileHead(BeforeCompileHeadEvent $event)
    {
        $schema = $event->getSchema();

        $graph = $schema->get('@graph');

        foreach ($graph as &$entry) {
            if (!isset($entry['@type']) || $entry['@type'] !== 'Recipe') {
                continue;
            }

            if (!empty($entry['datePublished'])) {
                $entry['datePublished'] = $this->prepareDate($entry['datePublished']);
            }

            if (!empty($entry['cookTime'])) {
                $entry['cookTime'] = $this->prepareDuration($entry['cookTime']);
            }

            if (!empty($entry['prepTime'])) {
                $entry['prepTime'] = $this->prepareDuration($entry['prepTime']);
            }

            // Clean recipeIngredient
            if (isset($entry['recipeIngredient']) && \is_array($entry['recipeIngredient'])) {
                $result = [];

                foreach ($entry['recipeIngredient'] as $key => $value) {
                    if (\is_array($value)) {
                        foreach ($value as $k => $v) {
                            $result[] = $v;
                        }

                        continue;
                    }

                    $result[] = $value;
                }

                $entry['recipeIngredient'] = !empty($result) ? $result : null;
            }
        }

        $schema->set('@graph', $graph);
    }
}
