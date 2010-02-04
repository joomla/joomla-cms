<?php
/**
 * @version		$Id: default.php 12812 2009-09-22 03:58:25Z dextercowley $
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers');

// If the page class is defined, wrap the whole output in a div.
$pageClass = $this->params->get('pageclass_sfx');
$curLevel = 0;
$difLevel = 0;
?>

<div class="jcategories-list<?php echo $pageClass;?>">

<?php if ($this->params->get('show_page_title', 1)) : ?>
<h1>
		<?php echo $this->escape($this->params->get('page_title')); ?>
</h1>
<?php endif; ?>



<?php if (!empty($this->items)) :
	$level = $this->items[0]->level;

	$itemcount = count($this->items);
	for ($i=0;$i<$itemcount;$i++) :
		$item = &$this->items[$i];

		$item->deeper		= (isset($this->items[$i+1]) && ($item->level < $this->items[$i+1]->level));
		$item->shallower	= (isset($this->items[$i+1]) && ($item->level > $this->items[$i+1]->level));
		$item->level_diff	= (isset($this->items[$i+1])) ? ($item->level - $this->items[$i+1]->level) : 0;
	endfor;
	echo '<ul>';
	for ($i=0;$i<$itemcount;$i++) :
		$item = &$this->items[$i];
		// The next item is deeper.
		if ($item->deeper)
		{
			echo "<li>";
		}
		// The next item is shallower.
		elseif ($item->shallower)
		{
			echo "<li>";
		}
		// The next item is on the same level.
		else {
			echo "<li>";
		}
?>
  		<span class="jitem-title"><a href="<?php echo WeblinksRoute::category($this->escape($item->slug));?>">
			<?php echo $this->escape($item->title); ?></a>
		</span>
		<?php if ($item->description) : ?>
			<div class="jdescription">
				<?php echo $item->description; ?>
			</div>
		<?php endif; ?>

<?php
		// The next item is deeper.
		if ($item->deeper)
		{
			echo "<ul>";
		}
		// The next item is shallower.
		elseif ($item->shallower)
		{
			echo "</li>";

			echo str_repeat("</ul></li>", $item->level_diff);
		}
		// The next item is on the same level.
		else {
			echo "</li>";
		}
	endfor;

	$alevel= $item->level ;

	if ($alevel > $level)
	{
		for ($i=$level;$i<$alevel;$i++)
		{
			echo "</li>";
			echo "</ul>";
		}
	}
	echo '</ul>';
?>
<?php endif; ?>

</div>
