<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<h3><?php echo Text::_('MOD_MENU_HELP'); ?></h3>
<div class="card-columns">

	<?php foreach ($this->links as $link) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo Text::_($link['label']); ?></h4>
				<span class="fa fa-<?php echo $link['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo Text::_($link['desc']); ?></p>
				<a href="<?php echo $link['link']; ?>" class="btn btn-primary"><?php echo Text::_($link['title']); ?></a>
			</div>
		</div>
	<?php endforeach; ?>
</div>