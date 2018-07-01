<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<?php foreach ($this->links as $name => $links) : ?>
<h3><?php echo \Joomla\CMS\Language\Text::_($name); ?></h3>
<div class="card-columns">
	<?php foreach ($links as $id => $link) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($link['title']); ?></h4>
				<span class="fa fa-<?php echo $link['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($link['title']); ?></p>
				<a href="<?php echo $link['link']; ?>" class="btn btn-primary"><?php echo JText::_($link['title']); ?></a>
			</div>
		</div>
	<?php endforeach; ?>
</div>
<hr>
<?php endforeach; ?>