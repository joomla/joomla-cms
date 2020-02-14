<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

?>
<dl class="search-results">
<?php foreach ($this->results as $result) : ?>
	<dt class="result-title">
		<?php echo $this->pagination->limitstart + $result->count . '. '; ?>
		<?php if ($result->href) : ?>
			<a href="<?php echo JRoute::_($result->href); ?>"<?php if ($result->browsernav == 1) : ?> target="_blank"<?php endif; ?>>
				<?php // $result->title should not be escaped in this case, as it may ?>
				<?php // contain span HTML tags wrapping the searched terms, if present ?>
				<?php // in the title. ?>
				<?php echo $result->title; ?>
			</a>
		<?php else : ?>
			<?php // see above comment: do not escape $result->title ?>
			<?php echo $result->title; ?>
		<?php endif; ?>
	</dt>
	<?php if ($result->section) : ?>
		<dd class="result-category">
			<span class="small">
				(<?php echo $this->escape($result->section); ?>)
			</span>
		</dd>
	<?php endif; ?>
	<dd class="result-text">
		<?php echo $result->text; ?>
	</dd>
	<?php if ($this->params->get('show_date')) : ?>
		<dd class="result-created">
			<?php echo JText::sprintf('JGLOBAL_CREATED_DATE_ON', $result->created); ?>
		</dd>
	<?php endif; ?>
<?php endforeach; ?>
</dl>
<div class="w-100">
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
