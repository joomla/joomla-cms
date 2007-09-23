<?php // @version $Id$
defined('_JEXEC') or die('Restricted access');
?>

<?php if ($params->get('item_title')) : ?>
<h4>
	<?php if ($params->get('link_titles') && $linkOn != '') : ?>
	<a href="<?php echo JRoute::_($linkOn); ?>" class="contentpagetitle<?php echo $params->get('moduleclass_sfx'); ?>">
		<?php echo $item->title; ?>
	</a>
	<?php else :
		echo $item->title;
	endif; ?>
</h4>
<?php endif; ?>

<?php if (!$params->get('intro_only')) :
	echo $item->afterDisplayTitle;
endif; ?>

<?php echo $item->beforeDisplayContent;
echo htmlspecialchars ($item->text, ENT_COMPAT , UTF-8);
if (isset($item->linkOn) && $item->readmore) : ?>
<a href="<?php $item->linkOn; ?>" class="readon">
	<?php echo JText::_('Read more'); ?>
</a>
<?php endif; ?>
<span class="article_separator">&nbsp;</span>
