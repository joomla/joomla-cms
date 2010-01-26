<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_newsflash
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>


<?php if ($params->get('item_title')) : ?>

	<h4 class="newsflash-title<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php if ($params->get('link_titles') && $item->linkOn != '') : ?>
		<a href="<?php echo $item->linkOn;?>">
			<?php echo $item->title;?></a>
	<?php else : ?>
		<?php echo $item->title; ?>
	<?php endif; ?>
	</h4>

<?php endif; ?>

<?php if (!$params->get('intro_only')) :
	echo $item->afterDisplayTitle;
endif; ?>

<?php echo $item->beforeDisplayContent; ?>

<?php echo $item->text; ?>


       <?php if (isset($item->linkOn) && $item->readmore && $params->get('readmore')) :
	      echo '<a class="readmore" href="'.$item->linkOn.'">'.$item->linkText.'</a>';
        endif; ?>

