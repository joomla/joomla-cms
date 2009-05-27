<?php
/**
 * @version		$Id: mod_banners.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Site
 * @subpackage	mod_related_items
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;
?>
<ul class="relateditems<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($list as $item) :	?>
<li>
	<a href="<?php echo $item->route; ?>">
		<?php if ($showDate) echo $item->created . " - "; ?>
		<?php echo $item->title; ?></a>
</li>
<?php endforeach; ?>
</ul>