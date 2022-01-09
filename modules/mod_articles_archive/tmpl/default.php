<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_archive
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php if (!empty($list)) : ?>
	<ul class="archive-module<?php echo $moduleclass_sfx; ?> mod-list">
	<?php foreach ($list as $item) : ?>
	<li>
		<a href="<?php echo $item->link; ?>">
			<?php echo $item->text; ?>
		</a>
	</li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>
