<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/*
* This segment of code sets up the autocompleter.
*/
if ($this->params->get('show_autosuggest', 1))
{
	JHtml::_('script', 'vendor/awesomplete/awesomplete.min.js', array('version' => 'auto', 'relative' => true));
	JFactory::getDocument()->addScriptOptions('finder-search', array('url' => JRoute::_('index.php?option=com_finder&task=suggestions.suggest&format=json&tmpl=component')));
}
?>

<form action="<?php echo JRoute::_($this->query->toUri()); ?>" method="get" class="js-finder-searchform form-inline">
	<?php echo $this->getFields(); ?>

	<?php
	/*
	 * DISABLED UNTIL WEIRD VALUES CAN BE TRACKED DOWN.
	 */
	if (false && $this->state->get('list.ordering') !== 'relevance_dsc') : ?>
		<input type="hidden" name="o" value="<?php echo $this->escape($this->state->get('list.ordering')); ?>" />
	<?php endif; ?>

	<fieldset class="word">
		<label for="q">
			<?php echo JText::_('COM_FINDER_SEARCH_TERMS'); ?>
		</label>
		<input type="text" name="q" class="js-finder-search-query inputbox" size="30" value="<?php echo $this->escape($this->query->input); ?>" />
		<?php if ($this->escape($this->query->input) != '' || $this->params->get('allow_empty_search')) : ?>
			<button name="Search" type="submit" class="btn btn-primary"><span class="icon-search icon-white"></span> <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
		<?php else : ?>
			<button name="Search" type="submit" class="btn btn-primary disabled"><span class="icon-search icon-white"></span> <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
		<?php endif; ?>
		<?php if ($this->params->get('show_advanced', 1)) : ?>
			<a href="#advancedSearch" data-toggle="collapse" class="btn"><span class="icon-list"></span> <?php echo JText::_('COM_FINDER_ADVANCED_SEARCH_TOGGLE'); ?></a>
		<?php endif; ?>
	</fieldset>

	<?php if ($this->params->get('show_advanced', 1)) : ?>
		<div class="js-finder-advanced collapse<?php if ($this->params->get('expand_advanced', 0)) echo ' in'; ?>">
			<hr>
			<?php if ($this->params->get('show_advanced_tips', 1)) : ?>
				<div class="advanced-search-tip">
					<?php echo JText::_('COM_FINDER_ADVANCED_TIPS'); ?>
				</div>
				<hr>
			<?php endif; ?>
			<div id="finder-filter-window">
				<?php echo JHtml::_('filter.select', $this->query, $this->params); ?>
			</div>
		</div>
	<?php endif; ?>
</form>
