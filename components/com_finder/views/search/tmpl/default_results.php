<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<?php // Display the suggested search if it is different from the current search. ?>
<?php if (($this->suggested && $this->params->get('show_suggested_query', 1)) || ($this->explained && $this->params->get('show_explained_query', 1))) : ?>
	<div id="search-query-explained">
		<?php // Display the suggested search query. ?>
		<?php if ($this->suggested && $this->params->get('show_suggested_query', 1)) : ?>
			<?php // Replace the base query string with the suggested query string. ?>
			<?php $uri = JUri::getInstance($this->query->toUri()); ?>
			<?php $uri->setVar('q', $this->suggested); ?>
			<?php // Compile the suggested query link. ?>
			<?php $linkUrl = JRoute::_($uri->toString(array('path', 'query'))); ?>
			<?php $link = '<a href="' . $linkUrl . '">' . $this->escape($this->suggested) . '</a>'; ?>
			<?php echo JText::sprintf('COM_FINDER_SEARCH_SIMILAR', $link); ?>
		<?php elseif ($this->explained && $this->params->get('show_explained_query', 1)) : ?>
			<?php // Display the explained search query. ?>
			<?php echo $this->explained; ?>
		<?php endif; ?>
	</div>
<?php endif; ?>
<?php // Display the 'no results' message and exit the template. ?>
<?php if (($this->total === 0) || ($this->total === null)) : ?>
	<div id="search-result-empty">
		<h2><?php echo JText::_('COM_FINDER_SEARCH_NO_RESULTS_HEADING'); ?></h2>
		<?php $multilang = JFactory::getApplication()->getLanguageFilter() ? '_MULTILANG' : ''; ?>
		<p><?php echo JText::sprintf('COM_FINDER_SEARCH_NO_RESULTS_BODY' . $multilang, $this->escape($this->query->input)); ?></p>
	</div>
	<?php // Exit this template. ?>
	<?php return; ?>
<?php endif; ?>
<?php // Activate the highlighter if enabled. ?>
<?php if (!empty($this->query->highlight) && $this->params->get('highlight_terms', 1)) : ?>
	<?php JHtml::_('behavior.highlighter', $this->query->highlight); ?>
<?php endif; ?>
<?php // Display a list of results ?>
<br id="highlighter-start" />
<ul class="search-results<?php echo $this->pageclass_sfx; ?> list-striped">
	<?php $this->baseUrl = JUri::getInstance()->toString(array('scheme', 'host', 'port')); ?>
	<?php foreach ($this->results as $result) : ?>
		<?php $this->result = &$result; ?>
		<?php $layout = $this->getLayoutFile($this->result->layout); ?>
		<?php echo $this->loadTemplate($layout); ?>
	<?php endforeach; ?>
</ul>
<br id="highlighter-end" />
<?php // Display the pagination ?>
<div class="search-pagination">
	<div class="pagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<div class="search-pages-counter">
		<?php // Prepare the pagination string.  Results X - Y of Z ?>
		<?php $start = (int) $this->pagination->get('limitstart') + 1; ?>
		<?php $total = (int) $this->pagination->get('total'); ?>
		<?php $limit = (int) $this->pagination->get('limit') * $this->pagination->get('pages.current'); ?>
		<?php $limit = (int) ($limit > $total ? $total : $limit); ?>
		<?php echo JText::sprintf('COM_FINDER_SEARCH_RESULTS_OF', $start, $limit, $total); ?>
	</div>
</div>
