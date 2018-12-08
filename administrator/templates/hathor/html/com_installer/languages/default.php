<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');
JHtml::_('bootstrap.tooltip');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$version = new JVersion;

// Add spindle-wheel for language installation.
JFactory::getDocument()->addScriptDeclaration('
jQuery(document).ready(function($) {
	Joomla.loadingLayer("load");
	$("#adminForm").on("submit", function(e) {
		if (document.getElementsByName("task")[0].value == "languages.install")
		{
			Joomla.loadingLayer("show");
		}
	});
});
');
?>
<div id="installer-languages">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=languages');?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif;?>

		<?php if (count($this->items) || $this->escape($this->state->get('filter.search'))) : ?>
			<?php echo $this->loadTemplate('filter'); ?>
			<table class="adminlist">
				<thead>
					<tr>
						<th width="5%"></th>
						<th class="nowrap">
							<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="center">
							<?php echo JText::_('JVERSION'); ?>
						</th>
						<th class="center nowrap hidden-phone">
							<?php echo JText::_('COM_INSTALLER_HEADING_TYPE'); ?>
						</th>
						<th width="35%" class="nowrap hidden-phone">
							<?php echo JText::_('COM_INSTALLER_HEADING_DETAILS_URL'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($this->items as $i => $language) :
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

							<?php $minorVersion = $version::MAJOR_VERSION . '.' . $version::MINOR_VERSION; ?>
							<?php // Display a Note if language pack version is not equal to Joomla version ?>
							<?php if (substr($language->version, 0, 3) != $minorVersion
									|| substr($language->version, 0, 5) != $version->getShortVersion()) : ?>
								<div class="small"><?php echo JText::_('JGLOBAL_LANGUAGE_VERSION_NOT_PLATFORM'); ?></div>
							<?php endif; ?>
						</td>
						<td class="center">
							<?php echo $language->version; ?>
						</td>
						<td class="center">
							<?php echo JText::_('COM_INSTALLER_TYPE_' . strtoupper($language->type)); ?>
						</td>
						<td>
							<?php echo $language->detailsurl; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php echo $this->pagination->getListFooter(); ?>
		<?php else : ?>
			<div class="alert"><?php echo JText::_('COM_INSTALLER_MSG_LANGUAGES_NOLANGUAGES'); ?></div>
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo base64_encode('index.php?option=com_installer&view=languages') ?>" />
		<input type="hidden" id="install_url" name="install_url" />
		<input type="hidden" name="installtype" value="url" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
