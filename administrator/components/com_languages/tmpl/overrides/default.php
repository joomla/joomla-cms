<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Component\Languages\Administrator\Helper\LanguagesHelper;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
$client    = $this->state->get('filter.client') == '0' ? JText::_('JSITE') : JText::_('JADMINISTRATOR');
$language  = $this->state->get('filter.language');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$opposite_client   = $this->state->get('filter.client') == '1' ? JText::_('JSITE') : JText::_('JADMINISTRATOR');
$opposite_filename = constant('JPATH_' . strtoupper($this->state->get('filter.client') ? 'administrator' : 'site'))
	. '/language/overrides/' . $this->state->get('filter.language', 'en-GB') . '.override.ini';
$opposite_strings  = LanguagesHelper::parseFile($opposite_filename);

echo \JLayoutHelper::render('joomla.sidebars.subnavigation', ['entries' => $this->entries]);
?>

<form action="<?php echo JRoute::_('index.php?option=com_languages&view=overrides'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->items)) : ?>
					<joomla-alert type="warning"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
				<?php else : ?>
					<table class="table table-striped" id="overrideList">
						<thead>
							<tr>
								<th style="width:1%" class="text-center">
									<?php echo JHtml::_('grid.checkall'); ?>
								</th>
								<th style="width:30%">
									<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_VIEW_OVERRIDES_KEY', 'key', $listDirn, $listOrder); ?>
								</th>
								<th class="d-none d-md-table-cell">
									<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_VIEW_OVERRIDES_TEXT', 'text', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap d-none d-md-table-cell">
									<?php echo JText::_('COM_LANGUAGES_FIELD_LANG_TAG_LABEL'); ?>
								</th>
								<th class="d-none d-md-table-cell">
									<?php echo JText::_('JCLIENT'); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="5">
									<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
						</tfoot>
						<tbody>
						<?php $canEdit = JFactory::getUser()->authorise('core.edit', 'com_languages'); ?>
						<?php $i = 0; ?>
						<?php foreach ($this->items as $key => $text) : ?>
							<tr class="row<?php echo $i % 2; ?>" id="overriderrow<?php echo $i; ?>">
								<td class="text-center">
									<?php echo JHtml::_('grid.id', $i, $key); ?>
								</td>
								<td>
									<?php if ($canEdit) : ?>
										<a id="key[<?php echo $this->escape($key); ?>]" href="<?php echo JRoute::_('index.php?option=com_languages&task=override.edit&id=' . $key); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($key)); ?>">
											<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span><?php echo $this->escape($key); ?></a>
									<?php else : ?>
										<?php echo $this->escape($key); ?>
									<?php endif; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<span id="string[<?php echo $this->escape($key); ?>]"><?php echo $this->escape($text); ?></span>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $language; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $client; ?>
									<?php
									if (isset($opposite_strings[$key]) && ($opposite_strings[$key] == $text))
									{
										echo '/' . $opposite_client;
									}
									?>
								</td>
							</tr>
						<?php $i++; ?>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
