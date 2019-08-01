<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\Html $this */
?>

<h2 class="mt-3 mb-3">
	<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_COMPATIBILITY_CHECK', '&#x200E;' . $this->updateInfo['latest']); ?>
</h2>

<div class="row">
	<div class="col-12 col-lg-6">
		<fieldset class="options-grid-form options-grid-form-full">
			<legend>
				<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PREUPDATE_CHECK'); ?>
			</legend>
			<div>
				<table class="table">
					<tbody>
					<?php foreach ($this->phpOptions as $option) : ?>
						<tr>
							<td>
								<?php echo $option->label; ?>
							</td>
							<td>
									<span class="badge badge-<?php echo $option->state ? 'success' : 'danger'; ?>">
										<?php echo Text::_($option->state ? 'JYES' : 'JNO'); ?>
										<?php if ($option->notice) : ?>
											<span class="icon-info icon-white" title="<?php echo $option->notice; ?>"></span>
										<?php endif; ?>
									</span>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</fieldset>
	</div>

	<div class="col-12 col-lg-6">
		<fieldset class="options-grid-form  options-grid-form-full">
			<legend>
				<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_RECOMMENDED_SETTINGS'); ?>
			</legend>
			<p>
				<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_RECOMMENDED_SETTINGS_DESC'); ?>
			</p>
			<div>
			<table class="table">
				<thead>
				<tr>
					<td>
						<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DIRECTIVE'); ?>
					</td>
					<td>
						<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_RECOMMENDED'); ?>
					</td>
					<td>
						<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_ACTUAL'); ?>
					</td>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($this->phpSettings as $setting) : ?>
					<tr>
						<td>
							<?php echo $setting->label; ?>
						</td>
						<td>
							<?php echo Text::_($setting->recommended ? 'JON' : 'JOFF'); ?>
						</td>
						<td>
								<span class="badge badge-<?php echo ($setting->state === $setting->recommended) ? 'success' : 'warning'; ?>">
									<?php echo Text::_($setting->state ? 'JON' : 'JOFF'); ?>
								</span>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			</div>
		</fieldset>
	</div>
</div>
<fieldset class="options-grid-form options-grid-form-full mt-3 mb-3">
	<legend>
		<?php echo Text::_('NOTICE'); ?>
	</legend>
	<p><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DESCRIPTION_BREAK'); ?></p>
	<p><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DESCRIPTION_MISSING_TAG'); ?></p>
	<p><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DESCRIPTION_UPDATE_REQUIRED'); ?></p>
</fieldset>