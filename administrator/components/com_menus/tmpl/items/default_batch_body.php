<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$options = [
	HTMLHelper::_('select.option', 'c', Text::_('JLIB_HTML_BATCH_COPY')),
	HTMLHelper::_('select.option', 'm', Text::_('JLIB_HTML_BATCH_MOVE'))
];
$published = $this->state->get('filter.published');
$clientId  = $this->state->get('filter.client_id');
$menuType  = Factory::getApplication()->getUserState('com_menus.items.menutype');

if ($clientId == 1)
{
	HTMLHelper::_('script', 'com_menus/default-batch-body.min.js', ['version' => 'auto', 'relative' => true], ['defer' => 'defer']);
}
?>
<div class="container">
	<?php if (strlen($menuType) && $menuType != '*') : ?>
	<?php if ($clientId != 1) : ?>
	<div class="row">
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo LayoutHelper::render('joomla.html.batch.language', []); ?>
			</div>
		</div>
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo LayoutHelper::render('joomla.html.batch.access', []); ?>
			</div>
		</div>
	</div>
	<?php endif; ?>
	<div class="row">
		<?php if ($published >= 0) : ?>
			<div class="form-group col-md-6">
				<div class="controls">
					<label id="batch-choose-action-lbl" for="batch-menu-id">
						<?php echo Text::_('COM_MENUS_BATCH_MENU_LABEL'); ?>
					</label>
					<select class="custom-select" name="batch[menu_id]" id="batch-menu-id">
						<option value=""><?php echo Text::_('JLIB_HTML_BATCH_NO_CATEGORY'); ?></option>
						<?php
						$opts     = array(
							'published' => $published,
							'checkacl'  => (int) $this->state->get('menutypeid'),
							'clientid'  => (int) $clientId,
						);
						echo HTMLHelper::_('select.options', HTMLHelper::_('menu.menuitems', $opts));
						?>
					</select>
				</div>

				<div id="batch-copy-move" class="control-group radio">
					<?php echo Text::_('JLIB_HTML_BATCH_MOVE_QUESTION'); ?>
					<?php echo HTMLHelper::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ($published < 0 && $clientId == 1): ?>
			<p><?php echo Text::_('COM_MENUS_SELECT_MENU_FILTER_NOT_TRASHED'); ?></p>
		<?php endif; ?>
	</div>
	<?php else : ?>
	<div class="row">
		<p><?php echo Text::_('COM_MENUS_SELECT_MENU_FIRST'); ?></p>
	</div>
	<?php endif; ?>
</div>
