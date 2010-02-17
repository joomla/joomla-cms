<?php
/**
 * @version		$Id: default.php 12812 2009-09-22 03:58:25Z dextercowley $
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers');

// If the page class is defined, wrap the whole output in a div.
$pageClass = $this->params->get('pageclass_sfx');
?>

<div class="jcategories-list<?php echo $pageClass;?>">

<?php if ($this->params->get('show_page_title', 1)) : ?>
<h2>
	<?php if ($this->escape($this->params->get('page_heading'))) :?>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	<?php else : ?>
		<?php echo $this->escape($this->params->get('page_title')); ?>
	<?php endif; ?>
</h2>
<?php endif; ?>

<?php if (!empty($this->items)) :
	$level = $this->items[0]->level;
	$itemcount=count($this->items);
?>	
<ul>

	<?php for ($i=0;$i<$itemcount;$i++) :
		$item = &$this->items[$i];
	?>	
	<li<?php echo $item->sclass != '' ? ' class="'.$item->sclass.'"' : ''?>>
		<span class="item-title"><a href="<?php echo ContactRoute::category('index.php?option=com_contact&view=category&catid='.$this->escape($item->slug));?>">
			<?php echo $this->escape($item->title); ?></a>
		</span>
		<?php if ($item->description) : ?>
			<div class="category-desc">
				<?php echo $item->description; ?>
			</div>
		<?php endif; ?>

<?php
		// The next item is deeper.
		if ($item->deeper) 
		{
			echo "\n<ul>";
		}
		// The next item is shallower.
		elseif ($item->shallower)
		{
			echo "\n</li>";
			echo str_repeat("\n</ul></li>", $item->level_diff);
		}
		// The next item is on the same level.
		else {
			echo "\n</li>";
		}
	endfor;

	if ($item->level > $level) :
		echo str_repeat("\n</ul></li>", $item->level-$level );
	endif;
?>
</ul>
<?php endif; ?>


</div>

