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
	<div class="row">
		<div class="col-md-12">
			<div class="page-header">
				<h2 itemprop="headline">
					<?php echo $this->item->name; ?>
				</h2>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4">
			image
		</div>
		<div class="col-md-8">
			<?php echo $this->item->event->afterDisplayTitle; ?>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<?php echo $this->item->event->beforeDisplayContent; ?>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<p><?php echo $this->item->username; ?></p>
			<p><?php echo $this->item->email; ?></p>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<?php echo $this->item->event->afterDisplayContent; ?>
		</div>
	</div>
</div>


