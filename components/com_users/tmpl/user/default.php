<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="item-page" itemscope itemtype="https://schema.org/Person">
	<div class="page-header">
		<h2 itemprop="name">
			<?php echo $this->escape($this->item->name); ?>
		</h2>
	</div>

	<?php echo $this->item->event->afterDisplayTitle; ?>

	<?php echo $this->item->event->beforeDisplayContent; ?>

	<div itemprop="email"><?php echo $this->escape($this->item->username); ?></div>
	<div><?php echo $this->escape($this->item->email); ?></div>

	<?php echo $this->item->event->afterDisplayContent; ?>
</div>


