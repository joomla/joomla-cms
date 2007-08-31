<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php if (!empty($this->searchword)) : ?>
<div class="searchintro<?php echo $this->params->get('pageclass_sfx') ?>">
	<p>
		<?php echo JText::_('Search Keyword') ?><strong><? $this->escape($this->searchword) ?></strong>
		<?php echo $this->result ?>
		<a href="http://www.google.com/search?q=<?php echo $this->escape($this->searchword) ?>" target="_blank"> 
			<?php echo $this->image ?>
		</a>
	</p>
	<p>
		<a href="#form1" onclick="document.getElementById('search_searchword').focus();return false" onkeypress="document.getElementById('search_searchword').focus();return false" ><?php echo JText::_('Search_again') ?></a>
	</p>
</div>
<?php endif; ?>

<?php if (count($this->results)) : ?>
<div class="results">
	<h3><?php echo JText :: _('Search_result'); ?></h3>
	<div class="display">
	<form action="index.php" method="post" class="limit">
		<label for="limit"><?php echo JText :: _('Display Num') ?></label>
		<?php echo $this->pagination->getLimitBox(); ?>
		<p>
			<?php echo $this->pagination->getPagesCounter(); ?>
		</p>
	</form>
	</div>
	<?php $start = $this->pagination->limitstart + 1; ?>
	<ol class="list<?php echo $this->params->get('pageclass_sfx') ?>" start="<?php $start ?>">
		<?php foreach ($this->results as $result) : ?>
		<li>
			<?php if ($result->href) : ?>
			<h4>
				<a href="<?php echo JRoute :: _($result->href) ?>" <?php echo ($result->browsernav == 1) ? 'target="_blank"' : ''; ?>" >
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

			<?php echo $this->escape($result->text); ?>
			<span class="small<?php echo $this->params->get('pageclass_sfx') ?>">
				<?php echo $result->created; ?>
			</span>
		</li>
		<?php endforeach; ?>
	</ol>
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
<?php endif; ?>