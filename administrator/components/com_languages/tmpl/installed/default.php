<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// Add specific helper files for html generation
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_languages&view=installed'); ?>" method="post" id="adminForm" name="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (empty($this->rows)) : ?>
					<joomla-alert type="warning"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
				<?php else : ?>
				<table class="table">
					<thead>
						<tr>
							<th style="width:1%">
								&#160;
							</th>
							<th style="width:15%" class="nowrap">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'name', $listDirn, $listOrder); ?>
							</th>
							<th style="width:15%" class="d-none d-sm-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_TITLE_NATIVE', 'nativeName', $listDirn, $listOrder); ?>
							</th>
							<th class="nowrap text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_TAG', 'language', $listDirn, $listOrder); ?>
							</th>
							<th style="width:5%" class="nowrap text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_DEFAULT', 'published', $listDirn, $listOrder); ?>
							</th>
							<th style="width:5%" class="nowrap text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_VERSION', 'version', $listDirn, $listOrder); ?>
							</th>
							<th style="width:10%" class="d-none d-md-table-cell text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_DATE', 'creationDate', $listDirn, $listOrder); ?>
							</th>
							<th style="width:10%" class="d-none d-md-table-cell text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_AUTHOR', 'author', $listDirn, $listOrder); ?>
							</th>
							<th style="width:10%" class="d-none d-md-table-cell text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_AUTHOR_EMAIL', 'authorEmail', $listDirn, $listOrder); ?>
							</th>
							<th style="width:5%" class="nowrap d-none d-md-table-cell text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'extension_id', $listDirn, $listOrder); ?>
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
								<?php echo HTMLHelper::_('languages.id', $i, $row->language); ?>
							</td>
							<td>
								<label for="cb<?php echo $i; ?>">
									<?php echo $this->escape($row->name); ?>
								</label>
							</td>
							<td class="hidden-md-down">
								<?php echo $this->escape($row->nativeName); ?>
							</td>
							<td class="text-center">
								<?php echo $this->escape($row->language); ?>
							</td>
							<td class="text-center">
								<?php echo HTMLHelper::_('jgrid.isdefault', $row->published, $i, 'installed.', !$row->published && $canChange); ?>
							</td>
							<td class="text-center">
                            <?php $minorVersion = $version::MAJOR_VERSION . '.' . $version::MINOR_VERSION; ?>
							<?php // Display a Note if language pack version is not equal to Joomla version ?>
							<?php if (substr($row->version, 0, 3) != $minorVersion || substr($row->version, 0, 5) != $currentShortVersion) : ?>
								<span class="badge badge-warning hasTooltip" title="<?php echo Text::_('JGLOBAL_LANGUAGE_VERSION_NOT_PLATFORM'); ?>"><?php echo $row->version; ?></span>
							<?php else : ?>
								<span class="badge badge-success"><?php echo $row->version; ?></span>
							<?php endif; ?>
							</td>
							<td class="d-none d-md-table-cell text-center">
								<?php echo $this->escape($row->creationDate); ?>
							</td>
							<td class="d-none d-md-table-cell text-center">
								<?php echo $this->escape($row->author); ?>
							</td>
							<td class="d-none d-md-table-cell text-center">
								<?php echo PunycodeHelper::emailToUTF8($this->escape($row->authorEmail)); ?>
							</td>
							<td class="d-none d-md-table-cell text-center">
								<?php echo $this->escape($row->extension_id); ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
