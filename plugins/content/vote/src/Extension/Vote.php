<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\Vote\Extension;

use Joomla\CMS\Event\Content\AfterDisplayEvent;
use Joomla\CMS\Event\Content\BeforeDisplayEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Vote plugin.
 *
 * @since  1.5
 */
final class Vote extends CMSPlugin implements SubscriberInterface
{
    /**
     * @var    \Joomla\CMS\Application\CMSApplication
     *
     * @since  3.7.0
     *
     * @deprecated 4.4.0 will be removed in 6.0 as it is there only for layout overrides
     *             Use getApplication() instead
     */
    protected $app;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentBeforeDisplay'    => 'onContentBeforeDisplay',
            'onContentAfterDisplay'     => 'onContentAfterDisplay',
            'onSchemaBeforeCompileHead' => 'onSchemaBeforeCompileHead',
        ];
    }

    /**
     * Displays the voting area when viewing an article and the voting section is displayed before the article.
     * Add HTML string containing code for the votes if in com_content.
     *
     * @param   BeforeDisplayEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onContentBeforeDisplay(BeforeDisplayEvent $event)
    {
        if ($this->params->get('position', 'top') !== 'top') {
            return;
        }

        $event->addResult(
            $this->displayVotingData($event->getContext(), $event->getItem(), $event->getParams(), $event->getPage())
        );
    }

    /**
     * Displays the voting area when viewing an article and the voting section is displayed after the article.
     * Add HTML string containing code for the votes if in com_content.
     *
     * @param   AfterDisplayEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onContentAfterDisplay(AfterDisplayEvent $event)
    {
        if ($this->params->get('position', 'top') !== 'bottom') {
            return;
        }

        $event->addResult(
            $this->displayVotingData($event->getContext(), $event->getItem(), $event->getParams(), $event->getPage())
        );
    }

    /**
     * Displays the voting area
     *
     * @param   string   $context  The context of the content being passed to the plugin
     * @param   object   $row     The article object
     * @param   object   $params  The article params
     * @param   integer  $page     The 'page' number
     *
     * @return  string  HTML string containing code for the votes if in com_content else empty string
     *
     * @since   3.7.0
     */
    private function displayVotingData($context, $row, $params, $page)
    {
        $parts = explode('.', $context);

        if ($parts[0] !== 'com_content') {
            return '';
        }

        if (empty($params) || !$params->get('show_vote', null)) {
            return '';
        }

        // Load plugin language files only when needed (ex: they are not needed if show_vote is not active).
        $this->loadLanguage();

        // Get the path for the rating summary layout file
        $path = PluginHelper::getLayoutPath('content', 'vote', 'rating');

        // Render the layout
        ob_start();
        include $path;
        $html = ob_get_clean();

        if ($this->getApplication()->getInput()->getString('view', '') === 'article' && $row->state == 1) {
            // Get the path for the voting form layout file
            $path = PluginHelper::getLayoutPath('content', 'vote', 'vote');

            // Render the layout
            ob_start();
            include $path;
            $html .= ob_get_clean();
        }

        return $html;
    }

    /**
     * Create SchemaOrg AggregateRating
     *
     * @param   Schemaorg\BeforeCompileHeadEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   5.2.0
     */
    public function onSchemaBeforeCompileHead(Schemaorg\BeforeCompileHeadEvent $event): void
    {
        $context  = $event->getContext();
        $schema   = $event->getSchema();
        $graph    = $schema->get('@graph');
        $baseId   = Uri::root() . '#/schema/';
        $schemaId = $baseId . str_replace('.', '/', $context);

        foreach ($graph as &$entry) {
            if (!isset($entry['@type']) || !isset($entry['@id'])) {
                continue;
            }
            if ($entry['@id'] !== $schemaId) {
                continue;
            }

            switch ($entry['@type']) {
                case 'Book':
                case 'Brand':
                case 'CreativeWork':
                case 'Event':
                case 'Offer':
                case 'Organization':
                case 'Place':
                case 'Product':
                case 'Recipe':
                case 'Service':
                    $rating = $this->prepareAggregateRating($context);
                    break;
                case 'Article':
                case 'BlogPosting':
                    $rating = $this->prepareProductAggregateRating($context);
                    break;
            }
        }

        if (isset($rating) && $rating) {
            $graph[] = $rating;
            $schema->set('@graph', $graph);
        }
    }

    /**
     * Prepare AggregateRating
     *
     * @param   string $context
     *
     * @return  ?string
     *
     * @since  5.2.0
     */
    protected function prepareAggregateRating($context)
    {
        [$extension, $view, $id] = explode('.', $context);

        if ($view === 'article') {
            $baseId   = Uri::root() . '#/schema/';
            $schemaId = $baseId . str_replace('.', '/', $context);

            $component = $this->getApplication()->bootComponent('com_content')->getMVCFactory();
            $model     = $component->createModel('Article', 'Site');
            $article   = $model->getItem($id);
            if ($article->rating_count > 0) {
                return ['@isPartOf' => ['@id' => $schemaId, 'aggregateRating' => ['@type' => 'AggregateRating','ratingCount' => (string) $article->rating_count,'ratingValue' => (string) $article->rating]]];
            }
        }

        return false;
    }

    /**
     * Prepare Product AggregateRating
     *
     * @param   string $context
     *
     * @return  ?string
     *
     * @since  5.2.0
     */
    protected function prepareProductAggregateRating($context)
    {
        [$extension, $view, $id] = explode('.', $context);

        if ($view === 'article') {
            $baseId   = Uri::root() . '#/schema/';
            $schemaId = $baseId . str_replace('.', '/', $context);

            $component = $this->getApplication()->bootComponent('com_content')->getMVCFactory();
            $model     = $component->createModel('Article', 'Site');
            $article   = $model->getItem($id);
            if ($article->rating_count > 0) {
                return ['@isPartOf' => ['@id' => $schemaId, '@type' => 'Product', 'name' => $article->title, 'aggregateRating' => ['@type' => 'AggregateRating', 'ratingCount' => (string) $article->rating_count, 'ratingValue' => (string) $article->rating]]];
            }
        }

        return false;
    }
}
