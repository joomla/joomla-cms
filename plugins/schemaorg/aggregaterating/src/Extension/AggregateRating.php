<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Schemaorg.AggregateRating
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Schemaorg\AggregateRating\Extension;

use Joomla\CMS\Event\Plugin\System\Schemaorg\BeforeCompileHeadEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Schemaorg\SchemaorgPrepareProductAggregateRating;
use Joomla\CMS\Schemaorg\SchemaorgPrepareRecipeAggregateRating;
use Joomla\Event\Priority;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Schemaorg Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
final class AggregateRating extends CMSPlugin implements SubscriberInterface
{
    use SchemaorgPrepareProductAggregateRating;
	use SchemaorgPrepareRecipeAggregateRating;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
 * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * The name of the schema form
     *
     * @var   string
     * @since  __DEPLOY_VERSION__
     */
    protected $pluginName = 'AggregateRating';

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onSchemaBeforeCompileHead' => ['onSchemaBeforeCompileHead', Priority::BELOW_NORMAL],
        ];
    }

    /**
     * Cleanup all BlogPosting types
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
		
        $need_vote = PluginHelper::isEnabled('content', 'vote');

		if (!$need_vote) {
			return;
		}
        foreach ($graph as $key => &$entry) {
            if (!isset($entry['@type']))  {
                continue;
            }
			if ($entry['@type'] == 'Recipe') {
				$rating = $this->prepareRecipeAggregateRating($event->getContext());
				continue;
			}	
			$rating = $this->prepareProductAggregateRating($event->getContext());
		}

	    if ($rating) { 
			$graph[] = $rating;
			$schema->set('@graph', $graph);
		}
    }
}
