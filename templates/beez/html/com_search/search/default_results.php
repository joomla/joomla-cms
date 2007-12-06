	<?php // @version $Id$
defined('_JEXEC') or die('Restricted access');
?>

<?php if (!empty($this->searchword)) : ?>
<div class="searchintro<?php echo $this->params->get('pageclass_sfx') ?>">
	<p>
		<?php echo JText::_('Search Keyword') ?> <strong><?php echo $this->escape($this->searchword) ?></strong>
		<?php echo $this->result ?>
	</p>
	<p>
		<a href="#form1" onclick="document.getElementById('search_searchword').focus();return false" onkeypress="document.getElementById('search_searchword').focus();return false" ><?php echo JText::_('Search_again') ?></a>
	</p>
</div>
<?php endif; ?>

<?php if (count($this->results)) : ?>
<div class="results">
	<h3><?php echo JText :: _('Search_result'); ?></h3>
	<?php $start = $this->pagination->limitstart + 1; ?>
	<ol class="list<?php echo $this->params->get('pageclass_sfx') ?>" start="<?php echo  $start ?>">
		<?php foreach ($this->results as $result) : ?>
		<li>
			<?php if ($result->href) : ?>
			<h4>
				<a href="<?php echo JRoute :: _($result->href) ?>" <?php echo ($result->browsernav == 1) ? 'target="_blank"' : ''; ?> >
					<?php echo $this->escape($result->title); ?>
				</a>
			</h4>
			<?php endif; ?>
			<?php if ($result->section) : ?>
			<p><?php echo JText::_('Category') ?>:
				<span class="small<?php echo $this->params->get('pageclass_sfx') ?>">
					<?php echo $this->escape($result->section); ?>
				</span>
			</p>
			<?php endif; ?>

			<?php echo $result->text; ?>
			<span class="small<?php echo $this->params->get('pageclass_sfx') ?>">
				<?php echo $result->created; ?>
			</span>
		</li>
		<?php endforeach; ?>
	</ol>
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
<?php endif; ?>