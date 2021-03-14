<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_stats
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ul class="mod-stats list-group">
<?php foreach ($list as $item) : ?>
	<li class="list-group-item justify-content-between">
		<?php echo $item->title; ?>
		<span class="badge bg-secondary rounded-pill"><?php echo $item->data; ?></span>
	</li>
<?php endforeach; ?>
</ul>
