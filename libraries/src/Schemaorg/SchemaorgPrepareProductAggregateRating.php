<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Schemaorg;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
use Joomla\CMS\Uri\Uri;
/**
 * Prepare Product AggregateRating to be valid for JSON-LD output
 *
 * @since  __DEPLOY_VERSION__
 */
trait SchemaorgPrepareProductAggregateRating
{
    /**
     * Prepare Product AggregateRating
     *
     * @param   array $context
     *
     * @return  ?string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function prepareProductAggregateRating($context)
    {
        [$extension, $view, $id] = explode('.', $context);
        if ($view ==  'article') {
            $baseId   = Uri::root() . '#/schema/';
            $schemaId = $baseId . str_replace('.', '/', $context);

            $component = $this->getApplication()->bootComponent('com_content')->getMVCFactory();
            $model = $component->createModel('Article', 'Site');
            $article = $model->getItem($id);
            return ['@isPartOf' => ['@id' => $schemaId,'@type' => 'Product','name' => $article->title,'aggregateRating' => ['@type'=> "AggregateRating","ratingCount" => (string)$article->rating_count,"ratingValue" => (string)$article->rating]]];
        }
        return false;
    }
}