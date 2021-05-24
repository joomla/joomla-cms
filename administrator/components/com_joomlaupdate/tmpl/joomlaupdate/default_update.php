<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\HtmlView;

/** @var HtmlView $this */
?>

<fieldset id="updateView" class="options-form">
	<legend>
		<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATEFOUND'); ?>
	</legend>
	<p>
		<?php echo Text::sprintf($this->langKey, $this->updateSourceKey); ?>
	</p>

	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLED'); ?>
		</div>
		<div class="controls">
			<?php echo '&#x200E;' . $this->updateInfo['installed']; ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_LATEST'); ?>
		</div>
		<div class="controls">
			<?php echo '&#x200E;' . $this->updateInfo['latest']; ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PACKAGE'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::link(
				$this->updateInfo['object']->downloadurl->_data,
				$this->updateInfo['object']->downloadurl->_data,
				[
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
					'title'  => Text::sprintf('JBROWSERTARGET_DOWNLOAD', $this->updateInfo['object']->downloadurl->_data)
				]
			); ?>
		</div>
	</div>

	<?php if (isset($this->updateInfo['object']->get('infourl')->_data)
		&& isset($this->updateInfo['object']->get('infourl')->title)) : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INFOURL'); ?>
			</div>
			<div class="controls">
				<?php echo HTMLHelper::link(
					$this->updateInfo['object']->get('infourl')->_data,
					$this->updateInfo['object']->get('infourl')->title,
					[
						'target' => '_blank',
						'rel'    => 'noopener noreferrer',
						'title'  => Text::sprintf('JBROWSERTARGET_NEW_TITLE', $this->updateInfo['object']->get('infourl')->title)
					]
				); ?>
			</div>
		</div>
	<?php endif; ?>

	<div id="preupdateCheckWarning" class="alert ">
		<h4 class="alert-heading">
			<?php echo Text::_('WARNING'); ?>
		</h4>
		<div class="alert-message">
			<div class="preupdateCheckIncomplete">
				<?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_CHECK_NOT_COMPLETE'); ?>
			</div>
		</div>
	</div>

	<div id="preupdateCheckCompleteProblems" class="hidden">
		<div class="alert ">
			<h4 class="alert-heading">
				<?php echo Text::_('WARNING'); ?>
			</h4>
			<div class="alert-message">
				<div class="preupdateCheckComplete">
					<?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_CHECK_COMPLETED_YOU_HAVE_DANGEROUS_PLUGINS'); ?>
				</div>
			</div>
		</div>
	</div>

	<div id="preupdateconfirmation" >
		<label class="preupdateconfirmation_label label label-warning span12">
			<h3>
				<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NON_CORE_PLUGIN_BEING_CHECKED'); ?>
			</h3>
		</label>
	</div>

	<div id="preupdatecheckheadings">
		<table class="table table-striped">
			<thead>
				<th>
					<?php echo Text::_('COM_INSTALLER_TYPE_PLUGIN'); ?>
				</th>
				<th>
					<?php echo Text::_('COM_INSTALLER_TYPE_PACKAGE'); ?>
				</th>
				<th>
					<?php echo Text::_('COM_INSTALLER_AUTHOR_INFORMATION'); ?>
				</th>
				<th>
					<?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_CHECK_EXTENSION_AUTHOR_URL'); ?>
				</th>
			</thead>
			<tbody>
				<?php foreach ($this->nonCoreCriticalPlugins as $nonCoreCriticalPlugin) : ?>
					<tr id='plg_<?php echo $nonCoreCriticalPlugin->extension_id ?>'>
						<td>
							<?php echo Text::_($nonCoreCriticalPlugin->name); ?>
						</td>
						<?php if ($nonCoreCriticalPlugin->package_id > 0) : ?>
							<?php foreach ($this->nonCoreExtensions as $nonCoreExtension) : ?>
								<?php if ($nonCoreCriticalPlugin->package_id == $nonCoreExtension->extension_id) : ?>
									<td>
										<?php echo $nonCoreExtension->name; ?>
									</td>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php else : ?>
							<td/>
						<?php endif; ?>
						<td>
							<?php if (isset($nonCoreCriticalPlugin->manifest_cache->author)) : ?>
								<?php echo Text::_($nonCoreCriticalPlugin->manifest_cache->author); ?>
							<?php elseif ($nonCoreCriticalPlugin->package_id > 0) : ?>
							<?php foreach ($this->nonCoreExtensions as $nonCoreExtension) : ?>
								<?php if ($nonCoreCriticalPlugin->package_id == $nonCoreExtension->extension_id) : ?>
								<td>
									<?php echo $nonCoreExtension->name; ?>
								</td>
								<?php endif; ?>
							<?php endforeach; ?>
							<?php endif;?>
						</td>
						<td>
							<?php
							$authorURL = "";
							if (isset($nonCoreCriticalPlugin->manifest_cache->authorUrl))
							{
								$authorURL = $nonCoreCriticalPlugin->manifest_cache->authorUrl;
							}
							elseif ($nonCoreCriticalPlugin->package_id > 0)
							{
								foreach ($this->nonCoreExtensions as $nonCoreExtension)
								{
									if ($nonCoreCriticalPlugin->package_id == $nonCoreExtension->extension_id)
									{
										$authorURL = $nonCoreExtension->manifest_cache->authorUrl;
									}
								}
							}
							?>
							<?php if (!empty($authorURL)) : ?>
								<a href="<?php echo $authorURL; ?>" target="_blank" >
								<?php echo $authorURL; ?>
								<span class="icon-out-2" aria-hidden="true"></span>
								<span class="element-invisible"><?php echo Text::_('JBROWSERTARGET_NEW'); ?></span>
								</a>
							<?php endif;?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<table id="preupdatecheckbox" >
		<td>
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NON_CORE_PLUGIN_CONFIRMATION'); ?>
		</td>
		<td>
			<input type="checkbox" id="noncoreplugins" name="noncoreplugins" value="1" required aria-required="true" />
		</td>
	</table>

	<hr>

	<div class="control-group">
		<div class="controls">
			<button class="btn btn-warning disabled submitupdate" type="submit" disabled>
				<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLUPDATE'); ?>
			</button>
		</div>
	</div>
</fieldset>
