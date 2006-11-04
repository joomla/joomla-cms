<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<div align="center">
<?php if ($link) :
/*
 * NOTE: Closing anchor tag must be hard against the end of the image tag
 * otherwise css styling can be unpredictable
 */
?>
	<a href="<?php echo $link; ?>" target="_self">
<?php endif; ?>
	<img src="<?php echo $image->folder.'/'.$image->name; ?>" border="0" width="<?php echo $image->width; ?>" height="<?php echo $image->height; ?>" alt="<?php echo $image->name; ?>" /><?php if ($link) : ?></a><?php endif; ?>
</div>