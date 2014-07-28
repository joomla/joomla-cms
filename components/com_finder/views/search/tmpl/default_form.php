<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;
?>

<script type="text/javascript">
	jQuery(function($) {
<?php if ($this->params->get('show_advanced', 1)) : ?>
		/*
		 * This segment of code adds the slide effect to the advanced search box.
		 */
		var $searchSlider = $('#advanced-search');
		if ($searchSlider.length)
		{
			<?php if (!$this->params->get('expand_advanced', 0)) : ?>
			$searchSlider.hide();
			<?php endif; ?>

			$('#advanced-search-toggle').on('click', function(e)
			{
				e.stopPropagation();
				e.preventDefault();
				$searchSlider.slideToggle();
			});
		}

		/*
		 * This segment of code disables select boxes that have no value when the
		 * form is submitted so that the URL doesn't get blown up with null values.
		 */
		if ($('#finder-search').length)
		{
			$('#finder-search').on('submit', function(e){
				e.stopPropagation();

				if ($searchSlider.length)
				{
					// Disable select boxes with no value selected.
					$searchSlider.find('select').each(function(index, el) {
						var $el = $(el);
			        	if(!$el.val()){
			        		$el.attr('disabled', 'disabled');
			        	}
        			});
				}

			});
		}
<?php endif; ?>
		/*
		 * This segment of code sets up the autocompleter.
		 */
<?php if ($this->params->get('show_autosuggest', 1)) : ?>
	<?php JHtml::_('script', 'com_finder/autocompleter.js', false, true); ?>
	var url = '<?php echo JRoute::_('index.php?option=com_finder&task=suggestions.display&format=json&tmpl=component', false); ?>';
	var completer = new Autocompleter.Request.JSON(document.getElementById("q"), url, {'postVar': 'q'});
<?php endif; ?>
	});
</script>

<form id="finder-search" action="<?php echo JRoute::_($this->query->toURI()); ?>" method="get" class="form-inline">
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
		<input type="text" name="q" id="q" size="30" value="<?php echo $this->escape($this->query->input); ?>" class="inputbox" />
		<?php if ($this->escape($this->query->input) != '' || $this->params->get('allow_empty_search')):?>
			<button name="Search" type="submit" class="btn btn-primary"><span class="icon-search icon-white"></span> <?php echo JText::_('JSEARCH_FILTER_SUBMIT');?></button>
		<?php else: ?>
			<button name="Search" type="submit" class="btn btn-primary disabled"><span class="icon-search icon-white"></span> <?php echo JText::_('JSEARCH_FILTER_SUBMIT');?></button>
		<?php endif; ?>
		<?php if ($this->params->get('show_advanced', 1)) : ?>
			<a href="#advancedSearch" data-toggle="collapse" class="btn"><span class="icon-list"></span> <?php echo JText::_('COM_FINDER_ADVANCED_SEARCH_TOGGLE'); ?></a>
		<?php endif; ?>
</fieldset>

	<?php if ($this->params->get('show_advanced', 1)) : ?>
		<div id="advancedSearch" class="collapse">
			<hr />
			<?php if ($this->params->get('show_advanced_tips', 1)) : ?>
				<div class="advanced-search-tip">
					<?php echo JText::_('COM_FINDER_ADVANCED_TIPS'); ?>
				</div>
				<hr />
			<?php endif; ?>
			<div id="finder-filter-window">
				<?php echo JHtml::_('filter.select', $this->query, $this->params); ?>
			</div>
		</div>
	<?php endif; ?>
</form>