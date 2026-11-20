<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\Component\Finder\Site\View\Search\HtmlView $this */
?>
<?php // Display the suggested search if it is different from the current search. ?>
<?php if (($this->suggested && $this->params->get('show_suggested_query', 1)) || ($this->explained && $this->params->get('show_explained_query', 1))) : ?>
    <div id="search-query-explained" class="com-finder__explained">
        <?php // Display the suggested search query. ?>
        <?php if ($this->suggested && $this->params->get('show_suggested_query', 1)) : ?>
            <?php // Replace the base query string with the suggested query string. ?>
            <?php $uri = Uri::getInstance($this->query->toUri()); ?>
            <?php $uri->setVar('q', $this->suggested); ?>
            <?php // Compile the suggested query link. ?>
            <?php $linkUrl = Route::_($uri->toString(['path', 'query'])); ?>
            <?php $link = '<a href="' . $linkUrl . '">' . $this->escape($this->suggested) . '</a>'; ?>
            <?php echo Text::sprintf('COM_FINDER_SEARCH_SIMILAR', $link); ?>
        <?php elseif ($this->explained && $this->params->get('show_explained_query', 1)) : ?>
            <?php // Display the explained search query. ?>
            <p role="alert">
                <?php echo Text::plural('COM_FINDER_QUERY_RESULTS', $this->total, $this->explained); ?>
            </p>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php // Display the 'no results' message and exit the template. ?>
<?php if (($this->total === 0) || ($this->total === null)) : ?>
    <div id="search-result-empty" class="com-finder__empty">
        <h2><?php echo Text::_('COM_FINDER_SEARCH_NO_RESULTS_HEADING'); ?></h2>
        <?php $multilang = Factory::getApplication()->getLanguageFilter() ? '_MULTILANG' : ''; ?>
        <p><?php echo Text::sprintf('COM_FINDER_SEARCH_NO_RESULTS_BODY' . $multilang, $this->escape($this->query->input)); ?></p>
    </div>
    <?php // Exit this template. ?>
    <?php return; ?>
<?php endif; ?>
<?php // Display the 'Sort By' drop-down. ?>
<?php if ($this->params->get('show_sort_order', 0) && !empty($this->sortOrderFields) && !empty($this->results)) : ?>
    <div id="search-sorting" class="com-finder__sorting">
        <?php echo $this->loadTemplate('sorting'); ?>
    </div>
<?php endif; ?>
<?php // Activate the highlighter if enabled. ?>
<?php if (!empty($this->query->highlight) && $this->params->get('highlight_terms', 1)) : ?>
    <?php
        // Allow a maximum of 10 tokens to be highlighted. Otherwise the URL can get too long.
        $this->getDocument()->getWebAssetManager()->useScript('highlight');
        $this->getDocument()->addScriptOptions(
            'highlight',
            [[
                    'class'      => 'js-highlight',
                    'highLight'  => array_slice($this->query->highlight, 0, 10),
            ]]
        );
    ?>
<?php endif; ?>
<?php // Display a list of results ?>
<ul id="search-result-list" class="js-highlight com-finder__results-list" start="<?php echo (int) $this->pagination->limitstart + 1; ?>">
    <?php $this->baseUrl = Uri::getInstance()->toString(['scheme', 'host', 'port']); ?>
    <?php foreach ($this->results as $i => $result) : ?>
        <?php $this->result = &$result; ?>
        <?php $this->result->counter = $i + 1; ?>
        <?php $layout = $this->getLayoutFile($this->result->layout); ?>
        <?php echo $this->loadTemplate($layout); ?>
    <?php endforeach; ?>
</ul>
<?php // Display the pagination ?>
<div class="com-finder__navigation search-pagination">
    <?php if ($this->params->get('show_pagination', 1) > 0) : ?>
    <div class="com-finder__pagination w-100">
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
    <?php endif; ?>
    <?php if ($this->params->get('show_pagination_results', 1) > 0) : ?>
        <div class="com-finder__counter search-pages-counter">
            <?php // Prepare the pagination string.  Results X - Y of Z ?>
            <?php $start = (int) $this->pagination->limitstart + 1; ?>
            <?php $total = (int) $this->pagination->total; ?>
            <?php $limit = (int) $this->pagination->limit * $this->pagination->pagesCurrent; ?>
            <?php $limit = (int) min($limit, $total); ?>
            <?php echo Text::sprintf('COM_FINDER_SEARCH_RESULTS_OF', $start, $limit, $total); ?>
        </div>
    <?php endif; ?>
</div>
