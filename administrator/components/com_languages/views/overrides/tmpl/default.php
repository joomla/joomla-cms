<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.tooltip');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
$client    = $this->state->get('filter.client') == 'site' ? JText::_('JSITE') : JText::_('JADMINISTRATOR');
$language  = $this->state->get('filter.language');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$oppositeClient   = $this->state->get('filter.client') == 'administrator' ? JText::_('JSITE') : JText::_('JADMINISTRATOR');
$oppositeFileName = constant('JPATH_' . strtoupper($this->state->get('filter.client') === 'site' ? 'administrator' : 'site'))
	. '/language/overrides/' . $this->state->get('filter.language', 'en-GB') . '.override.ini';
$oppositeStrings  = JLanguageHelper::parseIniFile($oppositeFileName);
?>

<form action="<?php echo JRoute::_('index.php?option=com_languages&view=overrides'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
        <div class="clearfix"></div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="overrideList">
				<thead>
					<tr>
						<th width="1%" class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="30%" class="left">
							<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_VIEW_OVERRIDES_KEY', 'key', $listDirn, $listOrder); ?>
						</th>
						<th class="left hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_VIEW_OVERRIDES_TEXT', 'text', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo JText::_('COM_LANGUAGES_FIELD_LANG_TAG_LABEL'); ?>
						</th>
						<th class="hidden-phone">
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
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $key); ?>
						</td>
						<td>
							<?php if ($canEdit) : ?>
								<a id="key[<?php echo $this->escape($key); ?>]" href="<?php echo JRoute::_('index.php?option=com_languages&task=override.edit&id=' . $key); ?>"><?php echo $this->escape($key); ?></a>
							<?php else : ?>
								<?php echo $this->escape($key); ?>
							<?php endif; ?>
						</td>
						<td class="hidden-phone">
							<span id="string[<?php echo $this->escape($key); ?>]"><?php echo $this->escape($text); ?></span>
						</td>
						<td class="hidden-phone">
							<?php echo $language; ?>
						</td>
						<td class="hidden-phone">
							<?php echo $client; ?><?php
							if (isset($oppositeStrings[$key]) && $oppositeStrings[$key] === $text)
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
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
