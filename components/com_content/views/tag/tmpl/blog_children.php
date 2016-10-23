<?php
/**
 * @package Tag View Feature for Joomla!
 * @copyright Copyright (c) 2016 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;
$class = ' class="first"';
$subdir = 0;
?>
<ul class="subdirectories">
<?php foreach ($this->children as $id => $child) :
        //Check if the current tag has children.
        $hasChildren = false;
	if(isset($this->children[$id + 1]) && $this->children[$id + 1]->level > $child->level) {
	  $hasChildren = true;
	}

	if($this->params->get('show_unused_tags') || $child->numitems || $hasChildren) :
	  if(!isset($this->children[$id + 1]) || $this->children[$id + 1]->level < $child->level) {
	    $class = ' class="last"';
	  }

          //The current child is a one level deeper subdirectory.
	  if(isset($this->children[$id - 1]) && $this->children[$id - 1]->level < $child->level) {
	    echo '<ul class="subdirectories">';
	    $subdir++;
	  }
	?>
	<li<?php echo $class; ?>>
		<?php $class = ''; ?>
			<span class="item-title"><a href="<?php echo JRoute::_(ContentHelperRoute::getTagRoute($child->id));?>">
				<?php echo $this->escape($child->title); ?></a>
			</span>

			<?php if ($this->params->get('show_subtag_desc') == 1) :?>
			<?php if ($child->description) : ?>
				<div class="category-desc">
					<?php echo JHtml::_('content.prepare', $child->description, '', 'com_content.tag'); ?>
				</div>
			<?php endif; ?>
            <?php endif; ?>

            <?php if ($this->params->get('show_tagged_num_articles') == 1) :?>
			<dl class="article-count"><dt>
				<?php echo JText::_('COM_CONTENT_NUM_ITEMS'); ?></dt>
				<dd><?php echo $child->numitems; ?></dd>
			</dl>
		<?php endif; ?>
		</li>
	<?php
	  //Close the current subdirectory.
	  if(isset($this->children[$id + 1]) && $this->children[$id + 1]->level < $child->level) {
	    $uls = $child->level - $this->children[$id + 1]->level;
	    for($i = 0; $i < $uls; $i++) {
	      echo '</ul>';
	      $subdir--;
	    }
	  }
	  elseif(!isset($this->children[$id + 1])) { //It's the last element.
	    for($i = 0; $i < $subdir; $i++) {
	      echo '</ul>';
	    }
	  }
        ?>
	<?php endif; ?>
      <?php endforeach; ?>
</ul>
