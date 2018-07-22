<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Add specific helper files for html generation
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.tooltip');

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_languages&view=installed'); ?>" method="post" id="adminForm" name="adminForm">
<?php if (!empty($this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if ($this->total > 0) : ?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th width="1%">
						&#160;
					</th>
					<th width="15%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'name', $listDirn, $listOrder); ?>
					</th>
					<th width="15%" class="hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_TITLE_NATIVE', 'nativeName', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_TAG', 'language', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_DEFAULT', 'published', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_VERSION', 'version', $listDirn, $listOrder); ?>
					</th>
					<th class="hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_DATE', 'creationDate', $listDirn, $listOrder); ?>
					</th>
					<th class="hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_AUTHOR', 'author', $listDirn, $listOrder); ?>
					</th>
					<th class="hidden-phone hidden-tablet">
						<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_AUTHOR_EMAIL', 'authorEmail', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'extension_id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="10">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php
			$version = new JVersion;
			$currentShortVersion = preg_replace('#^([0-9\.]+)(|.*)$#', '$1', $version->getShortVersion());
			foreach ($this->rows as $i => $row) :
				$canCreate = $user->authorise('core.create',     'com_languages');
				$canEdit   = $user->authorise('core.edit',       'com_languages');
				$canChange = $user->authorise('core.edit.state', 'com_languages');
			?>
				<tr class="row<?php echo $i % 2; ?>">
					<td>
						<?php echo JHtml::_('languages.id', $i, $row->language); ?>
					</td>
					<td>
						<label for="cb<?php echo $i; ?>">
							<?php echo $this->escape($row->name); ?>
						</label>
					</td>
					<td class="hidden-phone">
						<?php echo $this->escape($row->nativeName); ?>
					</td>
					<td>
						<?php echo $this->escape($row->language); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.isdefault', $row->published, $i, 'installed.', !$row->published && $canChange); ?>
					</td>
					<td class="center small">
					<?php $minorVersion = $version::MAJOR_VERSION . '.' . $version::MINOR_VERSION; ?>
					<?php // Display a Note if language pack version is not equal to Joomla version ?>
					<?php if (strpos($row->version, $minorVersion) !== 0 || strpos($row->version, $currentShortVersion) !== 0) : ?>
						<span class="label label-warning hasTooltip" title="<?php echo JText::_('JGLOBAL_LANGUAGE_VERSION_NOT_PLATFORM'); ?>"><?php echo $row->version; ?></span>
					<?php else : ?>
						<span class="label label-success"><?php echo $row->version; ?></span>
					<?php endif; ?>
					</td>
					<td class="hidden-phone">
						<?php echo $this->escape($row->creationDate); ?>
					</td>
					<td class="hidden-phone">
						<?php echo $this->escape($row->author); ?>
					</td>
					<td class="hidden-phone hidden-tablet">
						<?php echo JStringPunycode::emailToUTF8($this->escape($row->authorEmail)); ?>
					</td>
					<td class="hidden-phone">
						<?php echo $this->escape($row->extension_id); ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
