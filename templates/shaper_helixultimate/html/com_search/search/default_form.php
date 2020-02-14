<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

$lang = JFactory::getLanguage();
$upper_limit = $lang->getUpperLimitSearchWord();

?>
<form id="searchForm" action="<?php echo JRoute::_('index.php?option=com_search'); ?>" method="post">
	<div class="form-group">
		<div class="input-group">
			<input type="text" name="searchword" placeholder="<?php echo JText::_('COM_SEARCH_SEARCH_KEYWORD'); ?>" id="search-searchword" maxlength="<?php echo $upper_limit; ?>" value="<?php echo $this->escape($this->origkeyword); ?>" class="form-control">
			<div class="input-group-append">
				<button name="Search" onclick="this.form.submit()" class="btn btn-secondary">
					<span class="fa fa-search" aria-hidden="true"></span>
					<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
				</button>
			</div>
		</div>
		<input type="hidden" name="task" value="search">
	</div>
	<div class="form-group searchintro<?php echo $this->params->get('pageclass_sfx'); ?>">
		<?php if (!empty($this->searchword)) : ?>
			<p>
				<?php echo JText::plural('COM_SEARCH_SEARCH_KEYWORD_N_RESULTS', '<span class="badge badge-info">' . $this->total . '</span>'); ?>
			</p>
		<?php endif; ?>
	</div>
	<?php if ($this->params->get('search_phrases', 1)) : ?>
		<fieldset>
			<legend>
				<?php echo JText::_('COM_SEARCH_FOR'); ?>
			</legend>
			<div class="form-group">
				<?php echo $this->lists['searchphrase']; ?>
			</div>
			<div class="form-group">
				<label for="ordering" class="mr-2">
					<?php echo JText::_('COM_SEARCH_ORDERING'); ?>
				</label>
				<?php echo $this->lists['ordering']; ?>
			</div>
		</fieldset>
		<hr>
	<?php endif; ?>
	<?php if ($this->params->get('search_areas', 1)) : ?>
		<div class="form-group">
			<fieldset>
				<legend>
					<?php echo JText::_('COM_SEARCH_SEARCH_ONLY'); ?>
				</legend>
				<?php foreach ($this->searchareas['search'] as $val => $txt) : ?>
					<div class="form-check form-check-inline">
						<?php $checked = is_array($this->searchareas['active']) && in_array($val, $this->searchareas['active']) ? 'checked="checked"' : ''; ?>
						<input type="checkbox" class="form-check-input" name="areas[]" value="<?php echo $val; ?>" id="area-<?php echo $val; ?>" <?php echo $checked; ?>>
						<label for="area-<?php echo $val; ?>" class="form-check-label"><?php echo JText::_($txt); ?></label>
					</div>
				<?php endforeach; ?>
			</fieldset>
		</div>
		<hr>
	<?php endif; ?>
	<?php if ($this->total > 0) : ?>
		<div class="form-group">
			<div class="form-inline">
				<label for="limit" class="mr-2">
					<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
				</label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		</div>
		<p><?php echo $this->pagination->getPagesCounter(); ?></p>
	<?php endif; ?>
</form>
