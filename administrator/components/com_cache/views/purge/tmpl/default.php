<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<form action="<?php echo JRoute::_('index.php?option=com_cache&view=purge'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<div class="p-3">
					<p class="m-0"><?php echo JText::_('COM_CACHE_PURGE_INSTRUCTIONS'); ?></p>
					<input type="hidden" name="task" value="" />
					<?php echo JHtml::_('form.token'); ?>
				</div>
			</div>
		</div>
	</div>
</form>
