<?php // @version $Id$
defined('_JEXEC') or die;
?>

<?php if ($params->get('item_title')) : ?>
<h4>
	<?php if ($params->get('link_titles') && (isset($item->linkOn))) : ?>
	<a href="<?php echo JRoute::_($item->linkOn); ?>" class="contentpagetitle<?php echo $params->get('moduleclass_sfx'); ?>">
		<?php echo $item->title; ?></a>
	<?php else :
		echo $item->title;
	endif; ?>
</h4>
<?php endif; ?>

<?php if (!$params->get('intro_only')) :
	echo $item->afterDisplayTitle;
endif; ?>

<?php echo $item->beforeDisplayContent;
echo JFilterOutput::ampReplace($item->text);

$itemparams=new JParameter($item->attribs);
$readmoretxt=$itemparams->get('readmore',JText::_('Read more'));
if (isset($item->linkOn) && $item->readmore && $params->get('readmore')) : ?>
<a href="<?php echo $item->linkOn; ?>" class="readon">
	<?php echo $readmoretxt; ?></a>
<?php endif; ?>
<span class="article_separator">&nbsp;</span>
