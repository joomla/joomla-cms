<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$client    = $this->state->get('filter.client') == 'site' ? Text::_('JSITE') : Text::_('JADMINISTRATOR');
$language  = $this->state->get('filter.language');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$oppositeClient   = $this->state->get('filter.client') == 'administrator' ? Text::_('JSITE') : Text::_('JADMINISTRATOR');
$oppositeFilename = constant('JPATH_' . strtoupper($this->state->get('filter.client') === 'site' ? 'administrator' : 'site'))
	. '/language/overrides/' . $this->state->get('filter.language', 'en-GB') . '.override.ini';
$oppositeStrings  = LanguageHelper::parseIniFile($oppositeFilename);
?>

<form action="<?php echo Route::_('index.php?option=com_languages&view=overrides'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<div class="clearfix"></div>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-warning">
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table" id="overrideList">
						<caption id="captionTable" class="sr-only">
							<?php echo Text::_('COM_LANGUAGES_OVERRIDES_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
						</caption>
						<thead>
							<tr>
								<td style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" style="width:30%">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_VIEW_OVERRIDES_KEY', 'key', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_VIEW_OVERRIDES_TEXT', 'text', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="d-none d-md-table-cell">
									<?php echo Text::_('COM_LANGUAGES_FIELD_LANG_TAG_LABEL'); ?>
								</th>
								<th scope="col" class="d-none d-md-table-cell">
									<?php echo Text::_('JCLIENT'); ?>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php $canEdit = Factory::getUser()->authorise('core.edit', 'com_languages'); ?>
						<?php $i = 0; ?>
						<?php foreach ($this->items as $key => $text) : ?>
							<tr class="row<?php echo $i % 2; ?>" id="overriderrow<?php echo $i; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $key); ?>
								</td>
								<th scope="row">
									<?php if ($canEdit) : ?>
										<a id="key[<?php echo $this->escape($key); ?>]" href="<?php echo Route::_('index.php?option=com_languages&task=override.edit&id=' . $key); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($key)); ?>">
											<?php echo $this->escape($key); ?></a>
									<?php else : ?>
										<?php echo $this->escape($key); ?>
									<?php endif; ?>
								</th>
								<td class="d-none d-md-table-cell">
									<span id="string[<?php echo $this->escape($key); ?>]"><?php echo $this->escape($text); ?></span>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $language; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $client; ?>
									<?php
									if (isset($oppositeStrings[$key]) && ($oppositeStrings[$key] == $text))
									{
										echo '/' . $oppositeClient;
									}
									?>
								</td>
							</tr>
						<?php $i++; ?>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
