<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<form action="<?php echo JRoute::_('index.php?option=com_cache'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
<table class="adminlist">
	<thead>
		<tr>
			<th>
				<?php echo JText::_('COM_CACHE_PURGE_EXPIRED_ITEMS'); ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
			<p class="mod-purge-instruct"><?php echo JText::_('COM_CACHE_PURGE_INSTRUCTIONS'); ?></p>
			<p class="warning"><?php echo JText::_('COM_CACHE_RESOURCE_INTENSIVE_WARNING'); ?></p>
			</td>
		</tr>
	</tbody>
</table>

<div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</div>
</form>
