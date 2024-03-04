<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Schemaorg;

use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Prepare AggregateRating to be valid for JSON-LD output
 *
 * @since  __DEPLOY_VERSION__
 */
trait SchemaorgPrepareAggregateRating
{
    /**
     * Prepare AggregateRating
     *
     * @param   string $context
     *
     * @return  ?string
     *
     * @since  __DEPLOY_VERSION__
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
}
