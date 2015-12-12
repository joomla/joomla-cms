<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Add specific helper files for html generation
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('formbehavior.chosen', 'select');

$user     = JFactory::getUser();
$userId   = $user->get('id');
$client   = $this->state->get('filter.client_id', 0) ? JText::_('JADMINISTRATOR') : JText::_('JSITE');
$clientId = $this->state->get('filter.client_id', 0);
?>

<form action="<?php echo JRoute::_('index.php?option=com_languages&view=installed&client=' . $clientId); ?>" method="post" id="adminForm" name="adminForm">
<?php if (!empty($this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		</div>
		<div class="clearfix"> </div>
		<table class="table table-striped">
			<thead>
				<tr>
					<th width="20">
						&#160;
					</th>
					<th width="25%" class="title">
						<?php echo JText::_('COM_LANGUAGES_HEADING_LANGUAGE'); ?>
					</th>
					<th>
						<?php echo JText::_('COM_LANGUAGES_FIELD_LANG_TAG_LABEL'); ?>
					</th>
					<th>
						<?php echo JText::_('JCLIENT'); ?>
					</th>
					<th class="center">
						<?php echo JText::_('COM_LANGUAGES_HEADING_DEFAULT'); ?>
					</th>
					<th class="hidden-phone">
						<?php echo JText::_('JVERSION'); ?>
					</th>
					<th class="hidden-phone">
						<?php echo JText::_('JDATE'); ?>
					</th>
					<th class="hidden-phone">
						<?php echo JText::_('JAUTHOR'); ?>
					</th>
					<th class="hidden-phone">
						<?php echo JText::_('COM_LANGUAGES_HEADING_AUTHOR_EMAIL'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="9">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->rows as $i => $row) :
			$canCreate = $user->authorise('core.create',     'com_languages');
			$canEdit   = $user->authorise('core.edit',       'com_languages');
			$canChange = $user->authorise('core.edit.state', 'com_languages');
			?>
				<tr class="row<?php echo $i % 2; ?>">
					<td width="20">
						<?php echo JHtml::_('languages.id', $i, $row->language);?>
					</td>
					<td width="25%">
						<label for="cb<?php echo $i; ?>">
							<?php echo $this->escape($row->name); ?>
						</label>
					</td>
					<td>
						<?php echo $this->escape($row->language); ?>
					</td>
					<td>
						<?php echo $client;?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.isdefault', $row->published, $i, 'installed.', !$row->published && $canChange);?>
					</td>
					<td class="hidden-phone">
						<?php echo $this->escape($row->version); ?>
					</td>
					<td class="hidden-phone">
						<?php echo $this->escape($row->creationDate); ?>
					</td>
					<td class="hidden-phone">
						<?php echo $this->escape($row->author); ?>
					</td>
					<td class="hidden-phone">
						<?php echo JStringPunycode::emailToUTF8($this->escape($row->authorEmail)); ?>
					</td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
