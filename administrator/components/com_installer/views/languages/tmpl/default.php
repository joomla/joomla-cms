<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.tooltip');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div id="installer-languages" class="clearfix">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=languages'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif; ?>
			<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('filterButton' => false))); ?>
			<div class="clearfix"></div>
			<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
			<?php else : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="5%"></th>
						<th class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'name', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_LANGUAGE_TAG', 'element', $listDirn, $listOrder); ?>
						</th>
						<th width="5%" class="center">
							<?php echo JText::_('JVERSION'); ?>
						</th>
						<th width="40%" class="nowrap hidden-phone">
							<?php echo JText::_('COM_INSTALLER_HEADING_DETAILS_URL'); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="6">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php
				$version = new JVersion;
				$currentShortVersion = preg_replace('#^([0-9\.]+)(|.*)$#', '$1', $version->getShortVersion());
				$i = 0;
				foreach ($this->items as $language) :
					preg_match('#^pkg_([a-z]{2,3}-[A-Z]{2})$#', $language->element, $element);
					$language->code  = $element[1];
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td>
							<?php $buttonText = (isset($this->installedLang[0][$language->code]) || isset($this->installedLang[1][$language->code])) ? 'REINSTALL' : 'INSTALL'; ?>
							<?php $onclick = 'document.getElementById(\'install_url\').value = \'' . $language->detailsurl . '\'; Joomla.submitbutton(\'install.install\');'; ?>
							<input type="button" class="btn btn-small" value="<?php echo JText::_('COM_INSTALLER_' . $buttonText . '_BUTTON'); ?>" onclick="<?php echo $onclick; ?>" />
						</td>
						<td>
							<?php echo $language->name; ?>
						</td>
						<td>
							<?php echo $language->code; ?>
						</td>
						<td class="center">
								<?php $minorVersion = $version::MAJOR_VERSION . '.' . $version::MINOR_VERSION; ?>
								<?php // Display a Note if language pack version is not equal to Joomla version ?>
								<?php if (strpos($language->version, $minorVersion) !== 0 || strpos($language->version, $currentShortVersion) !== 0) : ?>
									<span class="label label-warning hasTooltip" title="<?php echo JText::_('JGLOBAL_LANGUAGE_VERSION_NOT_PLATFORM'); ?>"><?php echo $language->version; ?></span>
								<?php else : ?>
									<span class="label label-success"><?php echo $language->version; ?></span>
								<?php endif; ?>
						</td>
						<td class="small hidden-phone">
							<a href="<?php echo $language->detailsurl; ?>" target="_blank"><?php echo $language->detailsurl; ?></a>
						</td>
					</tr>
					<?php $i++; ?>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo base64_encode('index.php?option=com_installer&view=languages') ?>" />
			<input type="hidden" id="install_url" name="install_url" />
			<input type="hidden" name="installtype" value="url" />
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
