<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Activate the highlighter if enabled.
if (!empty($this->query->highlight) && $this->params->get('highlight_terms', 1)) {
	JHtml::_('behavior.highlighter', $this->query->highlight);
}

// Get the application object.
$app = JFactory::getApplication();

// Display the suggested search if it is different from the current search.
if (($this->suggested && $this->params->get('show_suggested_query', 1)) || ($this->explained && $this->params->get('show_explained_query', 1))):
?>
	<div id="search-query-explained">
		<?php
		// Display the suggested search query.
		if ($this->suggested && $this->params->get('show_suggested_query', 1))
		{
			// Replace the base query string with the suggested query string.
			$uri = JUri::getInstance($this->query->toURI());
			$uri->setVar('q', $this->suggested);

			// Compile the suggested query link.
			$link	= '<a href="' . JRoute::_($uri->toString(array('path', 'query'))) . '">'
					. $this->escape($this->suggested)
					. '</a>';

			echo JText::sprintf('COM_FINDER_SEARCH_SIMILAR', $link);
		}
		// Display the explained search query.
		elseif ($this->explained && $this->params->get('show_explained_query', 1)) {
			echo $this->explained;
		}
		?>
	</div>
<?php
endif;

if ($this->total == 0):
?>
	<div id="search-result-empty">
		<h2><?php echo JText::_('COM_FINDER_SEARCH_NO_RESULTS_HEADING'); ?></h2>
		<?php if ($app->getLanguageFilter()) : ?>
		<p><?php echo JText::sprintf('COM_FINDER_SEARCH_NO_RESULTS_BODY_MULTILANG', $this->escape($this->query->input)); ?></p>
		<?php else : ?>
		<p><?php echo JText::sprintf('COM_FINDER_SEARCH_NO_RESULTS_BODY', $this->escape($this->query->input)); ?></p>
		<?php endif; ?>
	</div>
<?php
else:
	// Prepare the pagination string.  Results X - Y of Z
	$start	= (int) $this->pagination->get('limitstart')+1;
	$total	= (int) $this->pagination->get('total');
	$limit	= (int) $this->pagination->get('limit') * $this->pagination->get('pages.current');
	$limit	= (int) ($limit > $total ? $total : $limit);
	$pages	= JText::sprintf('COM_FINDER_SEARCH_RESULTS_OF', $start, $limit, $total);
?>
	<br id="highlighter-start" />
	<dl class="search-results<?php echo $this->pageclass_sfx; ?>">
		<?php
		for ($i = 0, $n = count($this->results); $i < $n; $i++):
			$this->result	= &$this->results[$i];
			$layout			= $this->getLayoutFile($this->result->layout);
		?>
		<?php echo $this->loadTemplate($layout); ?>
		<?php
		endfor;
		?>
	</dl>
	<br id="highlighter-end" />

	<div class="search-pagination">
		<div class="pagination">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
		<div class="search-pages-counter">
			<?php echo $pages; ?>
		</div>
	</div>
<?php
endif;
