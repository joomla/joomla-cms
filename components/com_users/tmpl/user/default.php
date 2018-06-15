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
<div class="item-page">
	<div class="page-header">
		<h2 itemprop="headline">
			<?php echo $this->item->name; ?>
		</h2>
	</div>

	<?php echo $this->item->event->afterDisplayTitle; ?>

	<?php echo $this->item->event->beforeDisplayContent; ?>

	<p><?php echo $this->item->username; ?></p>
	<p><?php echo $this->item->email; ?></p>

	<?php echo $this->item->event->afterDisplayContent; ?>
</div>


